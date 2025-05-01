<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";

}
$student_id = $_SESSION['student_id'];


$stmt = $pdo->prepare(SQL_LIST_LOST_ITEMS_BY_STUDENT);
$stmt->execute([$student_id]);
$lost_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Items</title>
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
        <h1>Your Lost Items</h1>
        <section class="complaints-table">
            <button type="button" onclick="location.href='lost-item-form.php'">Report a Lost Item</button>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Photo</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Debug: Check the structure of the first complaint
                    if (!empty($complaints)) {
                        echo "<!-- Debug: First complaint data: " . print_r($complaints[0], true) . " -->";
                    }
                    ?>
                    <?php if (!empty($lost_items)): ?>
                        <?php foreach ($lost_items as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo $item['item_name']; ?></td>
                                <td><?php echo $item['category']; ?></td>
                                <td><?php echo $item['status']; ?></td>
                                <td><?php echo $item['date']; ?></td>

                                <td>
                                    <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                        <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>" alt="item photo" style="max-width: 100px; max-height: 100px;" />
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="lost-item-form.php" method="POST">
                                        <input type="hidden" name="user" id="user" value="<?= $item['id'] ?>">
                                        <button type="submit">edit</button>
                                    </form>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No lost items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>