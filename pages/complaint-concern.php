<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";

}
$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare(SQL_LIST_COMPLAINTS_CONCERNS_BY_STUDENT);
$stmt->execute([$student_id]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint/Concern</title>
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
        <h1>Your Complaints</h1>
        <section class="complaints-table">
            <button type="button" onclick="location.href='complaint-concern-form.php'">Add Complaint</button>
            <table>
                <thead>
                    <tr>
                        <th>Complaint ID</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Preferred Counseling Date</th>
                        <th>Scheduled Counseling Date</th>
                        <th>Evidence</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Debug: Check the structure of the first complaint
                    if (!empty($complaints)) {
                        echo "<!-- Debug: First complaint data: " . print_r($complaints[0], true) . " -->";
                    }
                    ?>
                    <?php if (!empty($complaints)): ?>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><?php echo $complaint['id']; ?></td>
                                <td><?php echo $complaint['type']; ?></td>
                                <td><?php echo $complaint['description']; ?></td>
                                <td><?php echo $complaint['status']; ?></td>
                                <td><?php echo $complaint['preferred_counseling_date']; ?></td>

                                <td>

                                    <?php
                                    if ($complaint['status'] == 'pending') {
                                        echo 'N/A';
                                    } else if ($complaint['status'] == 'scheduled') {
                                        echo $complaint['scheduled_date'];
                                    } else if ($complaint['status'] == 'resolved') {
                                        echo $complaint['scheduled_date'] . " (Resolved)";
                                    }
                                    ?>

                                </td>
                                <td>
                                    <?php if (!empty($complaint['evidence']) && !empty($complaint['mime_type'])): ?>
                                        <img src="data:<?php echo $complaint['mime_type']; ?>;base64,<?php echo base64_encode($complaint['evidence']); ?>" alt="Evidence" style="max-width: 100px; max-height: 100px;" />
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="complaint-concern-form.php" method="POST">
                                        <input type="hidden" name="user" id="user" value="<?= $complaint['id'] ?>">
                                        <button type="submit">edit</button>
                                    </form>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No complaints found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>