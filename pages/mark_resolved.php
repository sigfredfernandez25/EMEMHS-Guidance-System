<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/notification_logic.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = $_POST['complaint_id'] ?? null;
    $admin_remark = $_POST['admin_remark'] ?? null;
    $status = $_POST['status'] ?? 'resolved'; // 'resolved' or 'unresolved'

    if ($complaint_id) {
        try {
            // Validate admin remark is provided
            if (empty(trim($admin_remark))) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Admin remark is required'
                ]);
                exit();
            }

            // Update complaint status and admin remark
            $stmt = $pdo->prepare("
                UPDATE " . TBL_COMPLAINTS_CONCERNS . " 
                SET status = ?, admin_remark = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $admin_remark, $complaint_id]);

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
                // Create notification for the student
                if ($status === 'resolved') {
                    createResolvedNotification($result['student_id'], $complaint_id);
                }
                
                // Return JSON response with student information
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'student_email' => $result['email'],
                    'student_name' => $result['first_name'] . ' ' . $result['last_name'],
                    'status' => $status
                ]);
                exit();
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Student information not found'
            ]);
            exit();
        } catch (Exception $e) {
            error_log("Error marking complaint: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
            exit();
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid complaint ID'
    ]);
    exit();
}

header("Location: complaint-concern-admin.php");
exit();
?> 