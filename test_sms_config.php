<?php
/**
 * SMS Configuration Test Script
 * Run this to check if your SMS setup is correct
 */

require_once 'config.php';
require_once 'logic/db_connection.php';

echo "<h2>SMS Configuration Test</h2>";

// 1. Check Semaphore API Configuration
echo "<h3>1. Semaphore API Configuration</h3>";
$config = getSemaphoreConfig();
echo "API Key: " . ((!empty($config['api_key']) && $config['api_key'] !== 'YOUR_SEMAPHORE_API_KEY_HERE') ? '✓ Configured' : '✗ NOT CONFIGURED') . "<br>";
echo "Sender Name: " . htmlspecialchars($config['sender_name']) . "<br>";
echo "API URL: " . htmlspecialchars($config['api_url']) . "<br>";

// 2. Check if cURL is available
echo "<h3>2. cURL Availability</h3>";
echo "cURL: " . (function_exists('curl_init') ? '✓ Available' : '✗ NOT Available') . "<br>";

// 3. Check database for lost items with SMS enabled
echo "<h3>3. Lost Items with SMS Enabled</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT li.id, li.item_name, li.receive_sms, li.phone_number, li.status,
               s.first_name, s.last_name
        FROM lost_items li
        JOIN students s ON li.student_id = s.id
        ORDER BY li.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        echo "No lost items found in database.<br>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Item</th><th>Student</th><th>receive_sms</th><th>Phone</th><th>Status</th></tr>";
        foreach ($items as $item) {
            $smsEnabled = ($item['receive_sms'] == 1 || $item['receive_sms'] === '1') ? '✓ YES' : '✗ NO';
            echo "<tr>";
            echo "<td>" . $item['id'] . "</td>";
            echo "<td>" . htmlspecialchars($item['item_name']) . "</td>";
            echo "<td>" . htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) . "</td>";
            echo "<td>" . $smsEnabled . " (value: " . var_export($item['receive_sms'], true) . ")</td>";
            echo "<td>" . htmlspecialchars($item['phone_number'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($item['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// 4. Check error log location
echo "<h3>4. Error Log Location</h3>";
echo "Error log file: " . ini_get('error_log') . "<br>";
echo "Display errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "Log errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "<br>";

echo "<hr>";
echo "<h3>What to check:</h3>";
echo "<ol>";
echo "<li>Make sure Semaphore API Key is configured in config.php</li>";
echo "<li>Make sure cURL is available (required for SMS)</li>";
echo "<li>Check that receive_sms is set to 1 (not 0) for items you want SMS for</li>";
echo "<li>Check that phone_number is in correct format (09XXXXXXXXX or 639XXXXXXXXX)</li>";
echo "<li>Check error_log.txt file for detailed SMS sending logs</li>";
echo "</ol>";
?>
