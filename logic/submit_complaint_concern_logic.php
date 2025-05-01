<?php
require_once 'sql_querries.php';
require_once 'db_connection.php';
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
    $other = $_POST['other_specify'];
    $description = $_POST['description'];
    $counselingDate = $_POST['counseling_date'];
    $insertType = $type;
    if ($type === "others" && $other) {
        $insertType = $other;
    }
    $status = 'pending';
    $student_id = $_SESSION['student_id'];
    $evidence = null;
    $imageType = null;

    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['evidence']['tmp_name'];

        if (!empty($imageTmpPath) && file_exists($imageTmpPath)) {
            $evidence = file_get_contents($imageTmpPath); // keep as raw binary
            $imageType = mime_content_type($imageTmpPath);
        }
    } elseif (!empty($_POST['existing_evidence']) && !empty($_POST['existing_mime_type'])) {
        // Use previously uploaded evidence
        $evidence = base64_decode($_POST['existing_evidence']);
        $imageType = $_POST['existing_mime_type'];
    } else {
        // No evidence provided
        $evidence = null;
        $mime_type = null;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare($queryUse);
    if ($transacType == "update") {
        $stmt->execute([
            $student_id,
            $insertType,
            $description,
            $counselingDate,
            $evidence,      // raw binary stored in BLOB column
            $imageType,
            $status,   // store MIME type so you can convert later to base64 if needed
            $date,
            $time,
            $rowId
        ]);
    } else if ($transacType == "insert") {

        $stmt->execute([
            $student_id,
            $insertType,
            $description,
            $counselingDate,
            $evidence,      // raw binary stored in BLOB column
            $imageType,
            $status,   // store MIME type so you can convert later to base64 if needed
            $date,
            $time,
        ]);
    }



    $pdo->commit();

    echo "<script>
        alert('Your complaint has been submitted.');
        window.location.href = '../pages/student_dashboard.php';
    </script>";
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
}
