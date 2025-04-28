<?php
require_once 'sql_querries.php';
require_once 'db_connection.php'; // Make sure you have this file with database connection

try {
    // Get POST data
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $grade_level = $_POST['grade_level'];
    $section = $_POST['section'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $parent_name = $_POST['parent_name'];
    $parent_contact = $_POST['parent_contact'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if email already exists
    // $stmt = $pdo->prepare(SQL_CHECK_EMAIL_EXISTS);
    // $stmt->execute([$email]);
    // if ($stmt->rowCount() > 0) {
    //     throw new Exception("Email already exists");
    // }
    
    
    // Insert student
    $stmt = $pdo->prepare(SQL_INSERT_STUDENT);
    $stmt->execute([
        $first_name,
        $middle_name,
        $last_name,
        $grade_level,
        $section,
        $email,
        $phone,
        $password
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
    
    // Redirect to index page after successful registration
    header('Location: ../pages/index.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Return error response
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
}
?>