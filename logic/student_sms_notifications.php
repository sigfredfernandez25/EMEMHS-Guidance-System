<?php
/**
 * Student SMS Notifications using Semaphore API
 * Sends SMS to students when their lost items are found
 */

class StudentSMSNotifications {
    
    private $apiKey = "4f13582c3b12408500a7195239a591b7";
    private $senderName = "EMEMHS";
    private $apiUrl = "https://api.semaphore.co/api/v4/messages";

    /**
     * Send SMS using Semaphore API
     */
    private function sendSMS($phoneNumber, $message) {
        try {
            $data = [
                "apikey" => $this->apiKey,
                "number" => $phoneNumber,
                "message" => $message,
                "sendername" => $this->senderName
            ];

            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_POST, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($response === false || $httpCode >= 400) {
                error_log("Failed to send SMS. HTTP Code: $httpCode, Response: $response");
                return false;
            }

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

            // Check if student opted in for SMS and has phone number
            if (!$item['receive_sms'] || empty($item['phone_number'])) {
                return ['success' => false, 'message' => 'Student did not opt-in for SMS or no phone number provided'];
            }

            $studentName = $item['first_name'] . ' ' . $item['last_name'];
            $itemName = $item['item_name'];

            // Compose SMS message
            $message = "EMEMHS GUIDANCE SYSTEM\n\n";
            $message .= "Good news, " . $item['first_name'] . "!\n\n";
            $message .= "Your lost item has been found: " . $itemName . "\n\n";
            $message .= "Please visit the Guidance Office to claim your item.\n\n";
            $message .= "Bring a valid ID for verification.\n\n";
            $message .= "EMEMHS Guidance Department";

            // Send SMS
            $success = $this->sendSMS($item['phone_number'], $message);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully to student',
                    'details' => [
                        'student_name' => $studentName,
                        'item_name' => $itemName,
                        'phone_number' => $item['phone_number']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to send SMS'];
            }

        } catch (Exception $e) {
            error_log("Error in notifyItemFound: " . $e->getMessage());
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
