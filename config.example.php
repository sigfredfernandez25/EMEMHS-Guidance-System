<?php
/**
 * Configuration File Template for EMEMHS Guidance System
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to "config.php"
 * 2. Fill in your actual configuration values
 * 3. Never commit config.php to version control
 * 
 * SECURITY WARNING: 
 * - Keep config.php outside the web root if possible
 * - Restrict file permissions (chmod 600 on Linux/Mac)
 * - Never share your API keys or passwords
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Add your database password here
define('DB_NAME', 'guidancesystem');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// SEMAPHORE SMS API CONFIGURATION
// ============================================
// Get your API key from: https://semaphore.co/
define('SEMAPHORE_API_KEY', 'YOUR_SEMAPHORE_API_KEY_HERE');
define('SEMAPHORE_SENDER_NAME', 'EMEMHS');
define('SEMAPHORE_API_URL', 'https://api.semaphore.co/api/v4/messages');

// ============================================
// APPLICATION SETTINGS
// ============================================
define('APP_NAME', 'EMEMHS Guidance System');
define('APP_URL', 'http://localhost/EMEMHS-Guidance-System'); // Update with your actual URL
define('APP_ENV', 'development'); // development, staging, production

// ============================================
// SESSION CONFIGURATION
// ============================================
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'EMEMHS_SESSION');

// ============================================
// SECURITY SETTINGS
// ============================================
define('ENABLE_CSRF_PROTECTION', true);
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// ============================================
// FILE UPLOAD SETTINGS
// ============================================
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// ============================================
// EMAIL CONFIGURATION (if needed in future)
// ============================================
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@ememhs.edu');
define('SMTP_FROM_NAME', 'EMEMHS Guidance System');

// ============================================
// TIMEZONE CONFIGURATION
// ============================================
date_default_timezone_set('Asia/Manila');

// ============================================
// ERROR REPORTING (based on environment)
// ============================================
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get Semaphore API configuration as array
 * @return array
 */
function getSemaphoreConfig() {
    return [
        'api_key' => SEMAPHORE_API_KEY,
        'sender_name' => SEMAPHORE_SENDER_NAME,
        'api_url' => SEMAPHORE_API_URL
    ];
}

/**
 * Get database configuration as array
 * @return array
 */
function getDatabaseConfig() {
    return [
        'host' => DB_HOST,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'database' => DB_NAME,
        'charset' => DB_CHARSET
    ];
}
?>
