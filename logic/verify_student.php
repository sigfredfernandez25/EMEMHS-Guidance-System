<?php
// Set JSON response header immediately
header('Content-Type: application/json; charset=utf-8');

// Set error handler to catch all errors and output as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'PHP Error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline
    ]);
    exit();
});

session_start();

try {
    require_once 'sql_querries.php';
    require_once 'db_connection.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Check if admin is logged in
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn'] || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Session: ' . json_encode($_SESSION)]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['student_id']) || !isset($input['is_verified'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$student_id = (int)$input['student_id'];
$is_verified = (bool)$input['is_verified'];

try {
    $pdo->beginTransaction();
    
    // Update student verification status
    if ($is_verified) {
        $stmt = $pdo->prepare(SQL_VERIFY_STUDENT);
    } else {
        $stmt = $pdo->prepare(SQL_UNVERIFY_STUDENT);
    }
    
    $stmt->execute([$student_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Student not found or already processed');
    }
    
    // Get student details for notification
    $stmt = $pdo->prepare("SELECT s.*, u.id as user_id FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        // Create notification for the student
        $message = $is_verified ? 
            "Your account has been verified! You can now submit complaints and report lost items." :
            "Your account verification was rejected. Please contact the guidance office for assistance.";
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, reference_id, reference_type, type, message, date_created, time_created)
            VALUES (?, ?, 'verification', ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $student['user_id'],
            $student_id,
            $is_verified ? 'account_verified' : 'account_rejected',
            $message,
            date('Y-m-d'),
            date('H:i:s')
        ]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => $is_verified ? 'Student verified successfully' : 'Student rejected successfully'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Student verification error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>