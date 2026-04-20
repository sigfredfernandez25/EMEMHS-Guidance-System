<?php
session_start();
require_once 'db_connection.php';

// Check if staff is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $referral_id = $_POST['referral_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    // Validate inputs
    if (empty($referral_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Referral ID and status are required']);
        exit();
    }

    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }

    try {
        // Update referral
        $stmt = $pdo->prepare("
            UPDATE referrals 
            SET status = ?, notes = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$status, $notes, $referral_id]);

        // Get referral details for notification
        $referral_stmt = $pdo->prepare("
            SELECT r.*, s.user_id, r.complaint_id, r.referred_to
            FROM referrals r
            JOIN students s ON r.student_id = s.id
            WHERE r.id = ?
        ");
        $referral_stmt->execute([$referral_id]);
        $referral = $referral_stmt->fetch(PDO::FETCH_ASSOC);

        // Create notification for student if status changed to completed
        if ($status === 'completed' && $referral && $referral['user_id']) {
            $notif_message = "The referral to " . $referral['referred_to'] . " has been completed.";
            $notif_stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, reference_id, reference_type, type, message, is_read, date_created, time_created)
                VALUES (?, ?, 'complaint', 'referral_update', ?, 0, ?, ?)
            ");
            $notif_stmt->execute([
                $referral['user_id'],
                $referral['complaint_id'],
                $notif_message,
                date('Y-m-d'),
                date('H:i:s')
            ]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Referral updated successfully'
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
