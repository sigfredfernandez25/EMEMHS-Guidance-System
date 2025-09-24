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
    $new_date = $_POST['new_date'] ?? null;
    $new_time = $_POST['new_time'] ?? null;
    $reason = $_POST['reason'] ?? null;

    error_log("Received reschedule request - Complaint ID: $complaint_id, New Date: $new_date, New Time: $new_time");

    if (!$complaint_id || !$new_date || !$new_time || !$reason) {
        error_log("Missing required parameters");
        sendJsonResponse(false, [], 'Missing required parameters');
    }

    try {
        // Get current complaint details
        $stmt = $pdo->prepare("
            SELECT cc.*, s.first_name, s.last_name, u.email,
                   cc.scheduled_date as old_date, cc.scheduled_time as old_time
            FROM " . TBL_COMPLAINTS_CONCERNS . " cc
            JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
            JOIN " . TBL_USERS . " u ON s.user_id = u.id
            WHERE cc.id = ? AND cc.status = 'scheduled'
        ");
        $stmt->execute([$complaint_id]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint) {
            error_log("Complaint not found or not scheduled - ID: $complaint_id");
            sendJsonResponse(false, [], 'Complaint not found or not scheduled');
        }

        // Check if the new time slot is available
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM " . TBL_COMPLAINTS_CONCERNS . " 
            WHERE scheduled_date = ? AND scheduled_time = ? AND status = 'scheduled' AND id != ?
        ");
        $stmt->execute([$new_date, $new_time, $complaint_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            error_log("Time slot not available - Date: $new_date, Time: $new_time");
            sendJsonResponse(false, [], 'Selected time slot is not available');
        }

        // Update the complaint with new schedule
        $stmt = $pdo->prepare("
            UPDATE " . TBL_COMPLAINTS_CONCERNS . " 
            SET scheduled_date = ?, 
                scheduled_time = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$new_date, $new_time, $complaint_id]);
        error_log("Updated complaint schedule - ID: $complaint_id, New Date: $new_date, New Time: $new_time");

        // Get the user_id for the student
        $stmt = $pdo->prepare("SELECT user_id FROM " . TBL_STUDENTS . " WHERE id = ?");
        $stmt->execute([$complaint['student_id']]);
        $student_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student_user) {
            // Create notification for the student about reschedule
            $message = "Your counseling session has been rescheduled to " . date('F j, Y', strtotime($new_date)) . 
                       " at " . date('g:i A', strtotime($new_time)) . ". Reason: " . $reason;
            
            $notificationResult = createNotification(
                $student_user['user_id'], 
                $complaint_id,
                'complaint',
                'rescheduled',
                $message
            );
        }
        
        error_log("Notification creation result: " . ($notificationResult ? "success" : "failed"));
        
        sendJsonResponse(true, [
            'student_email' => $complaint['email'],
            'student_name' => $complaint['first_name'] . ' ' . $complaint['last_name'],
            'old_date' => date('F j, Y', strtotime($complaint['old_date'])),
            'old_time' => date('g:i A', strtotime($complaint['old_time'])),
            'new_date' => date('F j, Y', strtotime($new_date)),
            'new_time' => date('g:i A', strtotime($new_time)),
            'reason' => $reason
        ]);

    } catch (Exception $e) {
        error_log("Error rescheduling complaint: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendJsonResponse(false, [], 'Database error occurred');
    }
}

sendJsonResponse(false, [], 'Invalid request method');
?>