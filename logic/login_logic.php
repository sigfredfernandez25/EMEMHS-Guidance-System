<?php
require_once 'sql_querries.php';
require_once 'db_connection.php'; // Make sure you have this file with database connection
try {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $pdo->beginTransaction();


    $stmt = $pdo->prepare(SQL_STUDENT_LOGIN);
    $stmt->execute([$username, $password]);
    if ($stmt->rowCount() > 0) {
        header("Location: ../pages/student_dashboard.php");
    }else{
        throw new Exception("Invalid username or password");
    }
}catch(Exception $e){
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}

?>