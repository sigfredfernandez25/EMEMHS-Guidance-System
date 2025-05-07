<?php
require_once 'sql_querries.php';
require_once 'db_connection.php';
session_start();

try {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(SQL_LOGIN);
    $stmt->execute([$username, $password]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user'] = $user['user_id'];
        
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['student_id'] = $user['student_id'];
        $pdo->commit();

        if ($user['role'] === "admin") {
            header("Location: ../pages/staff-dashboard.php");
            exit;
        } elseif ($user['role'] === "student") {
            header("Location: ../pages/student_dashboard.php");
            exit;
        } else {
            echo "<script>alert('Unknown user role.'); window.location.href = '../pages/index.php';</script>";
        }

    } else {
        echo "<script>alert('Invalid username or password'); window.location.href = '../pages/index.php';</script>";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
