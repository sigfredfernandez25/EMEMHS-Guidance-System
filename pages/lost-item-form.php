<?php
require_once '../logic/db_connection.php';
require_once '../logic/sql_querries.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";

}
$id = "0";
if (isset($_POST['user'])) {
    $id = $_POST['user'];
    $stmt = $pdo->prepare(SQL_GET_LOST_ITEMS_BY_ID);
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report a Lost Item</title>
    <style>
        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            height: 100px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .sms-group {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="student_dashboard.php">home</a></li>
            <li><a href="student_dashboard.php">Complaint/Concern</a></li>
            <li><a href="lost_item.php">Lost Item</a></li>
            <li><a href="notifications.php">Notification</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="../logic/logout_logic.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <h1>Report a Lost Item</h1>

        <form action="../logic/submit_lost_item_logic.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="item_name">Item Name:</label>
                <input type="text" name="item_name" id="item_name" required
                    placeholder="e.g., Wallet, Phone, ID, Umbrella"
                    value="<?= htmlspecialchars($item['item_name'] ?? '') ?>">

            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="">Select a category</option>
                    <?php
                    $categories = ["bag", "clothing", "electronic", "id_card", "stationery", "umbrella", "money", "others"];
                    $currentCategory = $item['category'] ?? '';

                    // Output predefined categories
                    foreach ($categories as $cat) {
                        $selected = ($currentCategory === $cat) ? 'selected' : '';
                        echo "<option value=\"$cat\" $selected>" . ucfirst(str_replace('_', ' ', $cat)) . "</option>";
                    }

                    // If the current category is not in the list and is not empty, add it as a custom option
                    if (!in_array($currentCategory, $categories) && !empty($currentCategory)) {
                        echo "<option value=\"$currentCategory\" selected>" . ucfirst($currentCategory) . "</option>";
                    }
                    ?>

                </select>

            </div>

            <div class="form-group" id="other_category_group" style="display: none;">
                <label for="other_category">Specify Other Category:</label>
                <input type="text" name="other_category" id="other_category"
                    value="<?= htmlspecialchars($item['other_category'] ?? '') ?>">

            </div>

            <div class="form-group">
                <label for="date_lost">Date Lost:</label>
                <input type="date" name="date_lost" id="date_lost" required
                    value="<?= htmlspecialchars($item['date_lost'] ?? '') ?>">

            </div>

            <div class="form-group">
                <label for="time_lost">Time Lost (optional):</label>
                <input type="time" name="time_lost" id="time_lost"
                    value="<?= htmlspecialchars($item['time_lost'] ?? '') ?>">

            </div>

            <div class="form-group">
                <label for="location">Last Seen Location:</label>
                <select name="location" id="location" required>
                    <option value="">Select a location</option>
                    <?php
                    $locations = ["classroom", "library", "hallway", "canteen", "gymnasium", "comfort_room", "others"];
                    $currentLocation = $item['location'] ?? '';

                    // Output predefined locations
                    foreach ($locations as $loc) {
                        $selected = ($currentLocation === $loc) ? 'selected' : '';
                        echo "<option value=\"$loc\" $selected>" . ucfirst(str_replace('_', ' ', $loc)) . "</option>";
                    }

                    // If current location is not in the list and is not empty, add it as a custom option
                    if (!in_array($currentLocation, $locations) && !empty($currentLocation)) {
                        echo "<option value=\"$currentLocation\" selected>" . ucfirst($currentLocation) . "</option>";
                    }
                    ?>

                </select>

            </div>

            <div class="form-group" id="classroom_details" style="display: none;">
                <label for="building_room">Building/Room Number:</label>
                <input type="text" name="building_room" id="building_room"
                    value="<?= htmlspecialchars($item['building_room'] ?? '') ?>">

            </div>

            <div class="form-group" id="other_location_group" style="display: none;">
                <label for="other_location">Specify Other Location:</label>
                <input type="text" name="other_location" id="other_location"
                    value="<?= htmlspecialchars($item['other_location'] ?? '') ?>">

            </div>

            <div class="form-group">
                <label for="description">Description of Item:</label>
                <textarea name="description" id="description" required><?= htmlspecialchars($item['description'] ?? '') ?></textarea>

            </div>

            <div class="form-group">
                <label for="photo">Upload Photo (if available):</label>

                <!-- Existing thumbnail preview -->
                <?php if (!empty($item['photo'])): ?>
                    <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                        alt="Uploaded Photo"
                        style="max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;" />

                    <!-- Hidden inputs to pass existing image and MIME type -->
                    <input type="hidden" name="existing_photo"
                        value="<?php echo base64_encode($item['photo']); ?>" />
                    <input type="hidden" name="existing_photo_mime_type"
                        value="<?php echo htmlspecialchars($item['mime_type']); ?>" />
                <?php endif; ?>

                <!-- File input -->
                <input type="file" name="photo" id="photo" accept="image/jpeg,image/png">
                <small>Accepted formats: JPG, PNG only</small>
            </div>



            <div class="form-group">
                <label>
                    <input type="checkbox" name="receive_sms" id="receive_sms"
                        <?= isset($item['receive_sms']) && $item['receive_sms'] ? 'checked' : '' ?>>

                    I want to receive SMS updates
                </label>
            </div>

            <div class="form-group sms-group" id="phone_number_group" style="display: none;">
                <label for="phone_number">Phone Number (optional):</label>
                <input type="text" name="phone_number" id="phone_number"
                    value="<?= htmlspecialchars($item['phone_number'] ?? '') ?>">

            </div>
            <input type="hidden" name="isUpdate" value="<?= $id ?>">

            <div class="form-group">
                <button type="submit">Submit Lost Item Report</button>
            </div>
        </form>
    </main>
    <script>
        // Show/hide other category field
        document.getElementById('category').addEventListener('change', function() {
            document.getElementById('other_category_group').style.display =
                this.value === 'others' ? 'block' : 'none';
        });

        // Show/hide classroom details and other location fields
        document.getElementById('location').addEventListener('change', function() {
            const location = this.value;
            document.getElementById('classroom_details').style.display =
                location === 'classroom' ? 'block' : 'none';
            document.getElementById('other_location_group').style.display =
                location === 'others' ? 'block' : 'none';
        });

        // Show/hide phone number field
        document.getElementById('receive_sms').addEventListener('change', function() {
            document.getElementById('phone_number_group').style.display =
                this.checked ? 'block' : 'none';
        });
    </script>
</body>

</html>