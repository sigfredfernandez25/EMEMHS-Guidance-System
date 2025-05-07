<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: index.php");
    exit();
}
$stmt = $pdo->prepare(SQL_LIST_COMPLAINTS_CONCERNS);
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            background-color: #f3f4f6;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
        }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'?>
    <!-- Main Content -->
    <main class="ml-64 pt-16 min-h-screen">
        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Welcome, <?php echo $_SESSION['staff_name'] ?? 'Staff'; ?></h1>
            
            <!-- Pending Complaints Table -->
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Complaints</h2>
                <div class="table-container">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade&&Section</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidence</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php 
                                $pending_found = false;
                                if (!empty($complaints)):
                                    foreach ($complaints as $complaint):
                                        if ($complaint['status'] == 'pending'):
                                            $pending_found = true;
                                ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['first_name']." ".$complaint['last_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['grade_level']." ".$complaint['section']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['type']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['preferred_counseling_date']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($complaint['evidence']) && !empty($complaint['mime_type'])): ?>
                                                <img src="data:<?php echo $complaint['mime_type']; ?>;base64,<?php echo base64_encode($complaint['evidence']); ?>" 
                                                     alt="Evidence" 
                                                     class="w-16 h-16 object-cover rounded-lg shadow-sm" />
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form action="set-schedule.php" method="POST" class="inline" id="scheduleForm">
                                                <input type="hidden" name="complaint_id" value="<?=$complaint['id']?>">
                                                <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm set-schedule-btn" data-complaint-id="<?=$complaint['id']?>">
                                                    Set Schedule
                                                </button>
                                            </form>
                                            <button class="view-complaint bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm ml-2"
                                                    data-complaint='<?php 
                                                        $complaintData = $complaint;
                                                        // Remove binary data from the JSON
                                                        unset($complaintData['evidence']);
                                                        unset($complaintData['mime_type']);
                                                        echo json_encode($complaintData); 
                                                    ?>'
                                                    data-evidence='<?php echo !empty($complaint['evidence']) ? base64_encode($complaint['evidence']) : ''; ?>'
                                                    data-mime-type='<?php echo !empty($complaint['mime_type']) ? htmlspecialchars($complaint['mime_type']) : ''; ?>'>
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                        endif;
                                    endforeach;
                                endif;
                                if (!$pending_found):
                                ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No pending complaints found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- All Complaints Table -->
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">All Complaints</h2>
            <div class="table-container">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade&&Section</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidence</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($complaints)): ?>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['first_name']." ".$complaint['last_name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['grade_level']." ".$complaint['section']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['type']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-<?php echo strtolower($complaint['status']); ?>">
                                            <?php echo ucfirst($complaint['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $complaint['preferred_counseling_date']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php
                                        if ($complaint['status'] == 'pending') {
                                            echo 'N/A';
                                        } else if ($complaint['status'] == 'scheduled') {
                                                    echo $complaint['scheduled_date'] . ' ' . $complaint['scheduled_time'];
                                        } else if ($complaint['status'] == 'resolved') {
                                                    echo $complaint['scheduled_date'] . ' ' . $complaint['scheduled_time'] . " (Resolved)";
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($complaint['evidence']) && !empty($complaint['mime_type'])): ?>
                                            <img src="data:<?php echo $complaint['mime_type']; ?>;base64,<?php echo base64_encode($complaint['evidence']); ?>" 
                                                 alt="Evidence" 
                                                 class="w-16 h-16 object-cover rounded-lg shadow-sm" />
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <form action="complaint-concern-form.php" method="POST" class="inline">
                                            <input type="hidden" name="user" value="<?= $complaint['id'] ?>">
                                            <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg text-sm">
                                                Edit
                                            </button>
                                        </form>
                                                <button class="view-complaint bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm ml-2"
                                                        data-complaint='<?php 
                                                            $complaintData = $complaint;
                                                            // Remove binary data from the JSON
                                                            unset($complaintData['evidence']);
                                                            unset($complaintData['mime_type']);
                                                            echo json_encode($complaintData); 
                                                        ?>'
                                                        data-evidence='<?php echo !empty($complaint['evidence']) ? base64_encode($complaint['evidence']) : ''; ?>'
                                                        data-mime-type='<?php echo !empty($complaint['mime_type']) ? htmlspecialchars($complaint['mime_type']) : ''; ?>'>
                                                    View
                                                </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No complaints found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                    </div>
            </div>
        </div>
           
            <!-- Scheduled Complaints Table -->
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Scheduled Complaints</h2>
                <div class="table-container">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade&&Section</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidence</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php 
                                $scheduled_found = false;
                                if (!empty($complaints)):
                                    foreach ($complaints as $complaint):
                                        if ($complaint['status'] == 'scheduled'):
                                            $scheduled_found = true;
                                ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['first_name']." ".$complaint['last_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['grade_level']." ".$complaint['section']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['type']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['scheduled_date']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $complaint['scheduled_time']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($complaint['evidence']) && !empty($complaint['mime_type'])): ?>
                                                <img src="data:<?php echo $complaint['mime_type']; ?>;base64,<?php echo base64_encode($complaint['evidence']); ?>" 
                                                     alt="Evidence" 
                                                     class="w-16 h-16 object-cover rounded-lg shadow-sm" />
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form action="mark_resolved.php" method="POST" class="inline">
                                                <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                                                    Mark as Resolved
                                                </button>
                                            </form>
                                            <button class="view-complaint bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm ml-2"
                                                    data-complaint='<?php 
                                                        $complaintData = $complaint;
                                                        // Remove binary data from the JSON
                                                        unset($complaintData['evidence']);
                                                        unset($complaintData['mime_type']);
                                                        echo json_encode($complaintData); 
                                                    ?>'
                                                    data-evidence='<?php echo !empty($complaint['evidence']) ? base64_encode($complaint['evidence']) : ''; ?>'
                                                    data-mime-type='<?php echo !empty($complaint['mime_type']) ? htmlspecialchars($complaint['mime_type']) : ''; ?>'>
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                <?php 
                                        endif;
                                    endforeach;
                                endif;
                                if (!$scheduled_found):
                                ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No scheduled complaints found
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

    <!-- View Details Modal -->
    <div id="viewDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Complaint Details</h3>
                
                <div class="space-y-4">
                    <!-- Student Information -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Student Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">First Name</p>
                                <p class="text-sm font-medium" id="viewFirstName"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Last Name</p>
                                <p class="text-sm font-medium" id="viewLastName"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Grade Level</p>
                                <p class="text-sm font-medium" id="viewGradeLevel"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Section</p>
                                <p class="text-sm font-medium" id="viewSection"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Complaint Information -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Complaint Information</h4>
                        <div class="space-y-2">
                            <div>
                                <p class="text-sm text-gray-500">Type</p>
                                <p class="text-sm font-medium" id="viewType"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Description</p>
                                <p class="text-sm font-medium" id="viewDescription"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="text-sm font-medium" id="viewStatus"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Preferred Date</p>
                                <p class="text-sm font-medium" id="viewPreferredDate"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Scheduled Date</p>
                                <p class="text-sm font-medium" id="viewScheduledDate"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Scheduled Time</p>
                                <p class="text-sm font-medium" id="viewScheduledTime"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Evidence -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Evidence</h4>
                        <div id="viewEvidence" class="mt-2"></div>
                    </div>
                </div>

                <!-- Close Button -->
                <div class="flex justify-end mt-4">
                    <button id="closeViewModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduling Modal -->
    <div id="schedulingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Schedule Counseling Session</h3>
                <div class="mt-2 px-7 py-3">
                    <div class="mb-4">
                        <label for="scheduleDate" class="block text-sm font-medium text-gray-700">Select Date</label>
                        <input type="date" id="scheduleDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Available Time Slots</label>
                        <div id="timeSlots" class="mt-2 grid grid-cols-3 gap-2">
                            <!-- Time slots will be populated here -->
                        </div>
                    </div>
                    <input type="hidden" id="selectedComplaintId">
                </div>
                <div class="flex justify-end mt-4">
                    <button id="cancelSchedule" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2">
                        Cancel
                    </button>
                    <button id="confirmSchedule" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Confirm Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewModal = document.getElementById('viewDetailsModal');
            const closeViewModal = document.getElementById('closeViewModal');

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
                        <img src="data:${mimeType};base64,${evidence}" 
                             alt="Evidence" 
                             class="w-full h-auto object-cover rounded-lg shadow-sm" />
                    `;
                } else {
                    evidenceContainer.innerHTML = '<p class="text-sm text-gray-500">No evidence provided</p>';
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
            const startHour = 8; // 8 AM
            const endHour = 17; // 5 PM
            
            for (let hour = startHour; hour <= endHour; hour++) {
                const time = `${hour}:00`;
                slots.push(time);
            }
            
            return slots;
        }

        // Check time slot availability
        async function checkTimeSlotAvailability(date, time) {
            try {
                const response = await fetch('check_time_slot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ date, time })
                });
                const data = await response.json();
                return data.available;
            } catch (error) {
                console.error('Error checking time slot:', error);
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

            try {
                const requestData = {
                    complaint_id: selectedComplaintId.value,
                    date: scheduleDate.value,
                    time: selectedTime.textContent
                };
                
                console.log('Sending request:', requestData);
                
                const response = await fetch('set-schedule.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                });
                
                // Get the raw response text
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                // Extract just the JSON part (everything before the first [DEBUG])
                const jsonPart = responseText.split('[DEBUG]')[0];
                console.log('JSON part:', jsonPart);
                
                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(jsonPart);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('JSON part:', jsonPart);
                    alert('Error parsing server response. Check console for details.');
                    return;
                }

                if (data.success) {
                    alert('Schedule set successfully');
                    window.location.reload();
                } else {
                    alert('Error setting schedule: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error setting schedule:', error);
                console.error('Full error details:', {
                    message: error.message,
                    stack: error.stack
                });
                alert('Error setting schedule: ' + error.message);
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
    </script>
</body>
</html> 