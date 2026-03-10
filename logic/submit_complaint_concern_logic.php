<?php
// Use absolute paths to avoid issues on shared hosting
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/sql_querries.php';
require_once __DIR__ . '/db_connection.php';
require_once __DIR__ . '/notification_logic.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get Semaphore configuration from config file
$semaphoreConfig = getSemaphoreConfig();

$date = date('Y-m-d');
$time = date('H:i:s');

try {
    // Validate POST data exists
    if (!isset($_POST['isUpdate']) || !isset($_POST['complaint_type']) || !isset($_POST['description'])) {
        throw new Exception("Missing required form data");
    }

    $transacType = "insert";
    $queryUse = SQL_INSERT_COMPLAINTS_CONCERNS;
    if ($_POST['isUpdate'] != "0") {
        $queryUse = SQL_UPDATE_COMPLAINTS_CONCERNS;
        $transacType = "update";
    }

    $rowId = $_POST['isUpdate'];
    $type = $_POST['complaint_type'];
    $severity = $_POST['severity'] ?? 'medium';
    $other = $_POST['other_specify'] ?? '';
    $description = $_POST['description']; // <-- text from typing or voice recognition
    $counselingDate = $_POST['counseling_date'] ?? null;

    $insertType = $type;
    if ($type === "others" && $other) {
        $insertType = $other;
    }

    $status = 'pending';
    $student_id = $_SESSION['student_id'];
    if ($student_id === 0 || $student_id === null) {
        echo "<script>
        alert('Error: Invalid student session. Please log in again.');
        window.location.href = '../pages/index.php';
        </script>";
        exit();
    }

    // Evidence handling (optional, images/docs only)
    $evidence = null;
    $imageType = null;

    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['evidence']['tmp_name'];

        if (!empty($imageTmpPath) && file_exists($imageTmpPath)) {
            $evidence = file_get_contents($imageTmpPath);
            $imageType = mime_content_type($imageTmpPath);
        }
    } elseif (!empty($_POST['existing_evidence']) && !empty($_POST['existing_mime_type'])) {
        $evidence = base64_decode($_POST['existing_evidence']);
        $imageType = $_POST['existing_mime_type'];
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare($queryUse);
    if ($transacType == "update") {
        $stmt->execute([
            $student_id,
            $insertType,
            $severity,
            $description,
            $counselingDate,
            $evidence,
            $imageType,
            $status,
            $date,
            $time,
            $rowId
        ]);
    } else if ($transacType == "insert") {
        $stmt->execute([
            $student_id,
            $insertType,
            $severity,
            $description,
            $counselingDate,
            $evidence,
            $imageType,
            $status,
            $date,
            $time,
        ]);
        
        // Get the last inserted complaint ID
        $complaint_id = $pdo->lastInsertId();

        // Get the student's name
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $student_name = $student['first_name'] . ' ' . $student['last_name'];

        if ($student) {
            notifyAdminNewComplaint($complaint_id, $student_name, $insertType);
        }

        // Notify parent via SMS using Semaphore
        $stmt = $pdo->prepare("SELECT contact_number, parent_name FROM parents WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        $contact_number = $parent['contact_number'] ?? null;
        $parent_name = $parent['parent_name'] ?? "Parent";

        if ($parent && !empty($contact_number)) {
            // Semaphore API configuration from config file
            $apiKey = $semaphoreConfig['api_key'];
            $senderName = $semaphoreConfig['sender_name'];
            $url = $semaphoreConfig['api_url'];

            $severity_text = match($severity) {
                'low' => 'Low',
                'medium' => 'Med',
                'high' => 'High',
                'urgent' => 'URGENT',
                default => 'Med',
            };

            // Short and direct message to save SMS credits
            $message = "EMEMHS: Your child submitted a " . str_replace('_', ' ', $insertType) . " concern ($severity_text priority). Guidance will contact you within 1-2 days. For urgent matters, call school directly.";

            $data = [
                "apikey" => $apiKey,
                "number" => $contact_number,
                "message" => $message,
                "sendername" => $senderName
            ];

            // Try cURL first (most reliable on shared hosting)
            $smsSuccess = false;
            $smsError = '';
            
            if (function_exists('curl_init')) {
                try {
                    $ch = curl_init($url);
                    if ($ch === false) {
                        throw new Exception("cURL initialization failed");
                    }
                    
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Reduced timeout for shared hosting
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);

                    if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                        $smsSuccess = true;
                    } else {
                        $smsError = "cURL failed: HTTP $httpCode - $curlError";
                    }
                } catch (Exception $e) {
                    $smsError = "cURL exception: " . $e->getMessage();
                }
            } else {
                $smsError = "cURL not available on this server";
            }
            
            // Log SMS result (don't fail the complaint submission if SMS fails)
            if ($smsSuccess) {
                error_log("Parent SMS notification sent successfully");
            } else {
                error_log("SMS notification failed: $smsError");
            }
        }
    }

    $pdo->commit();

    echo "<script>
        alert('Your complaint has been submitted.');
        window.location.href = '../pages/complaint-concern.php';
    </script>";
    exit();
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error for debugging
    error_log("Complaint submission error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Return user-friendly error
    echo "<script>
        alert('Error submitting complaint: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
    exit();
}
