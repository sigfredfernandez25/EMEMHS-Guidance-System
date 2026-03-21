<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: login.php");
    exit();
}

// Get unresolved complaints with student information
$stmt = $pdo->prepare("
    SELECT cc.*,
           COALESCE(cc.severity, 'medium') as severity,
           s.first_name, s.last_name, s.grade_level, s.section,
           s.id as student_id
    FROM " . TBL_COMPLAINTS_CONCERNS . " cc
    JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
    WHERE cc.status = 'unresolved'
    ORDER BY 
        CASE 
            WHEN cc.severity = 'urgent' THEN 1
            WHEN cc.severity = 'high' THEN 2
            WHEN cc.severity = 'medium' THEN 3
            WHEN cc.severity = 'low' THEN 4
            ELSE 5
        END,
        cc.updated_at DESC
");
$stmt->execute();
$unresolved_issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by severity
$urgent_count = 0;
$high_count = 0;
$medium_count = 0;
$low_count = 0;

foreach ($unresolved_issues as $issue) {
    switch ($issue['severity']) {
        case 'urgent':
            $urgent_count++;
            break;
        case 'high':
            $high_count++;
            break;
        case 'medium':
            $medium_count++;
            break;
        case 'low':
            $low_count++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unresolved Issues - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background-color: rgba(239, 68, 68, 0.05);
        }

        .severity-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items-center;
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

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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

        .priority-indicator {
            width: 4px;
            height: 100%;
            position: absolute;
            left: 0;
            top: 0;
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
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'?>
<div class="main-content">
    <main class="min-h-screen">
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                        Unresolved Issues
                    </h1>
                    <p class="text-gray-600">Issues requiring follow-up action and intervention</p>
                </div>
                <div class="bg-red-600/10 text-red-600 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Total</p>
                            <h3 class="text-2xl font-bold text-red-600"><?= count($unresolved_issues) ?></h3>
                        </div>
                        <i class="fas fa-exclamation-circle text-2xl text-red-600/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Urgent</p>
                            <h3 class="text-2xl font-bold text-red-700"><?= $urgent_count ?></h3>
                        </div>
                        <i class="fas fa-fire text-2xl text-red-700/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">High</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?= $high_count ?></h3>
                        </div>
                        <i class="fas fa-exclamation text-2xl text-orange-600/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Medium</p>
                            <h3 class="text-2xl font-bold text-yellow-600"><?= $medium_count ?></h3>
                        </div>
                        <i class="fas fa-minus-circle text-2xl text-yellow-600/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Low</p>
                            <h3 class="text-2xl font-bold text-green-600"><?= $low_count ?></h3>
                        </div>
                        <i class="fas fa-check-circle text-2xl text-green-600/20"></i>
                    </div>
                </div>
            </div>

            <!-- Search and Info -->
            <div class="minimal-card p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-600/10 text-red-600 rounded-full p-3">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                        <div>
                            <h2 class="section-title text-xl font-semibold text-red-600">Follow-Up Required</h2>
                            <p class="text-gray-600 text-sm">These issues need additional intervention</p>
                        </div>
                    </div>
                    <div>
                        <input type="text" placeholder="Search issues..." class="search-input" id="searchInput" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-header">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Session</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="issuesTableBody">
                            <?php if (!empty($unresolved_issues)): ?>
                                <?php foreach ($unresolved_issues as $issue): ?>
                                    <tr class="table-row relative" data-severity="<?= $issue['severity'] ?>">
                                        <div class="priority-indicator priority-<?= $issue['severity'] ?>"></div>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($issue['first_name']." ".$issue['last_name']) ?></div>
                                            <div class="text-gray-500 text-xs"><?= htmlspecialchars($issue['grade_level']." - ".$issue['section']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $issue['type']))) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $severity = $issue['severity'] ?? 'medium';
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <?php if ($issue['scheduled_date']): ?>
                                                <div class="text-gray-900"><?= htmlspecialchars($issue['scheduled_date']) ?></div>
                                                <div class="text-gray-500 text-xs"><?= htmlspecialchars($issue['scheduled_time']) ?></div>
                                            <?php else: ?>
                                                <span class="text-gray-400">No session yet</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="max-w-xs truncate text-gray-600">
                                                <?= htmlspecialchars(substr($issue['admin_remark'] ?? 'No remarks', 0, 60)) ?>
                                                <?= strlen($issue['admin_remark'] ?? '') > 60 ? '...' : '' ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center justify-center gap-2">
                                                <button class="reschedule-session-btn bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg transition-colors duration-200 group relative"
                                                        data-issue-id="<?= $issue['id'] ?>"
                                                        data-student-id="<?= $issue['student_id'] ?>"
                                                        title="Schedule Follow-up">
                                                    <i class="fas fa-calendar-plus"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                        Schedule Follow-up
                                                    </span>
                                                </button>
                                                <button class="mark-resolved-btn bg-green-600 hover:bg-green-700 text-white p-2 rounded-lg transition-colors duration-200 group relative"
                                                        data-issue-id="<?= $issue['id'] ?>"
                                                        title="Mark as Resolved">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                        Mark as Resolved
                                                    </span>
                                                </button>
                                                <button class="view-details-btn bg-gray-600 hover:bg-gray-700 text-white p-2 rounded-lg transition-colors duration-200 group relative"
                                                        data-issue='<?php 
                                                            $issueData = $issue;
                                                            unset($issueData['evidence']);
                                                            unset($issueData['mime_type']);
                                                            unset($issueData['audio_recording']);
                                                            unset($issueData['audio_mime_type']);
                                                            echo json_encode($issueData); 
                                                        ?>'
                                                        data-evidence='<?php echo !empty($issue['evidence']) ? base64_encode($issue['evidence']) : ''; ?>'
                                                        data-mime-type='<?php echo !empty($issue['mime_type']) ? htmlspecialchars($issue['mime_type']) : ''; ?>'
                                                        data-audio='<?php echo !empty($issue['audio_recording']) ? base64_encode($issue['audio_recording']) : ''; ?>'
                                                        data-audio-mime='<?php echo !empty($issue['audio_mime_type']) ? htmlspecialchars($issue['audio_mime_type']) : ''; ?>'
                                                        data-audio-duration='<?php echo !empty($issue['audio_duration']) ? $issue['audio_duration'] : '0'; ?>'>
                                                    <i class="fas fa-eye"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                        View Details
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Unresolved Issues</h3>
                                            <p class="text-sm text-gray-500">All complaints have been successfully resolved or are in progress.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- View Details Modal -->
<div id="viewDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl modal-content">
        <div class="modal-header flex justify-between items-center">
            <h3 class="text-xl font-semibold text-red-600 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                Unresolved Issue Details
            </h3>
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

                    <!-- Issue Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Issue Information
                        </h4>
                        <div class="space-y-4">
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500">Type</p>
                                <p class="text-sm font-medium text-gray-900" id="viewType"></p>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500">Severity</p>
                                <p class="text-sm font-medium text-gray-900" id="viewSeverity"></p>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500">Description</p>
                                <p class="text-sm font-medium text-gray-900 whitespace-pre-wrap" id="viewDescription"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Session History -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
                            <i class="fas fa-history mr-2"></i>
                            Session History
                        </h4>
                        <div class="space-y-4">
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500">Last Session Date</p>
                                <p class="text-sm font-medium text-gray-900" id="viewScheduledDate"></p>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500">Last Session Time</p>
                                <p class="text-sm font-medium text-gray-900" id="viewScheduledTime"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Remarks -->
                    <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-600">
                        <h4 class="font-semibold text-red-700 mb-3 flex items-center">
                            <i class="fas fa-comment-medical mr-2"></i>
                            Why Unresolved
                        </h4>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap" id="viewRemark"></p>
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
        <div class="pt-4 border-t border-gray-200 flex justify-end">
            <button id="closeViewModalBtn" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition duration-200">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Schedule Follow-up Modal -->
<div id="scheduleFollowupModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm overflow-y-auto hidden z-50">
    <div class="relative top-20 mx-auto p-6 w-[28rem] bg-white rounded-2xl shadow-xl transform transition-all">
        <div class="pb-4 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-blue-700 flex items-center gap-2">
                <i class="fas fa-calendar-plus"></i>
                Schedule Follow-up Session
            </h3>
            <p class="text-sm text-gray-600 mt-1">Set a new counseling session for this unresolved issue</p>
        </div>
        <div class="py-6 space-y-6">
            <div>
                <label for="followupDate" class="block text-sm font-medium text-gray-700 mb-2">Follow-up Date</label>
                <input type="date" id="followupDate" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition duration-200 ease-in-out">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
                <div id="followupTimeSlots" class="grid grid-cols-3 gap-3">
                    <!-- Time slots will be populated here -->
                </div>
            </div>
            <input type="hidden" id="followupIssueId">
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <button id="cancelFollowup" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                <i class="fas fa-times"></i>
                Cancel
            </button>
            <button id="confirmFollowup" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition duration-200 flex items-center gap-2">
                <i class="fas fa-check"></i>
                Schedule Follow-up
            </button>
        </div>
    </div>
</div>

<!-- Mark as Resolved Modal -->
<div id="markResolvedModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm overflow-y-auto hidden z-50">
    <div class="relative top-20 mx-auto p-6 w-[32rem] bg-white rounded-2xl shadow-xl transform transition-all">
        <div class="pb-4 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                Mark Issue as Resolved
            </h3>
            <p class="text-sm text-gray-600 mt-1">Document how this issue was finally resolved</p>
        </div>
        <div class="py-6 space-y-4">
            <div>
                <label for="resolutionRemark" class="block text-sm font-medium text-gray-700 mb-2">
                    Resolution Details <span class="text-red-500">*</span>
                </label>
                <textarea id="resolutionRemark" rows="5" required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-green-600 focus:ring-2 focus:ring-green-600/20 transition duration-200 ease-in-out resize-none"
                    placeholder="Explain what actions were taken and how the issue was resolved..."></textarea>
                <p class="text-xs text-gray-500 mt-1">This will replace the previous unresolved remark</p>
            </div>
            <input type="hidden" id="resolvedIssueId">
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <button id="cancelResolved" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                <i class="fas fa-times"></i>
                Cancel
            </button>
            <button id="confirmResolved" class="px-4 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700 transition duration-200 flex items-center gap-2">
                <i class="fas fa-check"></i>
                Mark as Resolved
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const viewModal = document.getElementById('viewDetailsModal');
    const scheduleModal = document.getElementById('scheduleFollowupModal');
    const resolvedModal = document.getElementById('markResolvedModal');

    // View Details Modal
    document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const issueData = JSON.parse(this.dataset.issue);
            const evidence = this.dataset.evidence;
            const mimeType = this.dataset.mimeType;
            const audioData = this.dataset.audio;
            const audioMimeType = this.dataset.audioMime;
            const audioDuration = this.dataset.audioDuration;
            showIssueDetails(issueData, evidence, mimeType, audioData, audioMimeType, audioDuration);
        });
    });

    function showIssueDetails(issue, evidence, mimeType, audioData, audioMimeType, audioDuration) {
        document.getElementById('viewFirstName').textContent = issue.first_name;
        document.getElementById('viewLastName').textContent = issue.last_name;
        document.getElementById('viewGradeLevel').textContent = issue.grade_level;
        document.getElementById('viewSection').textContent = issue.section;
        document.getElementById('viewType').textContent = issue.type;
        
        const severityLabels = { 'low': 'Low', 'medium': 'Medium', 'high': 'High', 'urgent': 'Urgent' };
        const severityClasses = { 'low': 'severity-low', 'medium': 'severity-medium', 'high': 'severity-high', 'urgent': 'severity-urgent' };
        const severity = issue.severity || 'medium';
        document.getElementById('viewSeverity').innerHTML = `
            <span class="severity-badge ${severityClasses[severity]}">
                <i class="fas fa-exclamation-triangle"></i>
                ${severityLabels[severity]}
            </span>
        `;
        
        document.getElementById('viewDescription').textContent = issue.description || 'No description provided';
        document.getElementById('viewScheduledDate').textContent = issue.scheduled_date || 'No session yet';
        document.getElementById('viewScheduledTime').textContent = issue.scheduled_time || 'N/A';
        document.getElementById('viewRemark').textContent = issue.admin_remark || 'No remarks provided';

        const evidenceContainer = document.getElementById('viewEvidence');
        if (evidence && mimeType) {
            evidenceContainer.innerHTML = `
                <img src="data:${mimeType};base64,${evidence}" 
                     alt="Evidence" 
                     class="w-full h-auto object-cover rounded-lg shadow-sm" />
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

        viewModal.classList.remove('hidden');
    }

    document.getElementById('closeViewModal').addEventListener('click', () => {
        viewModal.classList.add('hidden');
    });

    document.getElementById('closeViewModalBtn').addEventListener('click', () => {
        viewModal.classList.add('hidden');
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Schedule Follow-up
    document.querySelectorAll('.reschedule-session-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('followupIssueId').value = this.dataset.issueId;
            document.getElementById('followupDate').value = '';
            document.getElementById('followupTimeSlots').innerHTML = '<p class="text-gray-400 text-sm col-span-3 text-center py-4"><i class="fas fa-calendar-alt mr-2"></i>Please select a date to view available time slots</p>';
            scheduleModal.classList.remove('hidden');
        });
    });

    document.getElementById('cancelFollowup').addEventListener('click', () => {
        scheduleModal.classList.add('hidden');
    });

    document.getElementById('confirmFollowup').addEventListener('click', async function() {
        const issueId = document.getElementById('followupIssueId').value;
        const date = document.getElementById('followupDate').value;
        const selectedTime = document.querySelector('#followupTimeSlots button.bg-indigo-100');

        // Validation: Check if date is selected
        if (!date) {
            alert('Please select a date for the follow-up session');
            document.getElementById('followupDate').focus();
            return;
        }

        // Validation: Check if time is selected
        if (!selectedTime) {
            alert('Please select a time slot for the follow-up session');
            return;
        }

        // Validation: Check if date is not in the past
        const selectedDate = new Date(date);
        const currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0);

        if (selectedDate < currentDate) {
            alert('Cannot schedule sessions in the past. Please select a future date.');
            return;
        }

        // Validation: Check if it's a weekend
        const dayOfWeek = selectedDate.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            alert('Cannot schedule sessions on weekends. Please select a weekday.');
            return;
        }

        // Validation: If today, check if time is not in the past
        const isToday = selectedDate.toDateString() === new Date().toDateString();
        if (isToday) {
            const timeParts = selectedTime.textContent.match(/(\d+):(\d+)\s*(AM|PM)/);
            let hour = parseInt(timeParts[1]);
            const period = timeParts[3];
            
            if (period === 'PM' && hour !== 12) hour += 12;
            if (period === 'AM' && hour === 12) hour = 0;

            const currentHour = new Date().getHours();
            if (hour <= currentHour) {
                alert('Cannot schedule sessions in the past. Please select a future time slot.');
                return;
            }
        }

        // Show loading state
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Scheduling...';

        try {
            const formData = new FormData();
            formData.append('complaint_id', issueId);
            formData.append('scheduled_date', date);
            formData.append('scheduled_time', selectedTime.textContent);

            const response = await fetch('set-schedule.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Follow-up session scheduled successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error scheduling follow-up. Please try again.');
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });

    // Mark as Resolved
    document.querySelectorAll('.mark-resolved-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('resolvedIssueId').value = this.dataset.issueId;
            resolvedModal.classList.remove('hidden');
        });
    });

    document.getElementById('cancelResolved').addEventListener('click', () => {
        resolvedModal.classList.add('hidden');
    });

    document.getElementById('confirmResolved').addEventListener('click', async function() {
        const issueId = document.getElementById('resolvedIssueId').value;
        const remark = document.getElementById('resolutionRemark').value.trim();

        if (!remark) {
            alert('Please provide resolution details');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('complaint_id', issueId);
            formData.append('admin_remark', remark);
            formData.append('status', 'resolved');

            const response = await fetch('mark_resolved.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Issue marked as resolved successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error marking as resolved. Please try again.');
        }
    });

    // Time slots generation for follow-up
    const followupDateInput = document.getElementById('followupDate');
    
    // Set minimum date to today
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    followupDateInput.min = todayString;
    
    // Set maximum date to 3 months from now
    const maxDate = new Date();
    maxDate.setMonth(maxDate.getMonth() + 3);
    const maxDateString = maxDate.toISOString().split('T')[0];
    followupDateInput.max = maxDateString;

    followupDateInput.addEventListener('change', async function() {
        const date = this.value;
        const slotsContainer = document.getElementById('followupTimeSlots');
        slotsContainer.innerHTML = '';

        // Validate date is not in the past
        const selectedDate = new Date(date);
        const currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0);

        if (selectedDate < currentDate) {
            slotsContainer.innerHTML = '<p class="text-red-600 text-sm col-span-3">Cannot schedule sessions in the past. Please select a future date.</p>';
            return;
        }

        // Check if selected date is a weekend
        const dayOfWeek = selectedDate.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            slotsContainer.innerHTML = '<p class="text-orange-600 text-sm col-span-3"><i class="fas fa-info-circle mr-1"></i>Weekend selected. Please choose a weekday for counseling sessions.</p>';
            return;
        }

        // Show loading state
        slotsContainer.innerHTML = '<p class="text-gray-500 text-sm col-span-3"><i class="fas fa-spinner fa-spin mr-2"></i>Loading available time slots...</p>';

        const slots = ['8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM'];
        
        // Clear loading state
        slotsContainer.innerHTML = '';

        // If today, filter out past time slots
        const isToday = selectedDate.toDateString() === new Date().toDateString();
        const currentHour = new Date().getHours();

        for (const time of slots) {
            // Parse time to get hour
            const timeParts = time.match(/(\d+):(\d+)\s*(AM|PM)/);
            let hour = parseInt(timeParts[1]);
            const period = timeParts[3];
            
            if (period === 'PM' && hour !== 12) hour += 12;
            if (period === 'AM' && hour === 12) hour = 0;

            // Skip past time slots if today
            if (isToday && hour <= currentHour) {
                continue;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'px-3 py-2 rounded-md text-sm bg-green-100 text-green-800 hover:bg-green-200 transition-colors';
            button.textContent = time;
            button.onclick = () => {
                document.querySelectorAll('#followupTimeSlots button').forEach(btn => {
                    btn.classList.remove('bg-indigo-100', 'text-indigo-800', 'ring-2', 'ring-indigo-500');
                    btn.classList.add('bg-green-100', 'text-green-800');
                });
                button.classList.remove('bg-green-100', 'text-green-800');
                button.classList.add('bg-indigo-100', 'text-indigo-800', 'ring-2', 'ring-indigo-500');
            };
            slotsContainer.appendChild(button);
        }

        // Show message if no slots available
        if (slotsContainer.children.length === 0) {
            slotsContainer.innerHTML = '<p class="text-gray-500 text-sm col-span-3"><i class="fas fa-info-circle mr-1"></i>No available time slots for today. Please select a future date.</p>';
        }
    });
});
</script>
</body>
</html>
