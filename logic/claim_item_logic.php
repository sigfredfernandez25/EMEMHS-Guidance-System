<?php
header('Content-Type: application/json');
require_once 'sql_querries.php';
require_once 'db_connection.php';
require_once 'notification_logic.php';

session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['isLoggedIn'])) {
        throw new Exception('User not logged in');
    }

    $item_id = $_POST['item_id'] ?? null;
    $claimant_photo = null;
    $claimant_photo_mime_type = null;
    $claim_evidence = $_POST['claim_evidence'] ?? 'Item claimed by student';

    if (!$item_id) {
        throw new Exception('Item ID is required');
    }

    // Handle photo upload from camera
    if (isset($_FILES['claimant_photo']) && $_FILES['claimant_photo']['error'] === UPLOAD_ERR_OK) {
        $photoTmpPath = $_FILES['claimant_photo']['tmp_name'];

        if (!empty($photoTmpPath) && file_exists($photoTmpPath)) {
            $claimant_photo = file_get_contents($photoTmpPath);
            $claimant_photo_mime_type = mime_content_type($photoTmpPath);
        }
    } elseif (!empty($_POST['claimant_photo_data'])) {
        // Handle base64 encoded photo from camera
        $photo_data = $_POST['claimant_photo_data'];
        if (strpos($photo_data, 'data:image') === 0) {
            $photo_parts = explode(',', $photo_data);
            $claimant_photo = base64_decode($photo_parts[1]);
            $claimant_photo_mime_type = str_replace('data:', '', explode(';', $photo_parts[0])[0]);
        }
    }

    // Get student ID from session
    $student_id = $_POST['students_id'];

    $pdo->beginTransaction();

    // First, verify the item exists and is available for claiming
    $stmt = $pdo->prepare(SQL_GET_LOST_ITEM_CLAIM_DETAILS);
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item not found or not available for claiming");
    }

    if ($item['status'] !== 'found') {
        throw new Exception("Item is not available for claiming");
    }

    // Check if item is already claimed
    if (!empty($item['claimed_by_student_id'])) {
        throw new Exception("Item has already been claimed");
    }

    // Claim the item
    $stmt = $pdo->prepare(SQL_CLAIM_LOST_ITEM);
    $result = $stmt->execute([
        $student_id,
        $claimant_photo,
        $claimant_photo_mime_type,
        $claim_evidence,
        $item_id
    ]);

    if (!$result) {
        throw new Exception("Failed to claim item");
    }

    // Get student information for notifications
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $student_name = $student['first_name'] . ' ' . $student['last_name'];

        // Notify admin about the claim
        $admin_notification = createAdminNotif(
            $item_id,
            $student_name,
            "claimed the lost item: " . $item['item_name']
        );

        // Create notification for the original owner (if different from claimant)
        if ($item['student_id'] != $student_id) {
            $owner_message = "Your lost item '" . $item['item_name'] . "' has been claimed by " . $student_name;
            $owner_notification = createNotification(
                $item['student_id'], // Original owner's user_id
                $item_id,
                'lost_item',
                'item_claimed',
                $owner_message
            );
        }

        // Create notification for the claimant
        $claimant_message = "You have successfully claimed the item: " . $item['item_name'];
        $claimant_notification = createNotification(
            $student_id,
            $item_id,
            'lost_item',
            'item_claimed',
            $claimant_message
        );
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Item claimed successfully!',
        'data' => [
            'item_id' => $item_id,
            'item_name' => $item['item_name'],
            'claimed_by' => $student_name
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error for debugging
    error_log("Claim item error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
