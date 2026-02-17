<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Set JSON header early
header('Content-Type: application/json');

class AdminSMSNotifications {

    private $apiKey = "4f13582c3b12408500a7195239a591b7";
    private $senderName = "EMEMHS";
    private $apiUrl = "https://api.semaphore.co/api/v4/messages";

    /**
     * Get admin contact number from database
     */
    public function getAdminContactNumber() {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get admin user with contact information
            $stmt = $pdo->prepare("
                SELECT u.id, u.email, u.contact_number, s.first_name, s.last_name
                FROM users u
                LEFT JOIN students s ON u.id = s.user_id
                WHERE u.role = 'admin'
                LIMIT 1
            ");
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && !empty($admin['contact_number'])) {
                return [
                    'phone' => $admin['contact_number'],
                    'name' => ($admin['first_name'] && $admin['last_name']) ?
                             $admin['first_name'] . ' ' . $admin['last_name'] : 'Admin',
                    'email' => $admin['email']
                ];
            }

            error_log("No admin found or no contact number set");
            return null;
        } catch (Exception $e) {
            error_log("Error getting admin contact number: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send SMS using Semaphore API
     * Tries cURL first, falls back to file_get_contents if cURL fails
     */
    private function sendSMS($recipients, $message) {
        try {
            // Semaphore accepts comma-separated numbers for bulk SMS
            $numbers = is_array($recipients) ? implode(',', $recipients) : $recipients;

            $data = [
                "apikey" => $this->apiKey,
                "number" => $numbers,
                "message" => $message,
                "sendername" => $this->senderName
            ];

            // Try cURL first
            if (function_exists('curl_init')) {
                $ch = curl_init($this->apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // If cURL succeeded
                if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                    return true;
                }
                
                // cURL failed, try fallback
                error_log("cURL failed, falling back to file_get_contents");
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
                error_log("Failed to send SMS via both methods");
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log("Error sending SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send daily reminder about unscheduled records
     */
    public function sendDailyReminder() {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get pending complaints that are not scheduled
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM complaints_concerns
                WHERE status = 'pending' AND preferred_counseling_date IS NULL
            ");
            $stmt->execute();
            $unscheduledComplaints = $stmt->fetchColumn();

            // Get found items that haven't been claimed
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM lost_items
                WHERE status = 'found' AND claimed_by_student_id IS NULL
            ");
            $stmt->execute();
            $unclaimedItems = $stmt->fetchColumn();

            if ($unscheduledComplaints == 0 && $unclaimedItems == 0) {
                return ['success' => true, 'message' => 'No reminders needed - all records are up to date'];
            }

            $adminContact = $this->getAdminContactNumber();
            if (!$adminContact) {
                return ['success' => false, 'message' => 'No admin contact number found'];
            }

            $currentHour = (int)date('H');
            $isAfternoon = ($currentHour >= 12 && $currentHour < 18);

            if ($isAfternoon) {
                $message = "EMEMHS EDUCARE GUIDANCE SYSTEM - AFTERNOON SUMMARY\n\n";
                $message .= "Good afternoon Admin,\n\n";
                $message .= "Here is your end-of-day summary for " . date('F j, Y') . ":\n\n";
            } else {
                $message = "EMEMHS EDUCARE GUIDANCE SYSTEM - DAILY REMINDER\n\n";
                $message .= "Good day Admin,\n\n";
                $message .= "This is your daily reminder about pending tasks:\n\n";
            }

            if ($unscheduledComplaints > 0) {
                $message .= "📋 Unscheduled Complaints: $unscheduledComplaints\n";
                if ($isAfternoon) {
                    $message .= "   - These complaints still need counseling dates scheduled\n";
                    $message .= "   - Please schedule these before end of day if possible\n\n";
                } else {
                    $message .= "   - These complaints need counseling dates scheduled\n\n";
                }
            }

            if ($unclaimedItems > 0) {
                $message .= "📦 Unclaimed Found Items: $unclaimedItems\n";
                if ($isAfternoon) {
                    $message .= "   - These items are still waiting to be claimed by students\n";
                    $message .= "   - Please follow up with students before end of day\n\n";
                } else {
                    $message .= "   - These items are waiting to be claimed by students\n\n";
                }
            }

            if ($isAfternoon) {
                $message .= "Thank you for your attention to these matters today.\n\n";
                $message .= "Have a great evening!\n\n";
            } else {
                $message .= "Please log in to the admin dashboard to review and take action on these items.\n\n";
            }

            $message .= "EMEMHS Guidance Department\n";
            $message .= "Generated: " . date('Y-m-d H:i:s');

            $success = $this->sendSMS([$adminContact['phone']], $message);

            if ($success) {
                return [
                    'success' => true,
                    'message' => "Daily reminder sent successfully to admin",
                    'details' => [
                        'unscheduled_complaints' => $unscheduledComplaints,
                        'unclaimed_items' => $unclaimedItems,
                        'admin_name' => $adminContact['name']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to send SMS'];
            }

        } catch (Exception $e) {
            error_log("Error sending daily reminder: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send notification about today's sessions
     */
    public function sendSessionNotification() {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $today = date('Y-m-d');

            // Get scheduled complaints for today
            $stmt = $pdo->prepare("
                SELECT cc.*, s.first_name, s.last_name, s.grade_level, s.section
                FROM complaints_concerns cc
                JOIN students s ON cc.student_id = s.id
                WHERE cc.status = 'scheduled'
                AND DATE(cc.preferred_counseling_date) = ?
            ");
            $stmt->execute([$today]);
            $todaySessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($todaySessions)) {
                return ['success' => true, 'message' => 'No sessions scheduled for today'];
            }

            $adminContact = $this->getAdminContactNumber();
            if (!$adminContact) {
                return ['success' => false, 'message' => 'No admin contact number found'];
            }

            $currentHour = (int)date('H');
            $isMorning = ($currentHour >= 6 && $currentHour < 12);

            if ($isMorning) {
                $message = "EMEMHS EDUCARE GUIDANCE SYSTEM - TODAY'S SESSIONS\n\n";
                $message .= "Good morning Admin,\n\n";
                $message .= "You have " . count($todaySessions) . " counseling session(s) scheduled for today (" . date('F j, Y') . "):\n\n";
            } else {
                $message = "EMEMHS EDUCARE GUIDANCE SYSTEM - SESSION REMINDER\n\n";
                $message .= "Good afternoon Admin,\n\n";
                $message .= "This is a reminder about today's " . count($todaySessions) . " counseling session(s) (" . date('F j, Y') . "):\n\n";
            }

            foreach ($todaySessions as $index => $session) {
                $studentName = $session['first_name'] . ' ' . $session['last_name'];
                $type = ucwords(str_replace('_', ' ', $session['type']));
                $time = date('h:i A', strtotime($session['preferred_counseling_date']));

                $message .= ($index + 1) . ". " . $studentName . "\n";
                $message .= "   Grade " . $session['grade_level'] . " - " . $session['section'] . "\n";
                $message .= "   Type: " . $type . "\n";
                $message .= "   Time: " . $time . "\n\n";
            }

            if ($isMorning) {
                $message .= "Please ensure you are prepared and available for these sessions.\n\n";
                $message .= "Have a productive day!\n\n";
            } else {
                $message .= "Please ensure these sessions are going well and on schedule.\n\n";
                $message .= "Continue the great work!\n\n";
            }

            $message .= "EMEMHS Guidance Department\n";
            $message .= "Generated: " . date('Y-m-d H:i:s');

            $success = $this->sendSMS([$adminContact['phone']], $message);

            if ($success) {
                return [
                    'success' => true,
                    'message' => "Session notification sent successfully to admin",
                    'details' => [
                        'sessions_count' => count($todaySessions),
                        'admin_name' => $adminContact['name']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to send SMS'];
            }

        } catch (Exception $e) {
            error_log("Error sending session notification: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send urgent notification for high priority complaints
     */
    public function sendUrgentNotification($complaintId) {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get complaint details
            $stmt = $pdo->prepare("
                SELECT cc.*, s.first_name, s.last_name, s.grade_level, s.section
                FROM complaints_concerns cc
                JOIN students s ON cc.student_id = s.id
                WHERE cc.id = ? AND (cc.severity = 'urgent' OR cc.severity = 'high')
            ");
            $stmt->execute([$complaintId]);
            $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$complaint) {
                return ['success' => false, 'message' => 'Complaint not found or not urgent/high priority'];
            }

            $adminContact = $this->getAdminContactNumber();
            if (!$adminContact) {
                return ['success' => false, 'message' => 'No admin contact number found'];
            }

            $studentName = $complaint['first_name'] . ' ' . $complaint['last_name'];
            $type = ucwords(str_replace('_', ' ', $complaint['type']));
            $severity = strtoupper($complaint['severity']);
            $priorityText = ($complaint['severity'] === 'urgent') ? '🚨 URGENT' : '⚠️ HIGH PRIORITY';

            $currentHour = (int)date('H');
            $isMorning = ($currentHour >= 6 && $currentHour < 12);

            $message = "EMEMHS EDUCARE GUIDANCE SYSTEM - $priorityText NOTIFICATION\n\n";

            if ($isMorning) {
                $message .= "IMMEDIATE ATTENTION REQUIRED - MORNING ALERT!\n\n";
            } else {
                $message .= "IMMEDIATE ATTENTION REQUIRED - AFTERNOON ALERT!\n\n";
            }

            $message .= "Student: " . $studentName . "\n";
            $message .= "Grade " . $complaint['grade_level'] . " - " . $complaint['section'] . "\n";
            $message .= "Complaint Type: " . $type . "\n";
            $message .= "Severity: " . $severity . "\n\n";

            if ($isMorning) {
                $message .= "This urgent matter requires your immediate attention at the start of your workday.\n\n";
            } else {
                $message .= "This urgent matter requires your immediate attention before end of day.\n\n";
            }

            $message .= "Please attend to this matter as soon as possible.\n\n";
            $message .= "EMEMHS Guidance Department\n";
            $message .= "Generated: " . date('Y-m-d H:i:s');

            $success = $this->sendSMS([$adminContact['phone']], $message);

            if ($success) {
                return [
                    'success' => true,
                    'message' => "Urgent notification sent successfully to admin",
                    'details' => [
                        'complaint_id' => $complaintId,
                        'student_name' => $studentName,
                        'severity' => $complaint['severity'],
                        'admin_name' => $adminContact['name']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to send SMS'];
            }

        } catch (Exception $e) {
            error_log("Error sending urgent notification: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    try {
        $adminSMS = new AdminSMSNotifications();

        switch ($_POST['action']) {
            case 'send_daily_reminder':
                $result = $adminSMS->sendDailyReminder();
                break;

            case 'send_session_notification':
                $result = $adminSMS->sendSessionNotification();
                break;

            case 'send_urgent_notification':
                $complaintId = $_POST['complaint_id'] ?? 0;
                $result = $adminSMS->sendUrgentNotification($complaintId);
                break;

            case 'check_and_send_notifications':
                try {
                    $checker = new AutomaticSMSChecker();
                    $result = $checker->checkAndSendAutomaticNotifications();
                } catch (Exception $e) {
                    error_log("Error in check_and_send_notifications: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    $result = [
                        'success' => false,
                        'message' => 'Error checking notifications',
                        'error' => $e->getMessage()
                    ];
                }
                break;
    
            case 'test_connection':
                try {
                    $result = [
                        'success' => true,
                        'message' => 'Connection test successful',
                        'admin_contact' => $adminSMS->getAdminContactNumber(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                } catch (Exception $e) {
                    $result = [
                        'success' => false,
                        'message' => 'Connection test failed',
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                }
                break;
    
            case 'debug_check':
                try {
                    $checker = new AutomaticSMSChecker();
                    $result = [
                        'success' => true,
                        'message' => 'Debug check completed',
                        'details' => [
                            'admin_contact' => $adminSMS->getAdminContactNumber(),
                            'daily_conditions' => $checker->checkDailyReminderConditions($adminSMS),
                            'session_conditions' => $checker->checkSessionConditions($adminSMS),
                            'urgent_conditions' => $checker->checkUrgentConditions($adminSMS)
                        ],
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                } catch (Exception $e) {
                    $result = [
                        'success' => false,
                        'message' => 'Debug check failed',
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                }
                break;
    
            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }

        // Ensure we always return valid JSON
        if (!isset($result) || $result === null) {
            $result = ['success' => false, 'message' => 'No result generated'];
        }

        echo json_encode($result);
        exit;

    } catch (Exception $e) {
        // Log the error and return JSON error response
        error_log("Admin SMS Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

        $errorResult = [
            'success' => false,
            'message' => 'Server error occurred',
            'error' => $e->getMessage()
        ];

        echo json_encode($errorResult);
        exit;
    }
}

// Automatic notification checker class
class AutomaticSMSChecker {

    /**
     * Check conditions and send automatic notifications based on time of day
     */
    public function checkAndSendAutomaticNotifications() {
        $results = [];
        $adminSMS = new AdminSMSNotifications();

        // Check if admin contact is configured
        $adminContact = $adminSMS->getAdminContactNumber();
        if (!$adminContact) {
            return [
                'success' => false,
                'message' => 'Admin contact number not configured',
                'notifications_sent' => 0
            ];
        }

        $currentHour = (int)date('H');
        $isMorning = ($currentHour >= 6 && $currentHour < 12); // 6 AM - 12 PM
        $isAfternoon = ($currentHour >= 12 && $currentHour < 18); // 12 PM - 6 PM

        // Morning notification (8 AM): Focus on urgent issues and today's sessions
        if ($isMorning) {
            // Check for urgent notifications (high priority complaints) - MORNING PRIORITY
            $urgentResult = $this->checkUrgentConditions($adminSMS);
            if ($urgentResult['should_send']) {
                $results[] = $adminSMS->sendUrgentNotification($urgentResult['complaint_id']);
            }

            // Check for today's sessions - MORNING INFO
            $sessionResult = $this->checkSessionConditions($adminSMS);
            if ($sessionResult['should_send']) {
                $results[] = $adminSMS->sendSessionNotification();
            }
        }

        // Afternoon notification (2 PM): Focus on daily summary and pending items
        if ($isAfternoon) {
            // Check for daily reminder conditions (unscheduled records) - AFTERNOON SUMMARY
            $dailyResult = $this->checkDailyReminderConditions($adminSMS);
            if ($dailyResult['should_send']) {
                $results[] = $adminSMS->sendDailyReminder();
            }

            // Check for urgent notifications (high priority complaints) - AFTERNOON CHECK
            $urgentResult = $this->checkUrgentConditions($adminSMS);
            if ($urgentResult['should_send']) {
                $results[] = $adminSMS->sendUrgentNotification($urgentResult['complaint_id']);
            }
        }

        $notificationsSent = count(array_filter($results, function($result) {
            return isset($result['success']) && $result['success'];
        }));

        $period = $isMorning ? 'morning' : 'afternoon';

        return [
            'success' => true,
            'message' => "Automatic {$period} check completed",
            'notifications_sent' => $notificationsSent,
            'period' => $period,
            'details' => [
                'is_morning' => $isMorning,
                'is_afternoon' => $isAfternoon,
                'current_hour' => $currentHour,
                'results' => $results
            ]
        ];
    }

    /**
     * Check if daily reminder should be sent
     */
    private function checkDailyReminderConditions($adminSMS) {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get pending complaints that are not scheduled
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM complaints_concerns
                WHERE status = 'pending' AND preferred_counseling_date IS NULL
            ");
            $stmt->execute();
            $unscheduledComplaints = $stmt->fetchColumn();

            // Get found items that haven't been claimed
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM lost_items
                WHERE status = 'found' AND claimed_by_student_id IS NULL
            ");
            $stmt->execute();
            $unclaimedItems = $stmt->fetchColumn();

            // Send if there are any unscheduled records
            $shouldSend = ($unscheduledComplaints > 0 || $unclaimedItems > 0);

            return [
                'should_send' => $shouldSend,
                'reason' => $shouldSend ? 'Has unscheduled records' : 'No unscheduled records',
                'unscheduled_complaints' => $unscheduledComplaints,
                'unclaimed_items' => $unclaimedItems
            ];

        } catch (Exception $e) {
            return [
                'should_send' => false,
                'reason' => 'Error checking conditions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if session notification should be sent
     */
    private function checkSessionConditions($adminSMS) {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $today = date('Y-m-d');

            // Get scheduled complaints for today
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM complaints_concerns
                WHERE status = 'scheduled'
                AND DATE(preferred_counseling_date) = ?
            ");
            $stmt->execute([$today]);
            $todaySessions = $stmt->fetchColumn();

            // Send if there are sessions today
            $shouldSend = ($todaySessions > 0);

            return [
                'should_send' => $shouldSend,
                'reason' => $shouldSend ? 'Has sessions today' : 'No sessions today',
                'sessions_count' => $todaySessions
            ];

        } catch (Exception $e) {
            return [
                'should_send' => false,
                'reason' => 'Error checking conditions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if urgent notification should be sent
     */
    private function checkUrgentConditions($adminSMS) {
        try {
            // Database connection parameters
            $servername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbname = "guidancesystem";

            // Create PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $dbUsername, $dbPassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get urgent/high priority complaints that haven't been notified yet
            // For now, we'll check for any urgent/high priority complaints
            $stmt = $pdo->prepare("
                SELECT id
                FROM complaints_concerns
                WHERE (severity = 'urgent' OR severity = 'high')
                AND status = 'pending'
                ORDER BY date_created DESC
                LIMIT 1
            ");
            $stmt->execute();
            $urgentComplaint = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($urgentComplaint) {
                return [
                    'should_send' => true,
                    'reason' => 'Has urgent/high priority complaint',
                    'complaint_id' => $urgentComplaint['id']
                ];
            }

            return [
                'should_send' => false,
                'reason' => 'No urgent/high priority complaints'
            ];

        } catch (Exception $e) {
            return [
                'should_send' => false,
                'reason' => 'Error checking conditions: ' . $e->getMessage()
            ];
        }
    }
}
?>
