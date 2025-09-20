<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    error_log("Session check failed - isLoggedIn: " . ($_SESSION['isLoggedIn'] ?? 'not set'));
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized', 'available' => false]);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';

error_log("Received request - Date: $date, Time: $time");

if (empty($date) || empty($time)) {
    error_log("Missing parameters - Date: $date, Time: $time");
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required parameters', 'available' => false]);
    exit();
}

try {
    // First, let's test if we can connect to the database at all
    $testStmt = $pdo->prepare("SELECT 1");
    $testStmt->execute();
    error_log("Database connection successful");

    // Use military time format directly (no conversion needed)
    // Database stores time in military format like "8:00", "9:00", etc.

    // Check if the time slot is already taken
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM complaints_concerns
        WHERE scheduled_date = :date
        AND scheduled_time = :time
        AND status IN ('scheduled', 'resolved')
    ");

    $stmt->execute([
        'date' => $date,
        'time' => $time
    ]);

    $count = (int) $stmt->fetchColumn();

    // Debug logging
    error_log("Time slot check - Date: $date, Time: $time, Count: $count");

    // Fallback: if count is 0 or less, make it available
    $isAvailable = ($count <= 0);

    header('Content-Type: application/json');
    echo json_encode(['available' => $isAvailable, 'debug_count' => $count]);
} catch (PDOException $e) {
    error_log("Database error in check_time_slot.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'available' => false]);
} catch (Exception $e) {
    error_log("General error in check_time_slot.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'General error: ' . $e->getMessage(), 'available' => false]);
}
?> 