<?php
// Simple test file to check database connection and basic functionality
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    // Get database configuration
    $dbConfig = getDatabaseConfig();

    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test basic query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'admin_count' => $result['count'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>