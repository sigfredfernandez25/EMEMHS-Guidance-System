    <?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: login.php");
    exit();
}

// Check if a specific complaint ID is provided
$specific_complaint_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$specific_complaint = null;

if ($specific_complaint_id) {
    // Fetch the specific complaint
    $stmt = $pdo->prepare("
        SELECT cc.*,
               COALESCE(cc.severity, 'medium') as severity,
               s.first_name, s.last_name, s.grade_level, s.section, s.email
        FROM " . TBL_COMPLAINTS_CONCERNS . " cc
        JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
        WHERE cc.id = ?
    ");
    $stmt->execute([$specific_complaint_id]);
    $specific_complaint = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("
    SELECT cc.*,
           COALESCE(cc.severity, 'medium') as severity,
           s.first_name, s.last_name, s.grade_level, s.section
    FROM " . TBL_COMPLAINTS_CONCERNS . " cc
    JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
    WHERE cc.status = 'pending'
    ORDER BY cc.date_created DESC, cc.time_created DESC
");
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count pending complaints
$pending_complaints = count($complaints);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add EmailJS Script -->
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <style>
        :root {
            --primary-color: #800000;
            --primary-hover: #600000;
            --secondary-color: #64748b;
            --background-color: #f8fafc;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: #1e293b;
        }

        .minimal-card {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .minimal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .minimal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .minimal-card:hover::before {
            opacity: 1;
        }

        .table-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border-bottom: 2px solid #e2e8f0;
        }

        .table-row {
            transition: all 0.2s ease;
        }

        .table-row:hover {
            background-color: rgba(128, 0, 0, 0.05);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .status-pending {
            background-color: rgba(128, 0, 0, 0.1);
            color: var(--primary-color);
        }

        .status-scheduled {
            background-color: rgba(128, 0, 0, 0.1);
            color: var(--primary-color);
        }

        .status-resolved {
            background-color: rgba(128, 0, 0, 0.1);
            color: var(--primary-color);
        }

        .severity-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .severity-low {
            background-color: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .severity-medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .severity-high {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .severity-urgent {
            background-color: rgba(139, 69, 19, 0.1);
            color: #a16207;
            animation: pulse 2s infinite;
        }

        .priority-indicator {
            width: 4px;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
            border-radius: 0.5rem 0 0 0.5rem;
        }

        .priority-urgent {
            background: linear-gradient(180deg, #dc2626 0%, #b91c1c 100%);
        }

        .priority-high {
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        }

        .priority-medium {
            background: linear-gradient(180deg, #eab308 0%, #ca8a04 100%);
        }

        .priority-low {
            background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
        }

        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn i {
            transition: transform 0.2s ease;
        }

        .action-btn:hover i {
            transform: translateX(2px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        .search-input {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background-color: white;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        .time-slot-btn {
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            text-align: center;
        }

        .time-slot-btn.available {
            background-color: rgba(128, 0, 0, 0.1);
            color: var(--primary-color);
        }

        .time-slot-btn.available:hover {
            background-color: rgba(128, 0, 0, 0.2);
        }

        .time-slot-btn.selected {
            background-color: var(--primary-color);
            color: white;
        }

        .time-slot-btn.unavailable {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        .tabs-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
        }

        .tabs-header {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1rem;
        }

        .tab-button {
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: var(--secondary-color);
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab-button:hover {
            color: var(--primary-color);
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-button i {
            font-size: 1.1rem;
        }

        .tab-content {
            display: none;
            padding: 1.5rem;
        }

        .tab-content.active {
            display: block;
        }

        .tab-badge {
            background-color: rgba(128, 0, 0, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'?>

<!-- Complaint Details Modal -->
<?php if ($specific_complaint): ?>
<div id="complaintDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-[#800000] to-[#a52a2a] text-white p-6 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">Complaint Details</h2>
                    <p class="text-white/80 text-sm mt-1">Complete information about this complaint</p>
                </div>
                <button onclick="window.location.href='complaint-concern-admin.php'" class="text-white hover:bg-white/20 rounded-full p-2 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Student Information -->
            <div class="bg-gray-50 rounded-xl p-4">
                <h3 class="text-lg font-semibold text-[#800000] mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    Student Information
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($specific_complaint['first_name'] . ' ' . $specific_complaint['last_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Grade & Section</p>
                        <p class="font-medium"><?php echo htmlspecialchars($specific_complaint['grade_level'] . ' - ' . $specific_complaint['section']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($specific_complaint['email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Complaint Information -->
            <div class="bg-gray-50 rounded-xl p-4">
                <h3 class="text-lg font-semibold text-[#800000] mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Complaint Details
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Complaint Type</p>
                        <p class="font-medium capitalize"><?php echo htmlspecialchars($specific_complaint['complaint_type']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Severity</p>
                        <span class="severity-badge severity-<?php echo strtolower($specific_complaint['severity']); ?>">
                            <?php 
                            $severity_labels = [
                                'low' => 'Low Priority',
                                'medium' => 'Medium Priority',
                                'high' => 'High Priority',
                                'urgent' => 'URGENT'
                            ];
                            echo htmlspecialchars($severity_labels[$specific_complaint['severity']] ?? ucfirst($specific_complaint['severity']));
                            ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="status-badge status-<?php echo strtolower($specific_complaint['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($specific_complaint['status'])); ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="font-medium bg-white p-3 rounded-lg"><?php echo nl2br(htmlspecialchars($specific_complaint['description'])); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Date Created</p>
                            <p class="font-medium"><?php echo date('F j, Y', strtotime($specific_complaint['date_created'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Time Created</p>
                            <p class="font-medium"><?php echo date('g:i A', strtotime($specific_complaint['time_created'])); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($specific_complaint['preferred_date'])): ?>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Preferred Date</p>
                            <p class="font-medium"><?php echo date('F j, Y', strtotime($specific_complaint['preferred_date'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Preferred Time</p>
                            <p class="font-medium"><?php echo htmlspecialchars($specific_complaint['preferred_time']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t">
                <?php if ($specific_complaint['status'] === 'pending'): ?>
                <button onclick="scheduleComplaint(<?php echo $specific_complaint['id']; ?>)" class="flex-1 bg-[#800000] text-white px-6 py-3 rounded-lg hover:bg-[#a52a2a] transition font-medium">
                    <i class="fas fa-calendar-alt mr-2"></i>Schedule Counseling
                </button>
                <?php endif; ?>
                <button onclick="window.location.href='complaint-concern-admin.php'" class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-medium">
                    Back to List
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function scheduleComplaint(complaintId) {
        // Redirect to set-schedule page or open schedule modal
        window.location.href = 'set-schedule.php?complaint_id=' + complaintId;
    }
</script>
<?php endif; ?>

<div class="main-content">
    <main class=" min-h-screen">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Pending Complaints</h1>
                    <p class="text-gray-600">Manage complaints awaiting schedule</p>
                </div>
                <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
            
            <!-- Search and Stats -->
            <div class="minimal-card p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                        </div>
                        <div>
                            <h2 class="section-title text-xl font-semibold text-[#800000]">Pending Complaints</h2>
                            <p class="text-gray-600 text-sm">Total pending: <?= $pending_complaints ?> complaints</p>
                        </div>
                    </div>
                    <div>
                        <input type="text" placeholder="Search complaints..." class="search-input" id="searchInput" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-header">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade & Section</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint Type</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred Date</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($complaints)): ?>
                                <?php foreach ($complaints as $complaint): ?>
                                    <tr class="table-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['first_name']." ".$complaint['last_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['grade_level']." ".$complaint['section']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['type']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $severity = $complaint['severity'] ?? 'medium';
                                            $severity_labels = [
                                                'low' => 'Low',
                                                'medium' => 'Medium',
                                                'high' => 'High',
                                                'urgent' => 'Urgent'
                                            ];
                                            $severity_class = 'severity-' . $severity;
                                            ?>
                                            <span class="severity-badge <?= $severity_class ?>">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <?= $severity_labels[$severity] ?? 'Medium' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php 
                                                $dateCreated = $complaint['date_created'] ?? '';
                                                if ($dateCreated) {
                                                    echo date('M d, Y', strtotime($dateCreated));
                                                } else {
                                                    echo 'N/A';
                                                }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['preferred_counseling_date']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button class="action-btn btn-primary set-schedule-btn" title="Set Schedule" data-complaint-id="<?= $complaint['id'] ?>">
                                                <i class="fas fa-calendar-plus"></i>
                                                    Schedule
                                                </button>
                                                <button class="action-btn btn-secondary view-complaint ml-2" title="View Details"
                                                        data-complaint='<?php
                                                            $complaintData = $complaint;
                                                            unset($complaintData['evidence']);
                                                            unset($complaintData['mime_type']);
                                                            unset($complaintData['audio_recording']);
                                                            unset($complaintData['audio_mime_type']);
                                                            echo json_encode($complaintData);
                                                        ?>'
                                                        data-evidence='<?php echo !empty($complaint['evidence']) ? base64_encode($complaint['evidence']) : ''; ?>'
                                                        data-mime-type='<?php echo !empty($complaint['mime_type']) ? htmlspecialchars($complaint['mime_type']) : ''; ?>'
                                                        data-audio='<?php echo !empty($complaint['audio_recording']) ? base64_encode($complaint['audio_recording']) : ''; ?>'
                                                        data-audio-mime='<?php echo !empty($complaint['audio_mime_type']) ? htmlspecialchars($complaint['audio_mime_type']) : ''; ?>'
                                                        data-audio-duration='<?php echo !empty($complaint['audio_duration']) ? $complaint['audio_duration'] : '0'; ?>'>
                                                    <i class="fas fa-eye"></i>
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Complaints</h3>
                                                <p class="text-sm text-gray-500">All complaints have been scheduled or resolved.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

    <!-- View Details Modal -->
    <div id="viewDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl modal-content">
            <div class="modal-header flex justify-between items-center">
                <h3 class="text-xl font-semibold text-[#800000]">Complaint Details</h3>
                <button id="closeViewModal" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="grid grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Student Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Student Information
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">First Name</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewFirstName"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Last Name</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewLastName"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Grade Level</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewGradeLevel"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Section</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewSection"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Complaint Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Complaint Information
                            </h4>
                            <div class="space-y-4">
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Type</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewType"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Description</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewDescription"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Status</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewStatus"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Schedule Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Schedule Information
                            </h4>
                            <div class="space-y-4">
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Preferred Date</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewPreferredDate"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Scheduled Date</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewScheduledDate"></p>
                                </div>
                                <div class="bg-white p-3 rounded-md shadow-sm">
                                    <p class="text-sm text-gray-500">Scheduled Time</p>
                                    <p class="text-sm font-medium text-gray-900" id="viewScheduledTime"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Evidence -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
                                <i class="fas fa-image mr-2"></i>
                                Evidence
                            </h4>
                            <div id="viewEvidence" class="bg-white p-3 rounded-md shadow-sm">
                                <!-- Evidence will be populated here -->
                            </div>
                        </div>

                        <!-- Audio Recording -->
                        <?php include 'components/audio-player.php'; ?>
                    </div>
                </div>
            </div>
            <!-- Modal Footer with Actions -->
            <div class="modal-footer flex justify-between items-center">
                <button id="sendParentSMS" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-sms"></i>
                    Send SMS to Parent
                </button>
                <button id="closeViewModalBtn" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Scheduling Modal -->
    <div id="schedulingModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm overflow-y-auto hidden">
        <div class="relative top-20 mx-auto p-6 w-[28rem] bg-white rounded-2xl shadow-xl transform transition-all">
            <div class="pb-4 border-b border-gray-100">
                <h3 class="text-xl font-semibold text-[#800000] flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    Schedule Counseling Session
                </h3>
            </div>
            <div class="py-6 space-y-6">
                <div>
                    <label for="scheduleDate" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                    <input type="date" id="scheduleDate" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200 ease-in-out">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
                    <div id="timeSlots" class="grid grid-cols-3 gap-3">
                        <!-- Time slots will be populated here -->
                    </div>
                </div>
                <input type="hidden" id="selectedComplaintId">
            </div>
            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <button id="cancelSchedule" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button id="confirmSchedule" class="px-4 py-2.5 rounded-xl bg-[#800000] text-white hover:bg-[#900000] transition duration-200 flex items-center gap-2">
                    <i class="fas fa-check"></i>
                    Confirm Schedule
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewModal = document.getElementById('viewDetailsModal');
            const closeViewModal = document.getElementById('closeViewModal');
            
            // Store current complaint ID for SMS
            let currentComplaintIdForSMS = null;

            // Function to show complaint details
            function showComplaintDetails(complaint, evidence, mimeType, audioData, audioMimeType, audioDuration) {
                // Store complaint ID for SMS
                currentComplaintIdForSMS = complaint.id;
                
                // Store student ID for fetching parent info
                const viewModal = document.getElementById('viewDetailsModal');
                viewModal.setAttribute('data-student-id', complaint.student_id);
                
                // Set student information
                document.getElementById('viewFirstName').textContent = complaint.first_name;
                document.getElementById('viewLastName').textContent = complaint.last_name;
                document.getElementById('viewGradeLevel').textContent = complaint.grade_level;
                document.getElementById('viewSection').textContent = complaint.section;

                // Set complaint information
                document.getElementById('viewType').textContent = complaint.type;
                document.getElementById('viewDescription').textContent = complaint.description || 'No description provided';
                document.getElementById('viewStatus').textContent = complaint.status;
                document.getElementById('viewPreferredDate').textContent = complaint.preferred_counseling_date || 'N/A';
                document.getElementById('viewScheduledDate').textContent = complaint.scheduled_date || 'N/A';
                document.getElementById('viewScheduledTime').textContent = complaint.scheduled_time || 'N/A';

                // Hide Send SMS button if complaint is resolved
                const sendSMSBtn = document.getElementById('sendParentSMS');
                if (complaint.status === 'resolved') {
                    sendSMSBtn.style.display = 'none';
                } else {
                    sendSMSBtn.style.display = 'inline-flex';
                }

                // Set evidence
                const evidenceContainer = document.getElementById('viewEvidence');
                if (evidence && mimeType) {
                    evidenceContainer.innerHTML = `
                        <div class="relative group">
                            <img src="data:${mimeType};base64,${evidence}" 
                                 alt="Evidence" 
                                 class="w-full h-auto object-cover rounded-lg shadow-sm transition-transform duration-300 group-hover:scale-105" />
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity duration-300 rounded-lg"></div>
                        </div>
                    `;
                } else {
                    evidenceContainer.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-image text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm text-gray-500">No evidence provided</p>
                        </div>
                    `;
                }

                // Display audio recording if available
                if (typeof displayAudioRecording === 'function') {
                    displayAudioRecording(audioData, audioMimeType, audioDuration);
                }

                // Show modal
                viewModal.classList.remove('hidden');
            }

            // Add click handlers for view buttons
            document.querySelectorAll('.view-complaint').forEach(button => {
                button.addEventListener('click', function() {
                    const complaintData = JSON.parse(this.dataset.complaint);
                    const evidence = this.dataset.evidence;
                    const mimeType = this.dataset.mimeType;
                    const audioData = this.dataset.audio;
                    const audioMimeType = this.dataset.audioMime;
                    const audioDuration = this.dataset.audioDuration;
                    showComplaintDetails(complaintData, evidence, mimeType, audioData, audioMimeType, audioDuration);
                });
            });

            // Close modal handler
            closeViewModal.addEventListener('click', function() {
                viewModal.classList.add('hidden');
            });
            
            // Close modal button handler
            document.getElementById('closeViewModalBtn').addEventListener('click', function() {
                viewModal.classList.add('hidden');
            });

            // Send Parent SMS handler - Open modal instead
            document.getElementById('sendParentSMS').addEventListener('click', async function() {
                if (!currentComplaintIdForSMS) {
                    alert('No complaint selected');
                    return;
                }

                // Fetch student ID from the view modal
                const viewModal = document.getElementById('viewDetailsModal');
                const studentId = viewModal?.getAttribute('data-student-id');
                
                if (!studentId) {
                    alert('Student information not found');
                    return;
                }

                // Fetch existing parent information from database
                try {
                    const response = await fetch('../logic/get_parent_info.php?student_id=' + studentId);
                    const data = await response.json();
                    
                    if (data.success && data.parent) {
                        // Pre-fill the form with existing parent data
                        document.getElementById('parentName').value = data.parent.parent_name || '';
                        document.getElementById('parentPhone').value = data.parent.contact_number || '';
                        
                        // Update modal title to indicate data is from database
                        const modalTitle = document.querySelector('#parentInfoModal h3');
                        if (data.parent.parent_name && data.parent.contact_number) {
                            modalTitle.innerHTML = '<i class="fas fa-user-tie"></i> Parent/Guardian Information (Existing)';
                            document.querySelector('#parentInfoModal p').textContent = 'Parent/guardian details found in system. Review and send SMS notification.';
                        }
                    } else {
                        // No existing parent data, show empty form
                        document.getElementById('parentName').value = '';
                        document.getElementById('parentPhone').value = '';
                        const modalTitle = document.querySelector('#parentInfoModal h3');
                        modalTitle.innerHTML = '<i class="fas fa-user-tie"></i> Parent/Guardian Information';
                        document.querySelector('#parentInfoModal p').textContent = 'Please enter parent/guardian details to send SMS notification';
                    }
                } catch (error) {
                    console.error('Error fetching parent info:', error);
                    // Continue with empty form if fetch fails
                    document.getElementById('parentName').value = '';
                    document.getElementById('parentPhone').value = '';
                }
                
                // Open parent info modal
                document.getElementById('parentInfoModal').classList.remove('hidden');
                document.getElementById('parentComplaintId').value = currentComplaintIdForSMS;
            });

            // Close modal when clicking outside
            viewModal.addEventListener('click', function(e) {
                if (e.target === viewModal) {
                    viewModal.classList.add('hidden');
                }
            });

            // Scheduling Modal functionality
            const schedulingModal = document.getElementById('schedulingModal');
            const scheduleDate = document.getElementById('scheduleDate');
            const timeSlots = document.getElementById('timeSlots');
            const selectedComplaintId = document.getElementById('selectedComplaintId');
            const cancelSchedule = document.getElementById('cancelSchedule');
            const confirmSchedule = document.getElementById('confirmSchedule');
            let currentComplaintId = null;

            // Set minimum date to today
            scheduleDate.min = new Date().toISOString().split('T')[0];

            // Generate time slots
            function generateTimeSlots() {
                const slots = [];
                const times = [
                    '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
                    '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'
                ];
                
                return times;
            }

            // Check time slot availability
            async function checkTimeSlotAvailability(date, time) {
                try {
                    console.log('Checking availability for:', date, time);
                    const response = await fetch('check_time_slot.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ date, time })
                    });

                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);

                    if (!response.ok) {
                        console.error('HTTP error:', response.status, response.statusText);
                        const text = await response.text();
                        console.error('Response text:', text);
                        return false;
                    }

                    const data = await response.json();
                    console.log('Availability response:', data);

                    if (data.error) {
                        console.error('Backend error:', data.error);
                        return false;
                    }

                    return data.available;
                } catch (error) {
                    console.error('Error checking time slot:', error);
                    console.error('Error details:', error.message);
                    return false;
                }
            }

            // Update time slots display
            async function updateTimeSlots(date) {
                timeSlots.innerHTML = '';
                const slots = generateTimeSlots();
                
                for (const time of slots) {
                    const isAvailable = await checkTimeSlotAvailability(date, time);
                    const button = document.createElement('button');
                    button.className = `px-3 py-2 rounded-md text-sm ${
                        isAvailable 
                            ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                            : 'bg-red-100 text-red-800 cursor-not-allowed'
                    }`;
                    button.textContent = time;
                    button.disabled = !isAvailable;
                    button.onclick = () => {
                        document.querySelectorAll('#timeSlots button').forEach(btn => {
                            btn.classList.remove('bg-indigo-100', 'text-indigo-800');
                        });
                        if (isAvailable) {
                            button.classList.add('bg-indigo-100', 'text-indigo-800');
                        }
                    };
                    timeSlots.appendChild(button);
                }
            }

            // Event listeners
            scheduleDate.addEventListener('change', () => {
                updateTimeSlots(scheduleDate.value);
            });

            cancelSchedule.addEventListener('click', () => {
                schedulingModal.classList.add('hidden');
            });

            confirmSchedule.addEventListener('click', async () => {
                const selectedTime = document.querySelector('#timeSlots button.bg-indigo-100');
                if (!selectedTime) {
                    alert('Please select a time slot');
                    return;
                }

                // Validate date and time are not in the past
                const selectedDate = scheduleDate.value;
                const selectedTimeStr = selectedTime.textContent;
                const scheduledDateTime = new Date(selectedDate + ' ' + selectedTimeStr);
                const now = new Date();

                if (scheduledDateTime <= now) {
                    alert('Cannot schedule a session in the past. Please select a future date and time.');
                    return;
                }

                // Disable button to prevent double submission
                confirmSchedule.disabled = true;
                confirmSchedule.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Scheduling...';

                try {
                    const formData = new FormData();
                    formData.append('complaint_id', selectedComplaintId.value);
                    formData.append('scheduled_date', selectedDate);
                    formData.append('scheduled_time', selectedTimeStr);
                    
                    const response = await fetch('set-schedule.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Check if response is ok before trying to parse JSON
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new TypeError("Response was not JSON");
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        try {
                            const scheduledMessage = `
Dear ${data.data.student_name},

Your counseling session has been scheduled. Please find the details below:

Session Details:
- Date: ${data.data.scheduled_date}
- Time: ${data.data.scheduled_time}
- Location: Guidance Office

Important Reminders:
1. Please arrive 5-10 minutes before your scheduled time
2. Bring any relevant documents or materials related to your concern
3. If you need to reschedule, please inform the guidance office at least 24 hours before your appointment

If you have any questions or need to make changes to your appointment, please contact the guidance office immediately.

Best regards,
EMEMHS Guidance Office`;

                            await sendEmailNotification(
                                data.data.student_email,
                                'Counseling Session Scheduled - EMEMHS Guidance System',
                                scheduledMessage
                            );
                            alert('Schedule set and notification sent successfully!');
                            window.location.reload();
                        } catch (emailError) {
                            console.error('Error sending email:', emailError);
                            alert('Schedule set but failed to send notification. Please try again.');
                            window.location.reload();
                        }
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                        // Re-enable button on error
                        confirmSchedule.disabled = false;
                        confirmSchedule.innerHTML = 'Confirm Schedule';
                    }
                } catch (error) {
                    console.error('Error setting schedule:', error);
                    alert('Error setting schedule. Please try again.');
                    // Re-enable button on error
                    confirmSchedule.disabled = false;
                    confirmSchedule.innerHTML = 'Confirm Schedule';
                }
            });

            // Update Set Schedule button click handler
            document.querySelectorAll('.set-schedule-btn').forEach(button => {
                button.addEventListener('click', () => {
                    currentComplaintId = button.dataset.complaintId;
                    selectedComplaintId.value = currentComplaintId;
                    schedulingModal.classList.remove('hidden');
                });
            });

            // Search functionality
            const searchInputs = document.querySelectorAll('.search-input');
            
            searchInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    const tabId = this.closest('.tab-content').id;
                    const table = document.querySelector(`#${tabId} table tbody`);
                    const rows = table.querySelectorAll('tr');
                    let hasVisibleRows = false;

                    rows.forEach(row => {
                        // Skip the "no results" row if it exists
                        if (row.querySelector('td[colspan]')) {
                            row.style.display = 'none';
                            return;
                        }

                        const cells = row.querySelectorAll('td');
                        let rowVisible = false;

                        cells.forEach(cell => {
                            const cellText = cell.textContent.toLowerCase().trim();
                            if (cellText.includes(searchTerm)) {
                                rowVisible = true;
                            }
                        });

                        row.style.display = rowVisible ? '' : 'none';
                        if (rowVisible) {
                            hasVisibleRows = true;
                        }
                    });

                    // Handle "no results" message
                    const noResultsRow = table.querySelector('tr td[colspan]');
                    if (!hasVisibleRows) {
                        if (!noResultsRow) {
                            const newRow = document.createElement('tr');
                            const cell = document.createElement('td');
                            cell.colSpan = 8;
                            cell.className = 'px-6 py-4 text-center text-sm text-gray-500';
                            cell.textContent = 'No matching complaints found';
                            newRow.appendChild(cell);
                            table.appendChild(newRow);
                        } else {
                            noResultsRow.style.display = '';
                        }
                    } else if (noResultsRow) {
                        noResultsRow.style.display = 'none';
                    }
                });
            });

            // Add search input styling
            const style = document.createElement('style');
            style.textContent += `
                .search-input {
                    transition: all 0.2s ease-in-out;
                }
                .search-input:focus {
                    box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
                }
                .search-input::placeholder {
                    color: #94a3b8;
                }
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                tr {
                    animation: fadeIn 0.2s ease-in-out;
                }
            `;
            document.head.appendChild(style);

            // Function to send email notification using EmailJS
            function sendEmailNotification(email, subject, message) {
                return new Promise((resolve, reject) => {
                    emailjs.init('GRi35_90k4gj9Es_f');
                    emailjs.send('service_8jh4949', 'template_gr1vonw', {
                        sendername: 'EMEMHS Guidance System',
                        to: email,
                        subject: subject,
                        replyto: 'noreply@ememhs.edu.ph',
                        message: message
                    }).then(function(response) {
                        console.log('Email sent successfully:', response);
                        resolve(response);
                    }, function(error) {
                        console.error('Failed to send email:', error);
                        reject(error);
                    });
                });
            }

            // Handle marking complaint as resolved
            document.querySelectorAll('form[action="mark_resolved.php"]').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    try {
                        const response = await fetch('mark_resolved.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            try {
                                const resolvedMessage = `
Dear ${data.student_name},

Your complaint has been successfully resolved by our guidance counselor. 

Complaint Details:
- Status: Resolved
- Resolution Date: ${new Date().toLocaleDateString()}
- Time: ${new Date().toLocaleTimeString()}

Thank you for bringing this matter to our attention. We appreciate your patience and cooperation throughout this process.

If you have any further concerns or questions, please don't hesitate to contact the guidance office.

Best regards,
EMEMHS Guidance Office`;

                                await sendEmailNotification(
                                    data.student_email,
                                    'Complaint Resolved - EMEMHS Guidance System',
                                    resolvedMessage
                                );
                                alert('Complaint marked as resolved and notification sent successfully!');
                                window.location.reload();
                            } catch (emailError) {
                                console.error('Error sending email:', emailError);
                                alert('Complaint marked as resolved but failed to send notification. Please try again.');
                            }
                        } else {
                            alert('Error marking complaint as resolved: ' + (data.error || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error processing request. Please try again.');
                    }
                });
            });
        });
    </script>

    <!-- Parent Information Modal -->
    <div id="parentInfoModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm overflow-y-auto hidden z-50">
        <div class="relative top-20 mx-auto p-6 w-[28rem] bg-white rounded-2xl shadow-xl transform transition-all">
            <div class="pb-4 border-b border-gray-100">
                <h3 class="text-xl font-semibold text-[#800000] flex items-center gap-2">
                    <i class="fas fa-user-tie"></i>
                    Parent/Guardian Information
                </h3>
                <p class="text-sm text-gray-600 mt-1">Please enter parent/guardian details to send SMS notification</p>
            </div>
            <form id="parentInfoForm" class="py-6 space-y-4">
                <div>
                    <label for="parentName" class="block text-sm font-medium text-gray-700 mb-2">Parent/Guardian Name</label>
                    <input type="text" id="parentName" name="parent_name" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200 ease-in-out"
                        placeholder="Enter parent/guardian name">
                </div>
                <div>
                    <label for="parentPhone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" id="parentPhone" name="contact_number" required pattern="09[0-9]{9}" maxlength="11"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200 ease-in-out"
                        placeholder="09XXXXXXXXX">
                    <p class="text-xs text-gray-500 mt-1">Format: 09XXXXXXXXX (11 digits)</p>
                    <span id="phoneError" class="text-xs text-red-600 mt-1 block"></span>
                </div>
                <input type="hidden" id="parentComplaintId" name="complaint_id">
            </form>
            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <button id="cancelParentInfo" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button id="saveAndSendSMS" class="px-4 py-2.5 rounded-xl bg-[#800000] text-white hover:bg-[#900000] transition duration-200 flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    Save & Send SMS
                </button>
            </div>
        </div>
    </div>

    <script>
        // Parent Info Modal handlers
        const parentInfoModal = document.getElementById('parentInfoModal');
        const parentInfoForm = document.getElementById('parentInfoForm');
        const cancelParentInfo = document.getElementById('cancelParentInfo');
        const saveAndSendSMS = document.getElementById('saveAndSendSMS');
        const parentPhone = document.getElementById('parentPhone');
        const phoneError = document.getElementById('phoneError');

        // Validate phone number
        parentPhone.addEventListener('input', function() {
            const value = this.value;
            const isValid = /^09[0-9]{9}$/.test(value);
            
            if (value && !isValid) {
                phoneError.textContent = 'Invalid format. Must be 09XXXXXXXXX (11 digits)';
                saveAndSendSMS.disabled = true;
                saveAndSendSMS.style.opacity = '0.5';
            } else {
                phoneError.textContent = '';
                saveAndSendSMS.disabled = false;
                saveAndSendSMS.style.opacity = '1';
            }
        });

        // Cancel button
        cancelParentInfo.addEventListener('click', function() {
            parentInfoModal.classList.add('hidden');
            parentInfoForm.reset();
        });

        // Save and Send SMS
        saveAndSendSMS.addEventListener('click', async function() {
            const parentName = document.getElementById('parentName').value.trim();
            const contactNumber = document.getElementById('parentPhone').value.trim();
            const complaintId = document.getElementById('parentComplaintId').value;

            // Validate inputs
            if (!parentName) {
                alert('Please enter parent/guardian name');
                return;
            }

            if (!contactNumber || !/^09[0-9]{9}$/.test(contactNumber)) {
                alert('Please enter a valid phone number (09XXXXXXXXX)');
                return;
            }

            // Show loading state
            const originalText = saveAndSendSMS.innerHTML;
            saveAndSendSMS.disabled = true;
            saveAndSendSMS.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

            try {
                const formData = new FormData();
                formData.append('complaint_id', complaintId);
                formData.append('parent_name', parentName);
                formData.append('contact_number', contactNumber);

                const response = await fetch('../logic/save_parent_and_send_sms.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(`✓ Parent info saved and SMS sent successfully to ${data.details.parent_name}`);
                    parentInfoModal.classList.add('hidden');
                    parentInfoForm.reset();
                } else {
                    alert(`✗ Error: ${data.message}`);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                saveAndSendSMS.disabled = false;
                saveAndSendSMS.innerHTML = originalText;
            }
        });

        // Close modal when clicking outside
        parentInfoModal.addEventListener('click', function(e) {
            if (e.target === parentInfoModal) {
                parentInfoModal.classList.add('hidden');
                parentInfoForm.reset();
            }
        });
    </script>
</body>
</html> 