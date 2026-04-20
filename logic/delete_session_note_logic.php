<?php
session_start();
require_once 'db_connection.php';
require_once 'session_notes_logic.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn'] || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

if (isset($_GET['session_id']) && isset($_GET['complaint_id'])) {
    $session_id = intval($_GET['session_id']);
    $complaint_id = intval($_GET['complaint_id']);
    
    $result = deleteSessionNote($session_id);
    
    if ($result['success']) {
        echo "<script>
            alert('" . $result['message'] . "');
            window.location.href = '../pages/view-session-history.php?complaint_id=$complaint_id';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . $result['message'] . "');
            window.location.href = '../pages/view-session-history.php?complaint_id=$complaint_id';
        </script>";
    }
} else {
    header("Location: ../pages/all-complaints.php");
    exit();
}
