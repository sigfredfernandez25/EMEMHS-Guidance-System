<?php
require_once 'sql_querries.php';
require_once 'db_connection.php'; // Make sure you have this file with database connection
session_start();
try {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(SQL_STUDENT_LOGIN);
    $stmt->execute([$username, $password]);
    
    if ($stmt->rowCount() > 0) {
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $student_id = $student['id'];
        $_SESSION['student_id'] = $student_id;
        $_SESSION['isLoggedIn'] = true;
        
        $pdo->commit();
        header("Location: ../pages/student_dashboard.php");
    }else{
       echo "<script>
       alert('Invalid username or  password')
       window.location.href = '../pages/index.php';
       </script>";
    }
}catch(Exception $e){
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}

?>