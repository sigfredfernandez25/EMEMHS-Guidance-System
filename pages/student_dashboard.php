<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";

}
$student_id = $_SESSION['student_id'];


$stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT);
$stmt->execute([$student_id]);
$total_complaints = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'pending']);
$pending_complaints = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'scheduled']);
$scheduled_complaints = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'resolved']);
$resolved_complaints = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_LOST_ITEMS_BY_STUDENT);
$stmt->execute([$student_id]);
$total_lost_items = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_LOST_ITEMS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'pending']);
$pending_lost_items = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_LOST_ITEMS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'found']);
$found_lost_items = $stmt->fetchColumn();





?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>

<body>
    <nav>
        <ul>
            <li><a href="student_dashboard.php">Home</a></li>
            <li><a href="complaint-concern.php">Complaint/Concern</a></li>
            <li><a href="lost_item.php">Lost Item</a></li>
            <li><a href="notifications.php">Notification</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../logic/logout_logic.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h1>Student's Dashboard</h1>

        <!-- Complaint Statistics Section -->
        <section class="complaint-stats">
            <h2>Complaint Statistics</h2>
            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total Complaints</h3>
                    <p class="stat-number"><?= $total_complaints ?></p>
                </div>
                <div class="stat-box">
                    <h3>Pending</h3>
                    <p class="stat-number"><?= $pending_complaints ?></p>
                </div>
                <div class="stat-box">
                    <h3>Resolved</h3>
                    <p class="stat-number"><?= $resolved_complaints ?></p>
                </div>
                <div class="stat-box">
                    <h3>In Progress</h3>
                    <p class="stat-number"><?= $scheduled_complaints ?></p>
                </div>
            </div>
        </section>
        <section class="lost-item-stats">
            <h2>Lost Item Statistics</h2>
            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total Lost Items</h3>
                    <p class="stat-number"><?= $total_lost_items ?></p>
                </div>
                <div class="stat-box">
                    <h3>Pending</h3>
                    <p class="stat-number"><?= $pending_lost_items ?></p>
                </div>
                <div class="stat-box">
                    <h3>Found</h3>
                    <p class="stat-number"><?= $found_lost_items ?></p>
                </div>

            </div>
        </section>
    </main>
</body>

</html>