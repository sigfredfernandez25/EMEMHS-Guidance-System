<?php
session_start();
require_once 'db_connection.php';

// Check if staff is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = $_POST['complaint_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    $referred_to = trim($_POST['referred_to'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $referral_date = date('Y-m-d');
    
    // Get the logged-in admin's email from session
    // Note: Admin names are not stored in the database, only email
    $referred_by_name = 'GUIDANCE OFFICE'; // Default fallback
    if (isset($_SESSION['email'])) {
        // Extract name from email (e.g., "john.doe@gmail.com" -> "JOHN DOE")
        $email_parts = explode('@', $_SESSION['email']);
        $name_part = str_replace(['.', '_', '-'], ' ', $email_parts[0]);
        $referred_by_name = strtoupper($name_part);
    }

    // Validate inputs
    if (empty($complaint_id) || empty($student_id) || empty($referred_to) || empty($reason)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    try {
        // Insert referral
        $stmt = $pdo->prepare("
            INSERT INTO referrals (complaint_id, student_id, referred_to, reason, referral_date, referred_by_name, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([$complaint_id, $student_id, $referred_to, $reason, $referral_date, $referred_by_name]);

        // Get the student's user_id for notification
        $user_stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
        $user_stmt->execute([$student_id]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data && $user_data['user_id']) {
            // Create notification for student
            $notif_message = "Your complaint has been referred to " . $referred_to . " for further assistance.";
            $notif_stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, reference_id, reference_type, type, message, is_read, date_created, time_created)
                VALUES (?, ?, 'complaint', 'referral', ?, 0, ?, ?)
            ");
            $notif_stmt->execute([
                $user_data['user_id'],
                $complaint_id,
                $notif_message,
                date('Y-m-d'),
                date('H:i:s')
            ]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Referral created successfully',
            'referral_id' => $pdo->lastInsertId()
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
