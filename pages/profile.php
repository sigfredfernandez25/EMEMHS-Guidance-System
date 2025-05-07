<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";

}
$student_id = $_SESSION['student_id'];

$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare(SQL_GET_STUDENT);
$stmt->execute([$student_id]);
$studentDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Student Profile</h2>

        <?php if (!empty($studentDetails)) {
            $student = $studentDetails[0];
        ?>
            <div style="border:1px solid #ccc; padding:20px; max-width:400px;">
                <div style="text-align:center;">
                    <div style="width:100px; height:100px; background:#ddd; border-radius:50%; margin:auto;"></div>
                    <h3><?= htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']) ?></h3>
                    <p><?= htmlspecialchars($student['email']) ?></p>
                </div>
                <hr>
                <ul style="list-style:none; padding:0;">
                    <li><strong>First Name:</strong> <?= htmlspecialchars($student['first_name']) ?></li>
                    <li><strong>Middle Name:</strong> <?= htmlspecialchars($student['middle_name']) ?></li>
                    <li><strong>Last Name:</strong> <?= htmlspecialchars($student['last_name']) ?></li>
                    <li><strong>Grade:</strong> <?= htmlspecialchars($student['grade_level']) ?></li>
                    <li><strong>Section:</strong> <?= htmlspecialchars($student['section']) ?></li>
                </ul>
            </div>
    </main>
<?php } else { ?>
    <p>No student data found.</p>
<?php } ?>
</body>

</html>