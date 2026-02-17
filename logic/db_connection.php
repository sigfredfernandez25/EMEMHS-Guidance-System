<?php
/**
 * Database Connection File
 * Uses centralized configuration from config.php
 */

// Load configuration
require_once __DIR__ . '/../config.php';

// Get database configuration
$dbConfig = getDatabaseConfig();
$servername = $dbConfig['host'];
$dbUsername = $dbConfig['username'];
$dbPassword = $dbConfig['password'];
$dbname = $dbConfig['database'];
$charset = $dbConfig['charset'];

// mysqli connection (for legacy code compatibility)
$con = mysqli_connect($servername, $dbUsername, $dbPassword, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset for mysqli
mysqli_set_charset($con, $charset);

// PDO connection (recommended for new code)
try {
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=$charset",
        $dbUsername,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("PDO Connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}
?>