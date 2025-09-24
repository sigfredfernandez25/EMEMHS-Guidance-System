<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connection.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Function to log errors
function logError($message) {
    error_log('Password Reset Error: ' . $message);
}

try {
    // Get raw input for debugging
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $token = $data['token'] ?? '';
    $password = $data['password'] ?? '';

    error_log('Received token: ' . $token);
    error_log('Token length: ' . strlen($token));
    
    if (empty($token) || empty($password)) {
        throw new Exception('Token and password are required');
    }
    
    error_log('Reset attempt - Token: ' . $token);

    // Check if token exists and is not expired (24 hours)
    $stmt = $pdo->prepare("
        SELECT prt.user_id as id, prt.created_at, prt.used 
        FROM password_reset_tokens prt
        WHERE prt.token = ?
        LIMIT 1
    ");
    
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log('Token lookup result: ' . json_encode($tokenData));
    
    if (!$tokenData) {
        error_log('Token not found: ' . $token);
        throw new Exception('Invalid or expired token');
    }
    
    if ($tokenData['used'] == 1) {
        error_log('Token already used: ' . $token);
        throw new Exception('This reset link has already been used');
    }
    
    $tokenAge = time() - strtotime($tokenData['created_at']);
    if ($tokenAge > 86400) { // 24 hours in seconds
        error_log('Token expired: ' . $token . ' (age: ' . $tokenAge . ' seconds)');
        throw new Exception('This reset link has expired');
    }
    
    // Update password
    try {
        $pdo->beginTransaction();
        
        // 1. Get user email for logging
        $userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $userStmt->execute([$tokenData['id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // 2. Update password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $tokenData['id']]);
        
        if ($updateStmt->rowCount() === 0) {
            throw new Exception('No rows updated - user may not exist');
        }
        
        // 3. Mark token as used
        $markUsedStmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $markUsedStmt->execute([$token]);
        
        if ($markUsedStmt->rowCount() === 0) {
            throw new Exception('Failed to mark token as used');
        }
        
        $pdo->commit();
        
        error_log('Password updated successfully for user ID: ' . $tokenData['id']);
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Password update failed: ' . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
