<?php
session_start();
require_once 'db_connection.php';
require_once 'session_notes_logic.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn'] || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = intval($_POST['complaint_id']);
    $is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] == '1';
    $session_id = $is_edit ? intval($_POST['session_id']) : 0;
    
    // Prepare data
    $data = [
        'complaint_id' => $complaint_id,
        'session_date' => $_POST['session_date'],
        'session_time' => $_POST['session_time'],
        'presenting_problem_1' => !empty($_POST['presenting_problem_1']) ? $_POST['presenting_problem_1'] : null,
        'presenting_problem_2' => !empty($_POST['presenting_problem_2']) ? $_POST['presenting_problem_2'] : null,
        'presenting_problem_3' => !empty($_POST['presenting_problem_3']) ? $_POST['presenting_problem_3'] : null,
        'general_observations' => !empty($_POST['general_observations']) ? $_POST['general_observations'] : null,
        'session_summary' => $_POST['session_summary'],
        'action_taken' => !empty($_POST['action_taken']) ? $_POST['action_taken'] : null,
        'follow_up_recommendations' => !empty($_POST['follow_up_recommendations']) ? $_POST['follow_up_recommendations'] : null,
        'next_appointment_date' => !empty($_POST['next_appointment_date']) ? $_POST['next_appointment_date'] : null,
        'next_appointment_time' => !empty($_POST['next_appointment_time']) ? $_POST['next_appointment_time'] : null,
        'counselor_name' => $_POST['counselor_name'],
        'counselor_id' => $_SESSION['user_id'] ?? null
    ];
    
    if ($is_edit) {
        // Update existing session
        $result = updateSessionNote($session_id, $data);
    } else {
        // Add new session
        $result = addSessionNote($data);
    }
    
    if ($result['success']) {
        echo "<script>
            alert('" . $result['message'] . "');
            window.location.href = '../pages/view-session-history.php?complaint_id=$complaint_id';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . $result['message'] . "');
            window.history.back();
        </script>";
    }
} else {
    header("Location: ../pages/all-complaints.php");
    exit();
}
