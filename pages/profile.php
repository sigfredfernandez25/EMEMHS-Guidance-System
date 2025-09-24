<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
}

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare(SQL_GET_STUDENT);
$stmt->execute([$student_id]);
$studentDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get student complaint statistics
$complaintStats = [];
$mostCommonComplaint = null;

try {
    // Get complaint statistics
    $stmt = $pdo->prepare(SQL_GET_STUDENT_COMPLAINT_STATS);
    $stmt->execute([$student_id]);
    $complaintStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get most common complaint
    $stmt = $pdo->prepare(SQL_GET_STUDENT_MOST_COMMON_COMPLAINT);
    $stmt->execute([$student_id]);
    $mostCommonComplaint = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get total complaints count
    $totalComplaints = array_sum(array_column($complaintStats, 'type_count'));

} catch (PDOException $e) {
    error_log("Error fetching complaint statistics: " . $e->getMessage());
    $complaintStats = [];
    $mostCommonComplaint = null;
    $totalComplaints = 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        // Debug: Log the received data
        error_log("Received POST data: " . print_r($_POST, true));
        error_log("Current student_id: " . $student_id);

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'grade_level', 'section'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Required fields are missing: " . implode(", ", $missing_fields));
        }

        // First, verify the user exists
        $check_stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $check_stmt->execute([$student_id]);
        $user_exists = $check_stmt->fetch();

        if (!$user_exists) {
            throw new Exception("User not found with ID: " . $student_id);
        }

        // Prepare the update query with correct field names
        $update_query = "UPDATE students SET 
            first_name = :first_name,
            middle_name = :middle_name,
            last_name = :last_name,
            grade_level = :grade_level,
            section = :section
            WHERE id = :student_id";

        $stmt = $pdo->prepare($update_query);
        
        // Bind parameters
        $params = [
            ':first_name' => trim($_POST['first_name']),
            ':middle_name' => trim($_POST['middle_name']),
            ':last_name' => trim($_POST['last_name']),
            ':grade_level' => trim($_POST['grade_level']),
            ':section' => trim($_POST['section']),
            ':student_id' => $student_id
        ];

        // Debug: Log the parameters
        error_log("Update parameters: " . print_r($params, true));

        // Execute the update
        $result = $stmt->execute($params);

        // Debug: Log the result
        error_log("Update result: " . ($result ? "true" : "false"));
        if (!$result) {
            error_log("PDO Error Info: " . print_r($stmt->errorInfo(), true));
        }

        if ($result) {
            // Verify the update
            $verify_stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $verify_stmt->execute([$student_id]);
            $updated_data = $verify_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug: Log the updated data
            error_log("Updated data: " . print_r($updated_data, true));

            // Refresh the page to show updated data
            header("Location: profile.php?success=1");
            exit;
        } else {
            throw new Exception("Update failed: " . implode(", ", $stmt->errorInfo()));
        }
    } catch (Exception $e) {
        $error = "Failed to update profile: " . $e->getMessage();
        // Log the error for debugging
        error_log("Profile update error: " . $e->getMessage());
    }
}

// Debug: Log the current session data
error_log("Session data: " . print_r($_SESSION, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EMEMHS Guidance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Mobile-first modal design */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 50;
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-content {
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal.active .modal-content {
            transform: translateY(0);
            opacity: 1;
        }

        /* Mobile-optimized form inputs */
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }

        .form-input:focus {
            outline: none;
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        /* Profile card design */
        .profile-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        /* Stats card design */
        .stats-card {
            background: linear-gradient(135deg, #800000 0%, #600000 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        /* Touch-friendly buttons */
        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #600000 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        @media (min-width: 768px) {
            .btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
            }
        }

        .btn-secondary {
            background: white;
            color: #800000;
            border: 2px solid #800000;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:active {
            transform: scale(0.98);
        }

        @media (min-width: 768px) {
            .btn-secondary:hover {
                background: #800000;
                color: white;
                transform: translateY(-1px);
            }
        }

        /* Mobile spacing */
        .mobile-section {
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .mobile-section {
                margin-bottom: 2rem;
            }
        }

        /* Progress bars */
        .progress-bar {
            height: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 0.25rem;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-[Inter]">
    <?php include 'navigation.php'; ?>

    <main class="px-4 py-4 sm:py-6 lg:px-8 max-w-6xl mx-auto">
        <!-- Mobile-First Notifications -->
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-800 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Profile updated successfully! ✅
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-800 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo $error; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mobile-First Header -->
        <div class="mobile-section">
            <div class="text-center sm:text-left">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                    My Profile
                </h1>
                <p class="text-sm sm:text-base text-gray-600">
                    View and manage your personal information
                </p>
            </div>
        </div>

        <?php if (!empty($studentDetails)) {
            $student = $studentDetails[0];
        ?>
            <!-- Mobile-First Profile Card -->
            <div class="profile-card mobile-section">
                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-[#800000] to-[#600000] px-4 sm:px-6 lg:px-8 py-6 text-white relative overflow-hidden">
                    <!-- Background decoration -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full transform translate-x-16 -translate-y-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full transform -translate-x-12 translate-y-12"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
                            <!-- Avatar -->
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="h-12 w-12 sm:h-16 sm:w-16 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            
                            <!-- User Info -->
                            <div class="text-center sm:text-left flex-1">
                                <h2 class="text-xl sm:text-2xl font-bold mb-1">
                                    <?= htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']) ?>
                                </h2>
                                <p class="text-white/80 text-sm sm:text-base mb-2">
                                    <?= htmlspecialchars($student['email']) ?>
                                </p>
                                <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-1 sm:space-y-0 sm:space-x-4 text-sm text-white/70">
                                    <span>Grade <?= htmlspecialchars($student['grade_level']) ?></span>
                                    <span class="hidden sm:inline">•</span>
                                    <span>Section <?= htmlspecialchars($student['section']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Edit Button (Mobile) -->
                            <button onclick="openEditModal()" class="sm:hidden btn-secondary bg-white/20 border-white/30 text-white hover:bg-white/30">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
                                <div class="p-2 bg-[#800000]/10 rounded-lg mr-3">
                                    <svg class="h-5 w-5 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                Personal Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">First Name</label>
                                    <p class="text-base font-semibold text-gray-900 mt-1"><?= htmlspecialchars($student['first_name']) ?></p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Middle Name</label>
                                    <p class="text-base font-semibold text-gray-900 mt-1"><?= htmlspecialchars($student['middle_name'] ?: 'Not specified') ?></p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Name</label>
                                    <p class="text-base font-semibold text-gray-900 mt-1"><?= htmlspecialchars($student['last_name']) ?></p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email Address</label>
                                    <p class="text-base font-semibold text-gray-900 mt-1"><?= htmlspecialchars($student['email']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-4">
                                <div class="p-2 bg-[#800000]/10 rounded-lg mr-3">
                                    <svg class="h-5 w-5 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                Academic Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Grade Level</label>
                                    <p class="text-base font-semibold text-gray-900 mt-1">Grade <?= htmlspecialchars($student['grade_level']) ?></p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Section</label>
                                    <p class="text-base font-semibold text-gray-900 mt-1">Section <?= htmlspecialchars($student['section']) ?></p>
                                </div>
                                
                                <!-- Quick Actions (Desktop) -->
                                <div class="hidden sm:block pt-4">
                                    <button onclick="openEditModal()" class="btn-secondary w-full">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Action Button -->
                    <div class="sm:hidden mt-6 pt-6 border-t border-gray-200">
                        <button onclick="openEditModal()" class="btn-secondary w-full">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Complaint Statistics Section -->
            <div class="profile-card mobile-section">
                <div class="p-4 sm:p-6 lg:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-6">
                        <div class="p-2 bg-[#800000]/10 rounded-lg mr-3">
                            <svg class="h-5 w-5 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        Activity Statistics
                    </h3>

                    <?php if ($totalComplaints > 0): ?>
                        <!-- Stats Overview -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <!-- Total Complaints -->
                            <div class="stats-card">
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2 bg-white/20 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-2xl font-bold"><?= $totalComplaints ?></span>
                                    </div>
                                    <h4 class="font-semibold text-white/90 mb-2">Total Complaints</h4>
                                    <p class="text-xs text-white/70">Submitted to guidance office</p>
                                </div>
                            </div>

                            <!-- Most Common Type -->
                            <div class="stats-card">
                                <div class="relative z-10">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="p-2 bg-white/20 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                        </div>
                                        <span class="text-lg font-bold">
                                            <?= $mostCommonComplaint ? $mostCommonComplaint['count'] : '0' ?>x
                                        </span>
                                    </div>
                                    <h4 class="font-semibold text-white/90 mb-2">Most Common</h4>
                                    <p class="text-xs text-white/70">
                                        <?= $mostCommonComplaint ? htmlspecialchars(ucfirst($mostCommonComplaint['type'])) : 'None yet' ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Complaint Breakdown -->
                        <?php if (!empty($complaintStats)): ?>
                            <div>
                                <h4 class="text-base font-semibold text-gray-900 mb-4">Complaint Types</h4>
                                <div class="space-y-3">
                                    <?php foreach ($complaintStats as $stat): ?>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-900">
                                                    <?= htmlspecialchars(ucfirst($stat['type'])) ?>
                                                </span>
                                                <span class="text-sm font-semibold text-gray-600">
                                                    <?= $stat['type_count'] ?>
                                                </span>
                                            </div>
                                            <div class="progress-bar bg-gray-200">
                                                <div class="progress-fill bg-[#800000]" 
                                                     style="width: <?= ($stat['type_count'] / $totalComplaints) * 100 ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">No Activity Yet</h4>
                            <p class="text-gray-600 mb-4">You haven't submitted any complaints yet.</p>
                            <a href="complaint-concern-form.php" class="btn-primary inline-flex">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Submit Your First Complaint
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile-First Edit Profile Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-[#800000] to-[#600000] px-6 py-4 rounded-t-xl">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-white">Edit Profile ✏️</h3>
                            <button onclick="closeEditModal()" class="text-white/80 hover:text-white p-1">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Content -->
                    <div class="p-6">
                        <form action="profile.php" method="POST" class="space-y-5">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <!-- Form Fields -->
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" class="form-input" required>
                                </div>
                                
                                <div>
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']) ?>" class="form-input" placeholder="Optional">
                                </div>
                                
                                <div>
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" class="form-input" required>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Grade Level *</label>
                                        <select name="grade_level" class="form-input" required>
                                            <option value="">Select Grade</option>
                                            <?php for ($i = 7; $i <= 12; $i++): ?>
                                                <option value="<?= $i ?>" <?= $student['grade_level'] == $i ? 'selected' : '' ?>>
                                                    Grade <?= $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="form-label">Section *</label>
                                        <input type="text" name="section" value="<?= htmlspecialchars($student['section']) ?>" class="form-input" required placeholder="e.g., A, B, C">
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                                <button type="button" onclick="closeEditModal()" class="btn-secondary order-2 sm:order-1">
                                    Cancel
                                </button>
                                <button type="submit" class="btn-primary order-1 sm:order-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <!-- No Profile Found State -->
            <div class="profile-card text-center p-8">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center">
                    <svg class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Profile Not Found</h3>
                <p class="text-gray-600 mb-4">We couldn't find your student profile information.</p>
                <a href="student_dashboard.php" class="btn-primary inline-flex">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Go to Dashboard
                </a>
            </div>
        <?php } ?>
    </main>

    <script>
        // Mobile-first modal functionality
        function openEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Focus first input for better UX
            setTimeout(() => {
                const firstInput = modal.querySelector('input[type="text"]');
                if (firstInput) firstInput.focus();
            }, 300);
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editModal');
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEditModal();
                }
            });

            // Close modal when pressing Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeEditModal();
                }
            });

            // Form validation
            const form = modal.querySelector('form');
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('input[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        field.classList.remove('border-red-500');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    // Show error message or highlight fields
                }
            });

            // Auto-hide success/error messages
            const notifications = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>