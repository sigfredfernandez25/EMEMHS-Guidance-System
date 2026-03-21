<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/sql_querries.php';
require_once __DIR__ . '/db_connection.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    $_SESSION['error_message'] = 'Unauthorized access. Please log in.';
    header("Location: ../pages/index.php");
    exit();
}

$date = date('Y-m-d');
$time = date('H:i:s');

try {
    // Validate POST data exists
    if (!isset($_POST['first_name']) || !isset($_POST['last_name']) || !isset($_POST['grade_level']) || 
        !isset($_POST['section']) || !isset($_POST['complaint_type']) || !isset($_POST['description']) || 
        !isset($_POST['counseling_date']) || !isset($_POST['admin_remark'])) {
        throw new Exception("Missing required form data");
    }

    // Get student information from form
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $grade_level = $_POST['grade_level'];
    $section = trim($_POST['section']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
    $contact_number = !empty($_POST['contact_number']) ? trim($_POST['contact_number']) : null;
    
    // Validate contact number if provided (must be 11 digits starting with 09)
    if ($contact_number && !preg_match('/^09[0-9]{9}$/', $contact_number)) {
        throw new Exception("Invalid contact number format. Must be 11 digits starting with 09 (e.g., 09123456789)");
    }
    
    // Get complaint information
    $type = $_POST['complaint_type'];
    $severity = $_POST['severity'] ?? 'medium';
    $other = $_POST['other_specify'] ?? '';
    $description = $_POST['description'];
    $counseling_date = $_POST['counseling_date'];
    $action_taken = $_POST['action_taken'] ?? '';
    $admin_remark = trim($_POST['admin_remark']);
    $follow_up_required = isset($_POST['follow_up_required']) ? 1 : 0;

    // Check if student already exists in the system
    $stmt = $pdo->prepare("SELECT id FROM students WHERE first_name = ? AND last_name = ? AND grade_level = ? AND section = ?");
    $stmt->execute([$first_name, $last_name, $grade_level, $section]);
    $existing_student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_student) {
        // Use existing student ID
        $student_id = $existing_student['id'];
    } else {
        // Create a temporary/walk-in student record
        // Generate a unique temporary email
        $temp_email = 'walkin_' . time() . '_' . rand(1000, 9999) . '@temp.local';
        
        // First create a user account for the student
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, '', 'student')");
        $stmt->execute([$temp_email]);
        $user_id = $pdo->lastInsertId();
        
        // Then create the student record with all required columns
        $stmt = $pdo->prepare(
            "INSERT INTO students (user_id, first_name, middle_name, last_name, grade_level, section, email, phone_number, password, address, school_id_image, school_id_mime_type, is_verified) 
             VALUES (?, ?, '', ?, ?, ?, ?, ?, '', '', NULL, NULL, 1)"
        );
        $stmt->execute([$user_id, $first_name, $last_name, $grade_level, $section, $temp_email, $contact_number]);
        $student_id = $pdo->lastInsertId();
    }

    $insertType = $type;
    if ($type === "others" && $other) {
        $insertType = $other;
    }

    // Status is 'resolved' since this is a walk-in that already happened
    $status = 'resolved';
    
    // Prepare notes with student info, action taken and follow-up info
    $notes = "Student: " . $first_name . " " . $last_name . " (Grade " . $grade_level . " - " . $section . ")";
    if ($age) {
        $notes .= " | Age: " . $age;
    }
    if ($contact_number) {
        $notes .= " | Contact: " . $contact_number;
    }
    $notes .= "\n\n" . $description;
    
    if (!empty($action_taken)) {
        $notes .= "\n\n--- Action Taken ---\n" . $action_taken;
    }
    if ($follow_up_required) {
        $notes .= "\n\n--- Follow-up Required: Yes ---";
    }
    
    // Record who created this entry
    $recorded_by = $_SESSION['staff_name'] ?? 'Admin';
    $notes .= "\n\n--- Walk-in recorded by: " . $recorded_by . " on " . $date . " at " . $time . " ---";

    $pdo->beginTransaction();

    // Insert the walk-in complaint record with admin remarks
    $stmt = $pdo->prepare(
        "INSERT INTO " . TBL_COMPLAINTS_CONCERNS . " 
        (student_id, type, severity, description, preferred_counseling_date, scheduled_date, scheduled_time, 
        evidence, mime_type, status, admin_remark, date_created, time_created)
        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?, ?, ?, ?)"
    );
    
    $stmt->execute([
        $student_id,
        $insertType,
        $severity,
        $notes,
        $counseling_date, // Use counseling date as preferred date
        $counseling_date, // Also set as scheduled date
        '00:00:00', // Default time for walk-ins
        $status,
        $admin_remark, // Add admin remarks
        $date,
        $time
    ]);

    $pdo->commit();

    $_SESSION['success_message'] = 'Walk-in complaint has been recorded successfully.';
    header("Location: ../pages/complaint-concern-admin.php");
    exit();
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error for debugging
    error_log("Walk-in complaint recording error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Return user-friendly error
    $_SESSION['error_message'] = 'Error recording walk-in complaint: ' . $e->getMessage();
    header("Location: ../pages/record-walkin-complaint.php");
    exit();
}
