<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['suggestion']) || empty(trim($input['suggestion']))) {
    echo json_encode(['success' => false, 'message' => 'Suggestion cannot be empty']);
    exit;
}

$suggestion = trim($input['suggestion']);

// Validate suggestion length
if (strlen($suggestion) < 10) {
    echo json_encode(['success' => false, 'message' => 'Suggestion must be at least 10 characters long']);
    exit;
}

if (strlen($suggestion) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Suggestion must not exceed 1000 characters']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO anonymous_suggestions (suggestion, submitted_at) VALUES (?, NOW())");
    $stmt->execute([$suggestion]);
    
    echo json_encode(['success' => true, 'message' => 'Suggestion submitted successfully']);
} catch (PDOException $e) {
    error_log("Error submitting suggestion: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit suggestion']);
}
?>
