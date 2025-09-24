<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])) {
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .form-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
        }

        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .form-input:focus {
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }

        .preview-image {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .preview-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="form-container p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-[#800000] mb-8">Submit A Complaint/Concern</h1>

            <form action="../logic/submit_complaint_concern_logic.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="space-y-2">
                    <label for="complaint_type" class="block text-sm font-medium text-gray-700">Type of Complaint/Concern <span class="text-red-500">*</span></label>
                    <select name="complaint_type" id="complaint_type" required
                        class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
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
                <div class="space-y-2" id="other_specify_group" style="display: none;">
                    <label for="other_specify" class="block text-sm font-medium text-gray-700">Specify Other Concern</label>
                    <input type="text" name="other_specify" id="other_specify"
                        class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label for="severity" class="block text-sm font-medium text-gray-700">Severity Level <span class="text-red-500">*</span></label>
                    <select name="severity" id="severity" required
                        class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        <?php
                        $current_severity = $selected_row['severity'] ?? 'medium';
                        $severities = [
                            'low' => 'Low - General guidance needed',
                            'medium' => 'Medium - Moderate concern',
                            'high' => 'High - Urgent attention required',
                            'urgent' => 'Urgent - Immediate intervention needed'
                        ];

                        foreach ($severities as $value => $label) {
                            $selected = ($current_severity == $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select the severity level that best describes your situation</p>
                </div>

                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description <span class="text-red-500">*</span></label>
                    <?php
                    if (!isset($selected_row['description']) || $selected_row['description'] == null) {
                        echo '<textarea name="description" id="description" rows="6" minlength="10" required 
                               class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"
                               placeholder="Please provide a detailed description of your complaint/concern (minimum 100 characters)"></textarea>';
                    } else {
                    ?>
                        <textarea name="description" id="description" rows="6" minlength="10" required
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"
                            placeholder="Please provide a detailed description of your complaint/concern (minimum 100 characters)"><?= $selected_row['description'] ?></textarea>
                    <?php
                    }
                    ?>
                </div>

                <div class="space-y-2">
                    <label for="evidence" class="block text-sm font-medium text-gray-700">Upload Evidence (optional)</label>

                    <?php if (!empty($selected_row['evidence'])): ?>
                        <div class="mb-4">
                            <img src="data:<?php echo $selected_row['mime_type']; ?>;base64,<?php echo base64_encode($selected_row['evidence']); ?>"
                                alt="Evidence"
                                class="preview-image max-w-xs" />
                        </div>
                        <input type="hidden" name="existing_evidence"
                            value="<?php echo base64_encode($selected_row['evidence']); ?>" />
                        <input type="hidden" name="existing_mime_type"
                            value="<?php echo htmlspecialchars($selected_row['mime_type']); ?>" />
                    <?php endif; ?>

                    <div class="flex items-center space-x-4">
                        <input type="file" name="evidence" id="evidence"
                            accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Accepted formats: Images (jpg, jpeg, png), PDF, Word documents (doc, docx)</p>
                </div>

                <div class="space-y-2">
                    <label for="counseling_date" class="block text-sm font-medium text-gray-700">Preferred Counseling Date (optional)</label>
                    <?php
                    if (!isset($selected_row['preferred_counseling_date']) || $selected_row['preferred_counseling_date'] == null) {
                        echo '<input type="date" name="counseling_date" id="counseling_date" 
                                   class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">';
                    } else {
                    ?>
                        <input type="date" name="counseling_date" id="counseling_date"
                            value="<?= $selected_row['preferred_counseling_date'] ?>"
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    <?php
                    }
                    ?>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Counseling sessions are available Monday-Friday during school hours (8:00 AM - 5:00 PM). Weekends are not available.
                    </p>
                </div>

                <input type="hidden" name="isUpdate" value="<?= $id ?>">

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        Submit Complaint/Concern
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script>
        // Show/hide other specify field based on complaint type selection
        document
            .getElementById("complaint_type")
            .addEventListener("change", function() {
                const otherSpecifyGroup = document.getElementById("other_specify_group");
                if (this.value === "others") {
                    otherSpecifyGroup.style.display = "block";
                } else {
                    otherSpecifyGroup.style.display = "none";
                }
            });

        // Set minimum date and validate weekends for counseling date
        document.addEventListener("DOMContentLoaded", function() {
            const counselingDateInput = document.getElementById("counseling_date");

            if (counselingDateInput) {
                // Function to get next Monday
                function getNextWeekday(date) {
                    const nextDate = new Date(date);
                    const dayOfWeek = nextDate.getDay();

                    // Calculate days to add to get to next Monday
                    let daysToAdd = 0;
                    if (dayOfWeek === 0) { // Sunday
                        daysToAdd = 1;
                    } else if (dayOfWeek === 6) { // Saturday  
                        daysToAdd = 2;
                    }

                    nextDate.setDate(nextDate.getDate() + daysToAdd);
                    return nextDate;
                }

                // Get today's date, but skip to next weekday if today is weekend
                const today = new Date();
                let minDate = today;

                // If today is Saturday (6) or Sunday (0), set min to next Monday
                if (today.getDay() === 0 || today.getDay() === 6) {
                    minDate = getNextWeekday(today);
                }

                const yyyy = minDate.getFullYear();
                const mm = String(minDate.getMonth() + 1).padStart(2, "0");
                const dd = String(minDate.getDate()).padStart(2, "0");
                const minDateString = `${yyyy}-${mm}-${dd}`;

                // Set min attribute so past dates and weekends are disabled
                counselingDateInput.setAttribute("min", minDateString);

                // Validate selected date for weekends and past dates
                function validateWeekday(selectedValue) {
                    if (!selectedValue) return true; // Allow empty values

                    const selectedDate = new Date(selectedValue);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // Reset time to compare dates only
                    selectedDate.setHours(0, 0, 0, 0);

                    // Check if selected date is in the past
                    if (selectedDate < today) {
                        alert(
                            "Past dates are not allowed. Please select a current or future date."
                        );
                        counselingDateInput.value = "";
                        return false;
                    }

                    const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 6 = Saturday

                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        // Calculate next Monday - avoid timezone issues
                        const nextMonday = new Date(selectedDate);
                        if (dayOfWeek === 0) { // Sunday
                            nextMonday.setDate(nextMonday.getDate() + 1);
                        } else { // Saturday
                            nextMonday.setDate(nextMonday.getDate() + 2);
                        }

                        // Format date as YYYY-MM-DD without timezone issues
                        const year = nextMonday.getFullYear();
                        const month = String(nextMonday.getMonth() + 1).padStart(2, '0');
                        const day = String(nextMonday.getDate()).padStart(2, '0');
                        const nextMondayString = `${year}-${month}-${day}`;

                        alert(
                            "Weekend selected. Counseling sessions are only available on weekdays. " +
                            "Automatically changed to next available weekday: " +
                            nextMonday.toLocaleDateString("en-US", {
                                weekday: "long",
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                            })
                        );

                        counselingDateInput.value = nextMondayString;
                        return false;
                    }

                    return true;
                }

                // Add event listener to validate on date selection
                counselingDateInput.addEventListener("change", function() {
                    validateWeekday(this.value);
                });

                // Prevent form submission with invalid dates
                const form = counselingDateInput.closest("form");
                if (form) {
                    form.addEventListener("submit", function(e) {
                        if (counselingDateInput.value) {
                            const selectedDate = new Date(counselingDateInput.value);
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            selectedDate.setHours(0, 0, 0, 0);

                            // Check for past dates
                            if (selectedDate < today) {
                                e.preventDefault();
                                alert(
                                    "Past dates are not allowed. Please select a current or future date."
                                );
                                return;
                            }

                            // Check for weekends
                            if (!validateWeekday(counselingDateInput.value)) {
                                e.preventDefault();
                                alert("Please select a valid counseling date (weekdays only).");
                                return;
                            }
                        }
                    });
                }
            }
        });
    </script>
</body>

</html>