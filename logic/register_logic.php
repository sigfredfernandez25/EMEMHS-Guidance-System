<?php
require_once 'sql_querries.php';
require_once 'db_connection.php'; // Make sure you have this file with database connection

try {
    // Get POST data
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $grade_level = $_POST['grade_level'];
    $section = trim($_POST['section']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $parent_name = trim($_POST['parent_name']);
    $parent_contact = trim($_POST['parent_contact']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);

    // Validate phone numbers (must be 11 digits starting with 09)
    if (!preg_match('/^09[0-9]{9}$/', $phone)) {
        throw new Exception("Invalid phone number format. Must be 11 digits starting with 09 (e.g., 09123456789)");
    }
    
    if (!preg_match('/^09[0-9]{9}$/', $parent_contact)) {
        throw new Exception("Invalid parent/guardian contact number format. Must be 11 digits starting with 09 (e.g., 09123456789)");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Validate password strength
    if (strlen($password) < 6 || !preg_match('/[!@#$%^&*]/', $password)) {
        throw new Exception("Password must be at least 6 characters and contain at least one special character (!@#$%^&*)");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $pdo->beginTransaction();
    
    // Check if email already exists
    $stmt = $pdo->prepare(SQL_CHECK_EMAIL_EXISTS);
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Email already exists");
    }
    
    $stmt = $pdo->prepare(SQL_INSERT_USER);
    $stmt->execute([
        $email,
        $hashed_password,
        "student"
    ]);
    $userId = $pdo->lastInsertId();
    // Insert student
 
    $stmt = $pdo->prepare(SQL_INSERT_STUDENT);
    $stmt->execute([
        $userId,
        $first_name,
        $middle_name,
        $last_name,
        $grade_level,
        $section,
        $phone,
        $address
    ]);
    $student_id = $pdo->lastInsertId();
 
    // Insert parent
    $stmt = $pdo->prepare(SQL_INSERT_PARENT);
    $stmt->execute([
        $parent_name,
        $parent_contact,
        $student_id
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect to login page after successful registration
    header('Location: ../pages/login.php?registration=success');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error for debugging
    error_log("Registration error: " . $e->getMessage());
    
    // Redirect back to registration with error message
    header('Location: ../pages/register.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>