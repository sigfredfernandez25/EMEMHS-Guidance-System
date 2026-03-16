<?php
/**
 * Manual Parent SMS Notification
 * Allows admin to manually send SMS to parent about student's complaint
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'db_connection.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = $_POST['complaint_id'];
    
    try {
        // Get complaint and student details
        $stmt = $pdo->prepare("
            SELECT cc.*, s.first_name, s.last_name, s.grade_level, s.section,
                   p.parent_name, p.contact_number
            FROM complaints_concerns cc
            JOIN students s ON cc.student_id = s.id
            LEFT JOIN parents p ON s.id = p.student_id
            WHERE cc.id = ?
        ");
        $stmt->execute([$complaint_id]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$complaint) {
            throw new Exception("Complaint not found");
        }
        
        // Check if parent contact exists
        if (empty($complaint['contact_number'])) {
            throw new Exception("No parent contact number available");
        }
        
        // Get Semaphore configuration
        $semaphoreConfig = getSemaphoreConfig();
        $apiKey = $semaphoreConfig['api_key'];
        $senderName = $semaphoreConfig['sender_name'];
        $url = $semaphoreConfig['api_url'];
        
        // Check if API key is configured
        if (empty($apiKey) || $apiKey === 'YOUR_SEMAPHORE_API_KEY_HERE') {
            throw new Exception("Semaphore API key not configured");
        }
        
        // Format severity text
        $severity_text = match($complaint['severity'] ?? 'medium') {
            'low' => 'Low',
            'medium' => 'Med',
            'high' => 'High',
            'urgent' => 'URGENT',
            default => 'Med',
        };
        
        // Compose SMS message
        $student_name = $complaint['first_name'] . ' ' . $complaint['last_name'];
        $complaint_type = str_replace('_', ' ', $complaint['type']);
        
        // Check if complaint is scheduled
        if ($complaint['status'] === 'scheduled' && !empty($complaint['scheduled_date']) && !empty($complaint['scheduled_time'])) {
            // Include schedule information
            $scheduled_date = date('M j, Y', strtotime($complaint['scheduled_date']));
            $scheduled_time = date('g:i A', strtotime($complaint['scheduled_time']));
            
            $message = "EMEMHS: Your child {$student_name} has a scheduled counseling session for {$complaint_type} on {$scheduled_date} at {$scheduled_time}. Please ensure they attend. For questions, contact the guidance office.";
        } else {
            // Regular notification without schedule
            $message = "EMEMHS: Your child {$student_name} submitted a {$complaint_type} concern ({$severity_text} priority). Guidance will contact you within 1-2 days. For urgent matters, call school directly.";
        }
        
        // Format phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $complaint['contact_number']);
        
        // Convert to 639XXXXXXXXX format
        if (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '9') {
            $phoneNumber = '63' . $phoneNumber;
        } elseif (strlen($phoneNumber) == 11 && substr($phoneNumber, 0, 2) == '09') {
            $phoneNumber = '63' . substr($phoneNumber, 1);
        } elseif (strlen($phoneNumber) != 12 || substr($phoneNumber, 0, 3) != '639') {
            throw new Exception("Invalid phone number format: " . $complaint['contact_number']);
        }
        
        $data = [
            "apikey" => $apiKey,
            "number" => $phoneNumber,
            "message" => $message,
            "sendername" => $senderName
        ];
        
        // Send SMS via cURL
        if (!function_exists('curl_init')) {
            throw new Exception("cURL not available on this server");
        }
        
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception("cURL initialization failed");
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            throw new Exception("SMS sending failed: HTTP $httpCode - $curlError");
        }
        
        // Log the SMS send
        error_log("Parent SMS sent manually for complaint ID: $complaint_id to $phoneNumber");
        
        echo json_encode([
            'success' => true,
            'message' => 'SMS sent successfully to parent',
            'details' => [
                'parent_name' => $complaint['parent_name'],
                'phone_number' => $phoneNumber,
                'student_name' => $student_name
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error sending parent SMS: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
