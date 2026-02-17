<?php
/**
 * Test file for Semaphore API integration
 * This file helps verify that the Semaphore API is working correctly
 */

require_once 'config.php';
header('Content-Type: application/json');

// Get Semaphore configuration from config file
$semaphoreConfig = getSemaphoreConfig();
$apiKey = $semaphoreConfig['api_key'];
$senderName = $semaphoreConfig['sender_name'];
$apiUrl = $semaphoreConfig['api_url'];

// Test phone number (replace with actual test number)
$testNumber = "09151018824"; // Replace with your test number

// Test message
$testMessage = "EMEMHS GUIDANCE SYSTEM\n\nThis is a test message from the Semaphore API integration.\n\nIf you receive this, the integration is working correctly!\n\nEMEMHS Guidance Department";

try {
    $data = [
        "apikey" => $apiKey,
        "number" => $testNumber,
        "message" => $testMessage,
        "sendername" => $senderName
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to connect to Semaphore API',
            'error' => curl_error($ch)
        ]);
        exit;
    }

    $responseData = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode([
            'success' => true,
            'message' => 'SMS sent successfully via Semaphore',
            'http_code' => $httpCode,
            'response' => $responseData,
            'test_number' => $testNumber
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send SMS',
            'http_code' => $httpCode,
            'response' => $responseData,
            'error' => 'HTTP error code: ' . $httpCode
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception occurred',
        'error' => $e->getMessage()
    ]);
}
?>
