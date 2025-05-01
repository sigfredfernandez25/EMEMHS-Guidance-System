<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";

}
$selected_row = null;
$id = "0";
if (isset($_POST['user'])) {
    $id = $_POST['user'];
    $stmt = $pdo->prepare(SQL_LIST_COMPLAINTS_CONCERNS_BY_ID);
    $stmt->execute([$id]);
    $selected_row = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit A Complaint/Concern</title>
</head>

<body>
    <nav>
        <ul>
            <li><a href="student_dashboard.php">Home</a></li>
            <li><a href="student_dashboard.php">Complaint/Concern</a></li>
            <li><a href="lost_item.php">Lost Item</a></li>
            <li><a href="notifications.php">Notification</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../logic/logout_logic.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <h1>Submit A Complaint/Concern</h1>

        <form action="../logic/submit_complaint_concern_logic.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="complaint_type">Type of Complaint/Concern:</label>

                <select name="complaint_type" id="complaint_type" required>
                    <?php
                    if (!isset($selected_row['type']) || $selected_row['type'] == null) {
                        echo "<option value=''>Select a type</option>";
                    } else {
                    ?>
                        <option value="<?= $selected_row['type'] ?>"><?= $selected_row['type'] ?></option>
                    <?php
                    }
                    ?>
                    <option value="bullying">Bullying</option>
                    <option value="family_problems">Family Problems</option>
                    <option value="academic_stress">Academic Stress</option>
                    <option value="mental_health">Mental Health Concerns</option>
                    <option value="peer_relationship">Peer Relationship Problems</option>
                    <option value="financial">Financial Problems</option>
                    <option value="physical_health">Physical Health Concerns</option>
                    <option value="romantic">Romantic Relationship Problems</option>
                    <option value="career">Career Guidance</option>
                    <option value="others">Others</option>
                </select>
            </div>

            <div class="form-group" id="other_specify_group" style="display: none;">
                <label for="other_specify">Specify Other Concern:</label>

                <input type="text" name="other_specify" id="other_specify">
            </div>

            <div class="form-group">
                <label for="description">Detailed Description:</label>
                <?php
                if (!isset($selected_row['description']) || $selected_row['description'] == null) {
                    echo '  <textarea name="description" id="description" rows="6" minlength="10" required 
                    placeholder="Please provide a detailed description of your complaint/concern (minimum 100 characters)"></textarea>';
                } else {
                ?>
                    <textarea name="description" id="description" rows="6" minlength="10" required
                        placeholder="Please provide a detailed description of your complaint/concern (minimum 100 characters)"><?= $selected_row['description'] ?></textarea>
                <?php
                }
                ?>
            </div>

            <div class="form-group">
                <label for="evidence">Upload Evidence (optional):</label>

                <!-- Existing thumbnail preview -->
                <?php if (!empty($selected_row['evidence'])): ?>
                    <img src="data:<?php echo $selected_row['mime_type']; ?>;base64,<?php echo base64_encode($selected_row['evidence']); ?>"
                        alt="Evidence"
                        style="max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;" />

                    <!-- Hidden input to pass existing image only if no new file is uploaded -->
                    <input type="hidden" name="existing_evidence"
                        value="<?php echo base64_encode($selected_row['evidence']); ?>" />
                    <input type="hidden" name="existing_mime_type"
                        value="<?php echo htmlspecialchars($selected_row['mime_type']); ?>" />
                <?php endif; ?>

                <!-- File input -->
                <input type="file" name="evidence" id="evidence" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                <small>Accepted formats: Images (jpg, jpeg, png), PDF, Word documents (doc, docx)</small>
            </div>


            <div class="form-group">
                <label for="counseling_date">Preferred Counseling Date (optional):</label>
                <?php
                if (!isset($selected_row['preferred_counseling_date']) || $selected_row['preferred_counseling_date'] == null) {
                    echo '<input type="date" name="counseling_date" id="counseling_date">';
                } else {
                ?>
                    <input type="date" name="counseling_date" id="counseling_date" value="<?= $selected_row['preferred_counseling_date'] ?>">
                <?php
                }
                ?>
            </div>
            <input type="hidden" name="isUpdate" value="<?= $id ?>">
            <div class="form-group">
                <button type="submit">Submit Complaint/Concern</button>
            </div>
        </form>

    </main>
    <script src="../js/complaint-concern.js"></script>
</body>

</html>