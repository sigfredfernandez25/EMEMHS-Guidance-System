<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_POST['item_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit();
}

$itemId = (int)$_POST['item_id'];

// Debug logging
error_log("get_claimant_photo.php called with item_id: " . $itemId);

try {
    // Get claimant photo data
    $stmt = $pdo->prepare("
        SELECT
            li.claimant_photo,
            li.claimant_photo_mime_type,
            cs.first_name as claimed_by_first_name,
            cs.last_name as claimed_by_last_name,
            li.claimed_at
        FROM lost_items li
        LEFT JOIN students cs ON li.claimed_by_student_id = cs.id
        WHERE li.id = ? AND li.claimant_photo IS NOT NULL AND li.claimant_photo_mime_type IS NOT NULL
    ");

    error_log("Executing query for item_id: " . $itemId);
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("Query result: " . ($item ? "Found item" : "No item found"));
    if ($item) {
        error_log("Item has photo: " . (!empty($item['claimant_photo']) ? "Yes" : "No"));
        error_log("Item has mime_type: " . (!empty($item['claimant_photo_mime_type']) ? "Yes" : "No"));
    }

    if ($item) {
        echo json_encode([
            'success' => true,
            'photo' => base64_encode($item['claimant_photo']),
            'mime_type' => $item['claimant_photo_mime_type'],
            'claimed_by_first_name' => $item['claimed_by_first_name'],
            'claimed_by_last_name' => $item['claimed_by_last_name'],
            'claimed_at' => $item['claimed_at']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No photo found for this item']);
    }

} catch (PDOException $e) {
    error_log("Error fetching claimant photo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>