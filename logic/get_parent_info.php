<?php
/**
 * Get Parent Information
 * Fetches parent/guardian details for a student from the database
 */

session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $student_id = $_GET['student_id'] ?? null;
    
    try {
        if (!$student_id) {
            throw new Exception("Student ID is required");
        }
        
        // Fetch parent information for the student
        $stmt = $pdo->prepare("
            SELECT parent_name, contact_number 
            FROM parents 
            WHERE student_id = ?
            LIMIT 1
        ");
        $stmt->execute([$student_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($parent) {
            echo json_encode([
                'success' => true,
                'parent' => $parent
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'parent' => null,
                'message' => 'No parent information found'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error fetching parent info: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
