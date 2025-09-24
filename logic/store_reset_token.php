<?php
require_once 'db_connection.php';

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required = ['email', 'token'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $email = $input['email'];
    $token = $input['token'];
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // 1. Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Don't reveal that the email doesn't exist for security reasons
            // Just return success to prevent email enumeration
            $pdo->commit();
            echo json_encode(['status' => 'success']);
            exit();
        }
        
        $userId = $user['id'];
        
        // 2. Invalidate any existing tokens for this user
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ? AND used = 0");
        $stmt->execute([$userId]);
        
        // 3. Store the new token
        $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, used) VALUES (?, ?, 0)");
        $stmt->execute([$userId, $token]);
        
        // 4. Commit transaction
        $pdo->commit();
        
        // 5. Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Reset token stored successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
