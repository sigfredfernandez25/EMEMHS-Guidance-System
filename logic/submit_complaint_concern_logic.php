<?php
require_once 'sql_querries.php';
require_once 'db_connection.php';
require_once 'notification_logic.php';
session_start();

$date = date('Y-m-d');
$time = date('H:i:s');

try {
    $transacType = "insert";
    $queryUse = SQL_INSERT_COMPLAINTS_CONCERNS;
    if ($_POST['isUpdate'] != "0") {
        $queryUse = SQL_UPDATE_COMPLAINTS_CONCERNS;
        $transacType = "update";
    }

    $rowId = $_POST['isUpdate'];
    $type = $_POST['complaint_type'];
    $severity = $_POST['severity'] ?? 'medium';
    $other = $_POST['other_specify'];
    $description = $_POST['description']; // <-- text from typing or voice recognition
    $counselingDate = $_POST['counseling_date'];

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

        // Notify parent via SMS
        $stmt = $pdo->prepare("SELECT contact_number, parent_name FROM parents WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        $contact_number = $parent['contact_number'] ?? null;
        $parent_name = $parent['parent_name'] ?? "Parent";

        if ($parent && !empty($contact_number)) {
            $apiToken = "5ff985e5-7b20-45cb-9044-ba67886de76b";
            $deviceId = "68cfb7f8b7dd99288d0a3f61";
            $url = "https://api.textbee.dev/api/v1/gateway/devices/$deviceId/send-sms";

            $severity_text = match($severity) {
                'low' => ' (Low Priority)',
                'medium' => ' (Medium Priority)',
                'high' => ' (High Priority)',
                'urgent' => ' (URGENT)',
                default => ' (Medium Priority)',
            };

            $message = "EMEMHS EDUCARE GUIDANCE SYSTEM\n\n";
            $message .= "Dear $parent_name,\n\n";
            $message .= "This is to inform you that your child has submitted a new ";
            $message .= str_replace('_', ' ', ucwords($insertType)) . " concern";
            $message .= $severity_text . " to our Counseling Office.\n\n";
            $message .= "Our guidance counselors will review this matter and contact you within 1-2 business days.\n\n";
            $message .= "If this is an urgent matter requiring immediate attention, please contact the school directly.\n\n";
            $message .= "Thank you.\n\n";
            $message .= "EMEMHS Guidance Department\nContact: (02) 123-4567";

            $data = [
                "recipients" => [$contact_number],
                "message" => $message
            ];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "x-api-key: $apiToken",
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_POST, true);

            curl_exec($ch);
            curl_close($ch);
        }
    }

    $pdo->commit();

    echo "<script>
        alert('Your complaint has been submitted.');
        window.location.href = '../pages/complaint-concern.php';
    </script>";
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
