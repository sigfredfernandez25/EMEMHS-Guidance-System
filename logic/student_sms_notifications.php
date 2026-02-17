<?php
/**
 * Student SMS Notifications using Semaphore API
 * Sends SMS to students when their lost items are found
 */

require_once __DIR__ . '/../config.php';

class StudentSMSNotifications {
    
    private $apiKey;
    private $senderName;
    private $apiUrl;

    public function __construct() {
        // Load configuration from config file
        $config = getSemaphoreConfig();
        $this->apiKey = $config['api_key'];
        $this->senderName = $config['sender_name'];
        $this->apiUrl = $config['api_url'];
    }

    /**
     * Send SMS using Semaphore API
     * Tries cURL first, falls back to file_get_contents if cURL fails
     */
    private function sendSMS($phoneNumber, $message) {
        try {
            $data = [
                "apikey" => $this->apiKey,
                "number" => $phoneNumber,
                "message" => $message,
                "sendername" => $this->senderName
            ];

            error_log("Attempting to send SMS to: " . $phoneNumber);

            // Try cURL first
            if (function_exists('curl_init')) {
                error_log("Trying cURL method...");
                
                $ch = curl_init($this->apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                // If cURL succeeded
                if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                    error_log("SMS sent successfully via cURL! HTTP Code: $httpCode");
                    error_log("Response: " . $response);
                    return true;
                }
                
                // cURL failed, log the error and try fallback
                error_log("cURL failed - HTTP Code: $httpCode, Error: " . $curlError);
                error_log("Falling back to file_get_contents...");
            } else {
                error_log("cURL not available, using file_get_contents...");
            }

            // Fallback to file_get_contents
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                    'timeout' => 30
                ]
            ];
            
            $context = stream_context_create($options);
            $response = @file_get_contents($this->apiUrl, false, $context);
            
            if ($response === false) {
                error_log("file_get_contents also failed!");
                return false;
            }
            
            error_log("SMS sent successfully via file_get_contents!");
            error_log("Response: " . $response);
            return true;

        } catch (Exception $e) {
            error_log("Error sending SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify student when their lost item is found
     */
    public function notifyItemFound($itemId) {
        try {
            error_log("=== Starting notifyItemFound for item ID: $itemId ===");
            
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get lost item details with student information
            $stmt = $pdo->prepare("
                SELECT li.*, s.first_name, s.last_name, s.phone_number as student_phone
                FROM lost_items li
                JOIN students s ON li.student_id = s.id
                WHERE li.id = ?
            ");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                error_log("Item not found with ID: $itemId");
                return ['success' => false, 'message' => 'Item not found'];
            }

            error_log("Item found: " . $item['item_name']);
            error_log("receive_sms: " . ($item['receive_sms'] ?? 'NULL'));
            error_log("phone_number: " . ($item['phone_number'] ?? 'NULL'));

            // Check if student opted in for SMS and has phone number
            if (!$item['receive_sms'] || empty($item['phone_number'])) {
                error_log("Student did not opt-in for SMS or no phone number provided");
                return ['success' => false, 'message' => 'Student did not opt-in for SMS or no phone number provided'];
            }

            $studentName = $item['first_name'] . ' ' . $item['last_name'];
            $itemName = $item['item_name'];
            
            // Format phone number for Philippine mobile (Semaphore expects 639XXXXXXXXX format)
            $phoneNumber = $item['phone_number'];
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber); // Remove non-numeric characters
            
            // Convert to 639XXXXXXXXX format
            if (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '9') {
                // 9XXXXXXXXX -> 639XXXXXXXXX
                $phoneNumber = '63' . $phoneNumber;
            } elseif (strlen($phoneNumber) == 11 && substr($phoneNumber, 0, 2) == '09') {
                // 09XXXXXXXXX -> 639XXXXXXXXX
                $phoneNumber = '63' . substr($phoneNumber, 1);
            } elseif (strlen($phoneNumber) == 12 && substr($phoneNumber, 0, 3) == '639') {
                // Already in correct format
                $phoneNumber = $phoneNumber;
            } else {
                error_log("Invalid phone number format: " . $item['phone_number']);
                return ['success' => false, 'message' => 'Invalid phone number format'];
            }
            
            error_log("Formatted phone number: " . $phoneNumber);

            // Compose SMS message - SHORT AND DIRECT to save costs
            $message = "EMEMHS: Hi " . $item['first_name'] . "! Your lost " . $itemName . " has been found. Please claim it at the Guidance Office with your ID.";

            error_log("Sending SMS to: " . $phoneNumber);

            // Send SMS
            $success = $this->sendSMS($phoneNumber, $message);

            if ($success) {
                error_log("=== SMS sent successfully! ===");
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully to student',
                    'details' => [
                        'student_name' => $studentName,
                        'item_name' => $itemName,
                        'phone_number' => $phoneNumber
                    ]
                ];
            } else {
                error_log("=== SMS sending failed ===");
                return ['success' => false, 'message' => 'Failed to send SMS'];
            }

        } catch (Exception $e) {
            error_log("Error in notifyItemFound: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notify student about scheduled counseling session
     */
    public function notifyScheduledSession($complaintId) {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get complaint details with student information
            $stmt = $pdo->prepare("
                SELECT cc.*, s.first_name, s.last_name, s.phone_number
                FROM complaints_concerns cc
                JOIN students s ON cc.student_id = s.id
                WHERE cc.id = ?
            ");
            $stmt->execute([$complaintId]);
            $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$complaint) {
                error_log("Complaint not found with ID: $complaintId");
                return ['success' => false, 'message' => 'Complaint not found'];
            }

            // Check if student has phone number
            if (empty($complaint['phone_number'])) {
                return ['success' => false, 'message' => 'Student has no phone number'];
            }

            $studentName = $complaint['first_name'] . ' ' . $complaint['last_name'];
            $scheduledDate = date('F j, Y', strtotime($complaint['scheduled_date']));
            $scheduledTime = date('g:i A', strtotime($complaint['scheduled_time']));

            // Compose SMS message
            $message = "EMEMHS GUIDANCE SYSTEM\n\n";
            $message .= "Dear " . $complaint['first_name'] . ",\n\n";
            $message .= "Your counseling session has been scheduled:\n\n";
            $message .= "Date: " . $scheduledDate . "\n";
            $message .= "Time: " . $scheduledTime . "\n\n";
            $message .= "Please be on time. If you need to reschedule, please contact the Guidance Office.\n\n";
            $message .= "EMEMHS Guidance Department";

            // Send SMS
            $success = $this->sendSMS($complaint['phone_number'], $message);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully to student',
                    'details' => [
                        'student_name' => $studentName,
                        'scheduled_date' => $scheduledDate,
                        'scheduled_time' => $scheduledTime
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to send SMS'];
            }

        } catch (Exception $e) {
            error_log("Error in notifyScheduledSession: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
