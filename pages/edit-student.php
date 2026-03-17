<?php
session_start();
require_once '../logic/db_connection.php';
require_once '../logic/sql_querries.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

// Fetch student data
try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.email, p.parent_name, p.contact_number, p.id as parent_id
        FROM " . TBL_STUDENTS . " s
        LEFT JOIN " . TBL_USERS . " u ON s.user_id = u.id
        LEFT JOIN parents p ON s.id = p.student_id
        WHERE s.id = ?
    ");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header("Location: students-list.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching student: " . $e->getMessage());
    header("Location: students-list.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update student information
        $stmt = $pdo->prepare("
            UPDATE " . TBL_STUDENTS . " 
            SET first_name = ?, middle_name = ?, last_name = ?, 
                grade_level = ?, section = ?, phone_number = ?, address = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['middle_name'],
            $_POST['last_name'],
            $_POST['grade_level'],
            $_POST['section'],
            $_POST['phone_number'],
            $_POST['address'],
            $student_id
        ]);

        // Update parent information
        if (!empty($_POST['parent_name']) || !empty($_POST['contact_number'])) {
            if ($student['parent_id']) {
                // Update existing parent
                $stmt = $pdo->prepare("
                    UPDATE parents 
                    SET parent_name = ?, contact_number = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['parent_name'],
                    $_POST['contact_number'],
                    $student['parent_id']
                ]);
            } else {
                // Insert new parent
                $stmt = $pdo->prepare("
                    INSERT INTO parents (parent_name, contact_number, student_id)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['parent_name'],
                    $_POST['contact_number'],
                    $student_id
                ]);
            }
        }

        $success = "Student information updated successfully!";
        
        // Refresh student data
        $stmt = $pdo->prepare("
            SELECT s.*, u.email, p.parent_name, p.contact_number, p.id as parent_id
            FROM " . TBL_STUDENTS . " s
            LEFT JOIN " . TBL_USERS . " u ON s.user_id = u.id
            LEFT JOIN parents p ON s.id = p.student_id
            WHERE s.id = ?
        ");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error updating student: " . $e->getMessage());
        $error = "Error updating student information. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navigation-admin.php'; ?>
    <div class="main-content">
        <main class="max-w-4xl mx-auto px-4 py-8">
            <div class="mb-8">
                <a href="students-list.php" class="text-[#800000] hover:text-[#600000] flex items-center gap-2 mb-4">
                    <i class="fas fa-arrow-left"></i>
                    Back to Students List
                </a>
                <h1 class="text-3xl font-bold text-[#800000] mb-2">Edit Student</h1>
                <p class="text-gray-600">Update student and parent/guardian information</p>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <p class="text-green-800"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                    <p class="text-red-800"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="bg-white rounded-lg shadow-lg p-8">
                <!-- Student Information Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b-2 border-[#800000]">Student Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" name="middle_name" value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Grade Level</label>
                            <select name="grade_level" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200" required>
                                <option value="">Select Grade Level</option>
                                <option value="7" <?php echo $student['grade_level'] === '7' ? 'selected' : ''; ?>>Grade 7</option>
                                <option value="8" <?php echo $student['grade_level'] === '8' ? 'selected' : ''; ?>>Grade 8</option>
                                <option value="9" <?php echo $student['grade_level'] === '9' ? 'selected' : ''; ?>>Grade 9</option>
                                <option value="10" <?php echo $student['grade_level'] === '10' ? 'selected' : ''; ?>>Grade 10</option>
                                <option value="11" <?php echo $student['grade_level'] === '11' ? 'selected' : ''; ?>>Grade 11</option>
                                <option value="12" <?php echo $student['grade_level'] === '12' ? 'selected' : ''; ?>>Grade 12</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                            <input type="text" name="section" value="<?php echo htmlspecialchars($student['section'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($student['phone_number'] ?? ''); ?>" 
                                   pattern="09[0-9]{9}" maxlength="11" placeholder="09XXXXXXXXX" oninput="validatePhoneNumber('phone_number')"
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200">
                            <span id="phone_number_status" class="text-xs text-red-600 mt-1 block"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Parent/Guardian Information Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 pb-4 border-b-2 border-[#800000]">Parent/Guardian Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Name</label>
                            <input type="text" name="parent_name" value="<?php echo htmlspecialchars($student['parent_name'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Phone Number</label>
                            <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($student['contact_number'] ?? ''); ?>" 
                                   pattern="09[0-9]{9}" maxlength="11" placeholder="09XXXXXXXXX" oninput="validatePhoneNumber('contact_number')"
                                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200">
                            <span id="contact_number_status" class="text-xs text-red-600 mt-1 block"></span>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                    <a href="students-list.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Phone number validation function
        function validatePhoneNumber(fieldId) {
            const phoneInput = document.getElementById(fieldId);
            const phoneStatus = document.getElementById(fieldId + '_status');
            const saveButton = document.querySelector('button[type="submit"]');
            const phoneValue = phoneInput.value;

            // Remove non-numeric characters
            const numericOnly = phoneValue.replace(/[^0-9]/g, '');
            
            // Update input with numeric only
            if (phoneValue !== numericOnly) {
                phoneInput.value = numericOnly;
            }

            // Clear status if empty
            if (!numericOnly) {
                phoneStatus.textContent = '';
                phoneInput.style.border = '1px solid #d1d5db';
                return;
            }

            // Validate format: must start with 09 and be exactly 11 digits
            const isValid = /^09[0-9]{9}$/.test(numericOnly);

            if (!isValid) {
                if (numericOnly.length < 11) {
                    phoneStatus.textContent = 'Phone number must be 11 digits starting with 09';
                } else if (!numericOnly.startsWith('09')) {
                    phoneStatus.textContent = 'Phone number must start with 09';
                } else {
                    phoneStatus.textContent = 'Invalid phone number format';
                }
                phoneStatus.style.color = 'red';
                phoneInput.style.border = '2px solid red';
            } else {
                phoneStatus.textContent = 'Valid phone number';
                phoneStatus.style.color = 'green';
                phoneInput.style.border = '2px solid green';
            }
        }

        // Validate phone numbers on page load if they have values
        document.addEventListener('DOMContentLoaded', function() {
            const phoneNumber = document.getElementById('phone_number');
            const contactNumber = document.getElementById('contact_number');

            if (phoneNumber && phoneNumber.value) {
                validatePhoneNumber('phone_number');
            }

            if (contactNumber && contactNumber.value) {
                validatePhoneNumber('contact_number');
            }
        });
    </script>
</body>
</html>
