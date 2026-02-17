<?php
/**
 * API Endpoint for Smart Matching
 * Returns matched items for a student
 */

session_start();
header('Content-Type: application/json');

require_once 'smart_matching.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['student_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$studentId = $_SESSION['student_id'];

try {
    $matches = getMatchesForDashboard($studentId);
    
    echo json_encode([
        'success' => true,
        'matches' => $matches,
        'count' => count($matches)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_matches.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching matches',
        'error' => $e->getMessage()
    ]);
}
?>
