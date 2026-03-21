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
    $description = $_POST['description'] ?? ''; // Text description (optional if audio provided)
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

    // Validate that either description or audio is provided
    $hasAudio = !empty($_POST['audio_data']);
    if (empty(trim($description)) && !$hasAudio) {
        throw new Exception("Please provide either a written description or an audio recording.");
    }

    // Check if student is verified
    $stmt = $pdo->prepare(SQL_CHECK_STUDENT_VERIFIED);
    $stmt->execute([$student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || $result['is_verified'] != 1) {
        throw new Exception("Your account is not yet verified by the admin. Please wait for verification before submitting complaints.");
    }

    // Check submission limits only for new complaints (not updates)
    if ($transacType == "insert") {
        // Check daily limit
        $stmt = $pdo->prepare(SQL_COUNT_COMPLAINTS_TODAY);
        $stmt->execute([$student_id]);
        $todayCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($todayCount >= MAX_COMPLAINTS_PER_DAY) {
            throw new Exception("You have reached the maximum number of complaints (" . MAX_COMPLAINTS_PER_DAY . ") you can submit per day. Please try again tomorrow.");
        }

        // Check monthly limit
        $stmt = $pdo->prepare(SQL_COUNT_COMPLAINTS_THIS_MONTH);
        $stmt->execute([$student_id]);
        $monthCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($monthCount >= MAX_COMPLAINTS_PER_MONTH) {
            throw new Exception("You have reached the maximum number of complaints (" . MAX_COMPLAINTS_PER_MONTH . ") you can submit per month. Please try again next month.");
        }
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

    // Audio recording handling (optional)
    $audioRecording = null;
    $audioMimeType = null;
    $audioDuration = null;

    if (!empty($_POST['audio_data'])) {
        $audioRecording = base64_decode($_POST['audio_data']);
        $audioMimeType = $_POST['audio_mime_type'] ?? 'audio/webm';
        $audioDuration = !empty($_POST['audio_duration']) ? intval($_POST['audio_duration']) : null;
        
        // Validate audio size (max 5MB)
        $audioSizeMB = strlen($audioRecording) / (1024 * 1024);
        if ($audioSizeMB > 5) {
            throw new Exception("Audio recording is too large (" . number_format($audioSizeMB, 2) . "MB). Maximum size is 5MB.");
        }
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
            $audioRecording,
            $audioMimeType,
            $audioDuration,
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
            $audioRecording,
            $audioMimeType,
            $audioDuration,
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

        // Note: Parent SMS notification removed - admins can send manually from complaint details
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
