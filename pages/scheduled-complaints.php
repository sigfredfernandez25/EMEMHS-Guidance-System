<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: index.php");
    exit();
}

// Get only scheduled complaints
$stmt = $pdo->prepare("
    SELECT cc.*,
           COALESCE(cc.severity, 'medium') as severity,
           s.first_name, s.last_name, s.grade_level, s.section
    FROM " . TBL_COMPLAINTS_CONCERNS . " cc
    JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
    WHERE cc.status = 'scheduled'
    ORDER BY cc.scheduled_date ASC, cc.scheduled_time ASC
");
$stmt->execute();
$scheduled_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Complaints - Guidance Portal</title>
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
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'?>
<div class="main-content">
    <main class="min-h-screen">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Scheduled Complaints</h1>
                    <p class="text-gray-600">Manage and track scheduled counseling sessions</p>
                </div>
                <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
            </div>
            
            <div class="minimal-card p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <div>
                            <h2 class="section-title text-xl font-semibold text-[#800000]">Upcoming Sessions</h2>
                            <p class="text-gray-600 text-sm">Total scheduled: <?= count($scheduled_complaints) ?> sessions</p>
                        </div>
                    </div>
                    <div>
                        <input type="text" placeholder="Search scheduled complaints..." class="search-input" id="searchInput" />
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-header">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade & Section</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint Type</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Time</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidence</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="complaintsTableBody">
                            <?php if (!empty($scheduled_complaints)): ?>
                                <?php foreach ($scheduled_complaints as $complaint): ?>
                                    <tr class="table-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($complaint['first_name']." ".$complaint['last_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($complaint['grade_level']." ".$complaint['section']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($complaint['type']); ?></td>
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
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-alt text-[#800000] mr-2"></i>
                                                <?php echo htmlspecialchars($complaint['scheduled_date']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <i class="fas fa-clock text-[#800000] mr-2"></i>
                                                <?php echo htmlspecialchars($complaint['scheduled_time']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($complaint['evidence']) && !empty($complaint['mime_type'])): ?>
                                                <img src="data:<?php echo $complaint['mime_type']; ?>;base64,<?php echo base64_encode($complaint['evidence']); ?>" 
                                                     alt="Evidence" 
                                                     class="w-16 h-16 object-cover rounded-lg shadow-sm hover:scale-110 transition-transform" />
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center gap-2">
                                                <button class="reschedule-btn bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200 flex items-center gap-2" 
                                                        data-complaint-id="<?= $complaint['id'] ?>"
                                                        data-current-date="<?= $complaint['scheduled_date'] ?>"
                                                        data-current-time="<?= $complaint['scheduled_time'] ?>">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    Reschedule
                                                </button>
                                                <form action="mark_resolved.php" method="POST" class="inline">
                                                    <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200 flex items-center gap-2">
                                                        <i class="fas fa-check-circle"></i>
                                                        Mark as Resolved
                                                    </button>
                                                </form>
                                                <button class="view-complaint bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200 flex items-center gap-2"
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
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Scheduled Complaints</h3>
                                            <p class="text-sm text-gray-500">There are currently no scheduled counseling sessions.</p>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm overflow-y-auto hidden z-50">
    <div class="relative top-20 mx-auto p-6 w-[28rem] bg-white rounded-2xl shadow-xl transform transition-all">
        <div class="pb-4 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-[#800000] flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                Reschedule Counseling Session
            </h3>
        </div>
        <div class="py-6 space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-700 mb-2">Current Schedule</h4>
                <div class="text-sm text-gray-600">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-calendar text-[#800000]"></i>
                        <span id="currentDate"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock text-[#800000]"></i>
                        <span id="currentTime"></span>
                    </div>
                </div>
            </div>
            <div>
                <label for="newScheduleDate" class="block text-sm font-medium text-gray-700 mb-2">New Date</label>
                <input type="date" id="newScheduleDate" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200 ease-in-out">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
                <div id="rescheduleTimeSlots" class="grid grid-cols-3 gap-3">
                    <!-- Time slots will be populated here -->
                </div>
            </div>
            <div>
                <label for="rescheduleReason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Reschedule</label>
                <textarea id="rescheduleReason" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20 transition duration-200 ease-in-out" placeholder="Please provide a reason for rescheduling..."></textarea>
            </div>
            <input type="hidden" id="rescheduleComplaintId">
        </div>
        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <button id="cancelReschedule" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200 flex items-center gap-2">
                <i class="fas fa-times"></i>
                Cancel
            </button>
            <button id="confirmReschedule" class="px-4 py-2.5 rounded-xl bg-[#800000] text-white hover:bg-[#900000] transition duration-200 flex items-center gap-2">
                <i class="fas fa-check"></i>
                Confirm Reschedule
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewModal = document.getElementById('viewDetailsModal');
    const closeViewModal = document.getElementById('closeViewModal');
    const searchInput = document.getElementById('searchInput');
    
    // Reschedule modal elements
    const rescheduleModal = document.getElementById('rescheduleModal');
    const newScheduleDate = document.getElementById('newScheduleDate');
    const rescheduleTimeSlots = document.getElementById('rescheduleTimeSlots');
    const rescheduleComplaintId = document.getElementById('rescheduleComplaintId');
    const rescheduleReason = document.getElementById('rescheduleReason');
    const cancelReschedule = document.getElementById('cancelReschedule');
    const confirmReschedule = document.getElementById('confirmReschedule');
    
    // Set minimum date to today
    newScheduleDate.min = new Date().toISOString().split('T')[0];

    // Function to show complaint details
    function showComplaintDetails(complaint, evidence, mimeType) {
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

        // Show modal
        viewModal.classList.remove('hidden');
    }

    // Add click handlers for view buttons
    document.querySelectorAll('.view-complaint').forEach(button => {
        button.addEventListener('click', function() {
            const complaintData = JSON.parse(this.dataset.complaint);
            const evidence = this.dataset.evidence;
            const mimeType = this.dataset.mimeType;
            showComplaintDetails(complaintData, evidence, mimeType);
        });
    });

    // Close modal handler
    closeViewModal.addEventListener('click', function() {
        viewModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    viewModal.addEventListener('click', function(e) {
        if (e.target === viewModal) {
            viewModal.classList.add('hidden');
        }
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const tableBody = document.getElementById('complaintsTableBody');
        const rows = tableBody.querySelectorAll('tr');
        let hasVisibleRows = false;

        rows.forEach(row => {
            // Skip the "no results" row if it exists
            if (row.querySelector('td[colspan]')) {
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
    });

    // Reschedule functionality
    function generateTimeSlots() {
        const slots = [];
        const startHour = 8; // 8 AM
        const endHour = 17; // 5 PM
        
        for (let hour = startHour; hour <= endHour; hour++) {
            const time = `${hour}:00`;
            slots.push(time);
        }
        
        return slots;
    }

    async function checkTimeSlotAvailability(date, time) {
        try {
            const response = await fetch('check_time_slot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ date, time })
            });

            if (!response.ok) {
                console.error('HTTP error:', response.status, response.statusText);
                return false;
            }

            const data = await response.json();
            if (data.error) {
                console.error('Backend error:', data.error);
                return false;
            }

            return data.available;
        } catch (error) {
            console.error('Error checking time slot:', error);
            return false;
        }
    }

    async function updateRescheduleTimeSlots(date) {
        rescheduleTimeSlots.innerHTML = '';
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
                document.querySelectorAll('#rescheduleTimeSlots button').forEach(btn => {
                    btn.classList.remove('bg-indigo-100', 'text-indigo-800');
                });
                if (isAvailable) {
                    button.classList.add('bg-indigo-100', 'text-indigo-800');
                }
            };
            rescheduleTimeSlots.appendChild(button);
        }
    }

    // Event listeners for reschedule
    newScheduleDate.addEventListener('change', () => {
        updateRescheduleTimeSlots(newScheduleDate.value);
    });

    cancelReschedule.addEventListener('click', () => {
        rescheduleModal.classList.add('hidden');
    });

    confirmReschedule.addEventListener('click', async () => {
        const selectedTime = document.querySelector('#rescheduleTimeSlots button.bg-indigo-100');
        if (!selectedTime) {
            alert('Please select a time slot');
            return;
        }

        if (!rescheduleReason.value.trim()) {
            alert('Please provide a reason for rescheduling');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('complaint_id', rescheduleComplaintId.value);
            formData.append('new_date', newScheduleDate.value);
            formData.append('new_time', selectedTime.textContent);
            formData.append('reason', rescheduleReason.value);
            
            const response = await fetch('reschedule_complaint.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                try {
                    const rescheduleMessage = `
Dear ${data.data.student_name},

Your counseling session has been rescheduled. Please find the updated details below:

Previous Schedule:
- Date: ${data.data.old_date}
- Time: ${data.data.old_time}

New Schedule:
- Date: ${data.data.new_date}
- Time: ${data.data.new_time}
- Location: Guidance Office

Reason for Reschedule: ${data.data.reason}

Important Reminders:
1. Please arrive 5-10 minutes before your scheduled time
2. Bring any relevant documents or materials related to your concern
3. If you need to reschedule again, please inform the guidance office at least 24 hours before your appointment

If you have any questions or concerns about this change, please contact the guidance office immediately.

Best regards,
EMEMHS Guidance Office`;

                    await sendEmailNotification(
                        data.data.student_email,
                        'Counseling Session Rescheduled - EMEMHS Guidance System',
                        rescheduleMessage
                    );
                    alert('Session rescheduled and notification sent successfully!');
                    window.location.reload();
                } catch (emailError) {
                    console.error('Error sending email:', emailError);
                    alert('Session rescheduled but failed to send notification. Please try again.');
                }
            } else {
                alert('Error rescheduling session: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error rescheduling session:', error);
            alert('Error rescheduling session. Please try again.');
        }
    });

    // Add click handlers for reschedule buttons
    document.querySelectorAll('.reschedule-btn').forEach(button => {
        button.addEventListener('click', function() {
            const complaintId = this.dataset.complaintId;
            const currentDate = this.dataset.currentDate;
            const currentTime = this.dataset.currentTime;
            
            rescheduleComplaintId.value = complaintId;
            document.getElementById('currentDate').textContent = currentDate;
            document.getElementById('currentTime').textContent = currentTime;
            
            // Clear previous selections
            rescheduleReason.value = '';
            newScheduleDate.value = '';
            rescheduleTimeSlots.innerHTML = '';
            
            rescheduleModal.classList.remove('hidden');
        });
    });

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
            
            if (!confirm('Are you sure you want to mark this complaint as resolved?')) {
                return;
            }
            
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
</body>
</html>