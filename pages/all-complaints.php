<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: index.php");
    exit();
}

// Get all complaints (all statuses)
$stmt = $pdo->prepare("
    SELECT cc.*,
           COALESCE(cc.severity, 'medium') as severity,
           s.first_name, s.last_name, s.grade_level, s.section
    FROM " . TBL_COMPLAINTS_CONCERNS . " cc
    JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
    ORDER BY cc.date_created DESC, cc.time_created DESC
");
$stmt->execute();
$all_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$pending_count = 0;
$scheduled_count = 0;
$resolved_count = 0;
foreach ($all_complaints as $complaint) {
    if ($complaint['status'] === 'pending') $pending_count++;
    elseif ($complaint['status'] === 'scheduled') $scheduled_count++;
    elseif ($complaint['status'] === 'resolved') $resolved_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Complaints - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background-color: rgba(128, 0, 0, 0.05);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .status-scheduled {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .status-resolved {
            background-color: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .severity-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
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

        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            background: white;
        }

        .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .filter-btn:hover:not(.active) {
            background-color: #f8fafc;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
                <div class="flex items-center">
                    <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3 mr-4">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">All Complaints History</h1>
                        <p class="text-gray-600 text-sm">Complete record of all complaints and concerns</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Total</p>
                            <h3 class="text-2xl font-bold text-[#800000]"><?= count($all_complaints) ?></h3>
                        </div>
                        <i class="fas fa-clipboard-list text-2xl text-[#800000]/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Pending</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?= $pending_count ?></h3>
                        </div>
                        <i class="fas fa-clock text-2xl text-orange-600/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Scheduled</p>
                            <h3 class="text-2xl font-bold text-blue-600"><?= $scheduled_count ?></h3>
                        </div>
                        <i class="fas fa-calendar-check text-2xl text-blue-600/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Resolved</p>
                            <h3 class="text-2xl font-bold text-green-600"><?= $resolved_count ?></h3>
                        </div>
                        <i class="fas fa-check-circle text-2xl text-green-600/20"></i>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="minimal-card p-4 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <button class="filter-btn active" data-filter="all">
                            <i class="fas fa-list mr-2"></i>All
                        </button>
                        <button class="filter-btn" data-filter="pending">
                            <i class="fas fa-clock mr-2"></i>Pending
                        </button>
                        <button class="filter-btn" data-filter="scheduled">
                            <i class="fas fa-calendar-check mr-2"></i>Scheduled
                        </button>
                        <button class="filter-btn" data-filter="resolved">
                            <i class="fas fa-check-circle mr-2"></i>Resolved
                        </button>
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
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="complaintsTableBody">
                            <?php if (!empty($all_complaints)): ?>
                                <?php foreach ($all_complaints as $complaint): ?>
                                    <tr class="table-row" data-status="<?= $complaint['status'] ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($complaint['first_name']." ".$complaint['last_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($complaint['grade_level']." ".$complaint['section']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($complaint['type']) ?></td>
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-<?= strtolower($complaint['status']) ?>">
                                                <?php if ($complaint['status'] === 'pending'): ?>
                                                    <i class="fas fa-clock"></i>
                                                <?php elseif ($complaint['status'] === 'scheduled'): ?>
                                                    <i class="fas fa-calendar-check"></i>
                                                <?php elseif ($complaint['status'] === 'resolved'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php endif; ?>
                                                <?= ucfirst($complaint['status']) ?>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php
                                            if ($complaint['status'] == 'pending') {
                                                echo '<span class="text-gray-500">Not scheduled</span>';
                                            } else {
                                                echo htmlspecialchars($complaint['scheduled_date'] . ' ' . $complaint['scheduled_time']);
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button class="bg-[#800000] hover:bg-[#600000] text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200 flex items-center gap-2 view-complaint-btn"
                                                    data-complaint='<?php
                                                        $complaintData = $complaint;
                                                        unset($complaintData['evidence']);
                                                        unset($complaintData['mime_type']);
                                                        echo json_encode($complaintData);
                                                    ?>'
                                                    data-evidence='<?php echo !empty($complaint['evidence']) ? base64_encode($complaint['evidence']) : ''; ?>'
                                                    data-mime-type='<?php echo !empty($complaint['mime_type']) ? htmlspecialchars($complaint['mime_type']) : ''; ?>'>
                                                <i class="fas fa-eye"></i>
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Complaints Found</h3>
                                            <p class="text-sm text-gray-500">There are no complaints in the system yet.</p>
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
    <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-[#800000] flex items-center gap-2">
                <i class="fas fa-file-alt"></i>
                Complaint Details
            </h3>
            <button id="closeViewModal" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="py-6">
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
                                <p class="text-sm text-gray-500">Severity</p>
                                <p class="text-sm font-medium text-gray-900" id="viewSeverity"></p>
                            </div>
                            <div class="bg-white p-3 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500">Description</p>
                                <p class="text-sm font-medium text-gray-900 whitespace-pre-wrap" id="viewDescription"></p>
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

<script>
    // Modal functionality
    const viewModal = document.getElementById('viewDetailsModal');
    const closeViewModal = document.getElementById('closeViewModal');
    const closeViewModalBtn = document.getElementById('closeViewModalBtn');

    // Function to show complaint details
    function showComplaintDetails(complaint, evidence, mimeType) {
        // Set student information
        document.getElementById('viewFirstName').textContent = complaint.first_name;
        document.getElementById('viewLastName').textContent = complaint.last_name;
        document.getElementById('viewGradeLevel').textContent = complaint.grade_level;
        document.getElementById('viewSection').textContent = complaint.section;

        // Set complaint information
        document.getElementById('viewType').textContent = complaint.type;
        
        // Set severity with badge
        const severityLabels = {
            'low': 'Low',
            'medium': 'Medium',
            'high': 'High',
            'urgent': 'Urgent'
        };
        const severityClasses = {
            'low': 'severity-low',
            'medium': 'severity-medium',
            'high': 'severity-high',
            'urgent': 'severity-urgent'
        };
        const severity = complaint.severity || 'medium';
        document.getElementById('viewSeverity').innerHTML = `
            <span class="severity-badge ${severityClasses[severity]}">
                <i class="fas fa-exclamation-triangle"></i>
                ${severityLabels[severity]}
            </span>
        `;
        
        document.getElementById('viewDescription').textContent = complaint.description || 'No description provided';
        
        // Set status with badge
        const statusClasses = {
            'pending': 'status-pending',
            'scheduled': 'status-scheduled',
            'resolved': 'status-resolved'
        };
        const statusIcons = {
            'pending': 'fa-clock',
            'scheduled': 'fa-calendar-check',
            'resolved': 'fa-check-circle'
        };
        document.getElementById('viewStatus').innerHTML = `
            <span class="status-badge ${statusClasses[complaint.status]}">
                <i class="fas ${statusIcons[complaint.status]}"></i>
                ${complaint.status.charAt(0).toUpperCase() + complaint.status.slice(1)}
            </span>
        `;
        
        document.getElementById('viewPreferredDate').textContent = complaint.preferred_counseling_date || 'N/A';
        document.getElementById('viewScheduledDate').textContent = complaint.scheduled_date || 'N/A';
        document.getElementById('viewScheduledTime').textContent = complaint.scheduled_time || 'N/A';

        // Set evidence
        const evidenceContainer = document.getElementById('viewEvidence');
        if (evidence && mimeType) {
            evidenceContainer.innerHTML = `
                <div class="relative group">
                    <img src="data:${mimeType};base64,${evidence}" 
                         alt="Evidence" 
                         class="w-full h-auto object-cover rounded-lg shadow-sm transition-transform duration-300 group-hover:scale-105 cursor-pointer" 
                         onclick="viewImageFullscreen(this.src)" />
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

        // Show modal
        viewModal.classList.remove('hidden');
    }

    // Add click handlers for view buttons
    document.querySelectorAll('.view-complaint-btn').forEach(button => {
        button.addEventListener('click', function() {
            const complaintData = JSON.parse(this.dataset.complaint);
            const evidence = this.dataset.evidence;
            const mimeType = this.dataset.mimeType;
            showComplaintDetails(complaintData, evidence, mimeType);
        });
    });

    // Close modal handlers
    closeViewModal.addEventListener('click', function() {
        viewModal.classList.add('hidden');
    });
    
    closeViewModalBtn.addEventListener('click', function() {
        viewModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    viewModal.addEventListener('click', function(e) {
        if (e.target === viewModal) {
            viewModal.classList.add('hidden');
        }
    });

    // View image in fullscreen
    function viewImageFullscreen(src) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-[60] p-4';
        modal.innerHTML = `
            <div class="relative max-w-7xl max-h-screen">
                <button onclick="this.parentElement.parentElement.remove()" class="absolute -top-12 right-0 text-white bg-red-600 hover:bg-red-700 rounded-full w-10 h-10 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
                <img src="${src}" class="max-w-full max-h-screen rounded-lg shadow-2xl" />
            </div>
        `;
        modal.onclick = function(e) {
            if (e.target === modal) modal.remove();
        };
        document.body.appendChild(modal);
    }

    // Filter functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    const tableRows = document.querySelectorAll('.table-row');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;

            // Filter rows
            tableRows.forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
