<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/notification_logic.php';

// Disable error display and set error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Prevent any output before JSON response
ob_start();

// Function to send JSON response
function sendJsonResponse($success, $data = [], $error = null) {
    ob_clean(); // Clear any previous output
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error
    ]);
    exit();
}

// Check if staff is logged in
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    sendJsonResponse(false, [], 'Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = $_POST['complaint_id'] ?? null;
    $scheduled_date = $_POST['scheduled_date'] ?? null;
    $scheduled_time = $_POST['scheduled_time'] ?? null;

    error_log("Received schedule request - Complaint ID: $complaint_id, Date: $scheduled_date, Time: $scheduled_time");

    if (!$complaint_id || !$scheduled_date || !$scheduled_time) {
        error_log("Missing required parameters - Complaint ID: $complaint_id, Date: $scheduled_date, Time: $scheduled_time");
        sendJsonResponse(false, [], 'Missing required parameters');
    }

    try {
        // Check if complaint exists
        $stmt = $pdo->prepare("SELECT id FROM " . TBL_COMPLAINTS_CONCERNS . " WHERE id = ?");
        $stmt->execute([$complaint_id]);
        if (!$stmt->fetch()) {
            error_log("Complaint not found - ID: $complaint_id");
            sendJsonResponse(false, [], 'Complaint not found');
        }

        // Update scheduled date and time
        $stmt = $pdo->prepare("
            UPDATE " . TBL_COMPLAINTS_CONCERNS . " 
            SET scheduled_date = ?, 
                scheduled_time = ?,
                status = 'scheduled'
            WHERE id = ?
        ");
        
        $stmt->execute([$scheduled_date, $scheduled_time, $complaint_id]);
        error_log("Updated complaint schedule - ID: $complaint_id, Date: $scheduled_date, Time: $scheduled_time");

        // Get student information from the complaint
        $stmt = $pdo->prepare("
            SELECT s.id as student_id, s.first_name, s.last_name, u.email 
            FROM " . TBL_COMPLAINTS_CONCERNS . " cc
            JOIN students s ON cc.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE cc.id = ?
        ");
        $stmt->execute([$complaint_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            error_log("Found student information - ID: {$result['student_id']}, Name: {$result['first_name']} {$result['last_name']}");
            
            // Create notification for the student
            $notificationResult = createScheduledNotification(
                $result['student_id'], 
                $complaint_id,
                $scheduled_date,
                $scheduled_time
            );
            
            error_log("Notification creation result: " . ($notificationResult ? "success" : "failed"));
            
            sendJsonResponse(true, [
                'student_email' => $result['email'],
                'student_name' => $result['first_name'] . ' ' . $result['last_name'],
                'scheduled_date' => $scheduled_date,
                'scheduled_time' => $scheduled_time
            ]);
        } else {
            error_log("Student information not found for complaint ID: $complaint_id");
            sendJsonResponse(false, [], 'Student information not found');
        }
    } catch (Exception $e) {
        error_log("Error setting schedule: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendJsonResponse(false, [], 'Database error occurred');
    }
}

sendJsonResponse(false, [], 'Invalid request method');
?>
