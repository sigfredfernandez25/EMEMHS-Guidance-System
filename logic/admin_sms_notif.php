<?php
require_once 'config.php';
require_once 'sql_querries.php';
require_once 'db_connection.php'; // make sure you have PDO $pdo

// Get Semaphore configuration from config file
$semaphoreConfig = getSemaphoreConfig();

// Get admin contact number
$stmt = $pdo->prepare("SELECT contact_number FROM users WHERE role = :role LIMIT 1");
$stmt->execute([':role' => 'admin']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && !empty($admin['contact_number'])) {
    $contact_number = $admin['contact_number'];

    // ✅ Use your defined SQL query for pending complaints/concerns
    $stmt = $pdo->prepare(SQL_LIST_PENDING_COMPLAINTS_CONCERNS);
    $stmt->execute();
    $unscheduled = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countUnscheduled = count($unscheduled);

    if ($countUnscheduled > 0) {
        // Take top 3 records for preview in the SMS
        $preview = array_slice($unscheduled, 0, 3);

        $details = [];
        $i = 1;
        foreach ($preview as $row) {
            $details[] = $i++ . ". " . $row['first_name'] . " " . $row['last_name'] .
                        " - Grade " . $row['grade_level'] . " (" . $row['section'] . 
                        "), Severity: " . ucfirst($row['severity']);
        }

        $detailsMsg = implode("\n", $details);

        // If more than 3, append "+X more"
        if ($countUnscheduled > 3) {
            $detailsMsg .= "\n+ " . ($countUnscheduled - 3) . " more pending case(s)";
        }

        // ✅ Final professional SMS message
        $message  = "EMEMHS EDUCARE GUIDANCE SYSTEM\n\n";
        $message .= "Dear Administrator,\n\n";
        $message .= "This is to formally remind you that there are currently ";
        $message .= "$countUnscheduled pending complaints/concerns awaiting scheduling in the Guidance System.\n\n";
        $message .= "Top reported cases:\n$detailsMsg\n\n";
        $message .= "We kindly request your immediate attention to review and address these matters accordingly.\n\n";
        $message .= "Thank you for your continued support in ensuring student wellbeing.\n\n";
        $message .= "Sincerely,\n";
        $message .= "EMEMHS Guidance Department";

        // ✅ Semaphore API
        $apiKey = $semaphoreConfig['api_key'];
        $senderName = $semaphoreConfig['sender_name'];
        $url = $semaphoreConfig['api_url'];

        $data = [
            "apikey" => $apiKey,
            "number" => $contact_number,
            "message" => $message,
            "sendername" => $senderName
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $httpCode >= 400) {
            echo "❌ Failed to send SMS. HTTP Code: $httpCode<br>";
            echo "Response: $response";
        } else {
            echo "✅ SMS sent!<br>Response: $response";
        }

        curl_close($ch);
    } else {
        echo "ℹ️ No pending complaints/concerns found. No SMS sent.";
    }
} else {
    echo "⚠️ Admin contact number not found.";
}
