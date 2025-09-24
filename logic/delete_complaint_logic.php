<?php
session_start();
require_once 'db_connection.php';
require_once 'sql_querries.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'student') {
    echo "<script>alert('Unauthorized access!'); window.location.href = '../pages/index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = $_POST['complaint_id'];
    $student_id = $_SESSION['student_id'];

    try {
        // Verify the complaint belongs to the logged-in student and is pending
        $stmt = $pdo->prepare("SELECT id, status FROM " . TBL_COMPLAINTS_CONCERNS . " WHERE id = ? AND student_id = ?");
        $stmt->execute([$complaint_id, $student_id]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$complaint) {
            echo "<script>alert('Complaint not found or you do not have permission to delete it.'); window.location.href = '../pages/complaint-concern.php';</script>";
            exit();
        }

        if ($complaint['status'] !== 'pending') {
            echo "<script>alert('Only pending complaints can be deleted.'); window.location.href = '../pages/complaint-concern.php';</script>";
            exit();
        }

        // Start transaction to ensure both complaint and notifications are deleted together
        $pdo->beginTransaction();

        try {
            // Delete related notifications first
            $stmt = $pdo->prepare("DELETE FROM " . TBL_NOTIFICATIONS . " WHERE reference_id = ? AND reference_type = 'complaint'");
            $stmt->execute([$complaint_id]);

            // Delete the complaint
            $stmt = $pdo->prepare("DELETE FROM " . TBL_COMPLAINTS_CONCERNS . " WHERE id = ? AND student_id = ?");
            $result = $stmt->execute([$complaint_id, $student_id]);

            if ($result) {
                $pdo->commit();
                echo "<script>alert('Complaint deleted successfully.'); window.location.href = '../pages/complaint-concern.php';</script>";
            } else {
                $pdo->rollBack();
                echo "<script>alert('Failed to delete complaint.'); window.location.href = '../pages/complaint-concern.php';</script>";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error in transaction: " . $e->getMessage());
            echo "<script>alert('An error occurred while deleting the complaint.'); window.location.href = '../pages/complaint-concern.php';</script>";
        }
    } catch (Exception $e) {
        error_log("Error deleting complaint: " . $e->getMessage());
        echo "<script>alert('An error occurred while deleting the complaint.'); window.location.href = '../pages/complaint-concern.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = '../pages/complaint-concern.php';</script>";
}
