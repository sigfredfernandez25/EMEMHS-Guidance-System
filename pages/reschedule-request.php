<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/notification_logic.php';

// Check if student is logged in
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student's scheduled complaints with their reschedule request status
$stmt = $pdo->prepare("
    SELECT cc.*, s.first_name, s.last_name,
           rr.status as reschedule_status,
           rr.preferred_date as requested_date,
           rr.preferred_time as requested_time,
           rr.admin_response,
           rr.date_processed
    FROM " . TBL_COMPLAINTS_CONCERNS . " cc
    JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
    LEFT JOIN reschedule_requests rr ON cc.id = rr.complaint_id 
        AND rr.id = (SELECT MAX(id) FROM reschedule_requests WHERE complaint_id = cc.id)
    WHERE cc.student_id = ? AND cc.status = 'scheduled'
    ORDER BY cc.scheduled_date ASC, cc.scheduled_time ASC
");
$stmt->execute([$student_id]);
$scheduled_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle reschedule request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reschedule'])) {
    $complaint_id = $_POST['complaint_id'];
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];
    $reason = $_POST['reason'];
    
    try {
        // Insert reschedule request
        $stmt = $pdo->prepare("
            INSERT INTO reschedule_requests (complaint_id, student_id, preferred_date, preferred_time, reason, status, date_requested)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$complaint_id, $student_id, $preferred_date, $preferred_time, $reason]);
        
        // Notify admin about reschedule request
        $stmt = $pdo->prepare("SELECT id FROM " . TBL_USERS . " WHERE role = 'admin'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get student name from database
        $stmt_student = $pdo->prepare("SELECT first_name, last_name FROM " . TBL_STUDENTS . " WHERE id = ?");
        $stmt_student->execute([$student_id]);
        $student_info = $stmt_student->fetch(PDO::FETCH_ASSOC);
        $student_name = $student_info ? $student_info['first_name'] . ' ' . $student_info['last_name'] : 'Student';
        $message = "Reschedule request from $student_name for complaint ID: $complaint_id";
        
        foreach ($admins as $admin) {
            createNotification($admin['id'], $complaint_id, 'complaint', 'reschedule_request', $message);
        }
        
        $success_message = "Your reschedule request has been submitted successfully. You will be notified once it's reviewed.";
        
    } catch (Exception $e) {
        $error_message = "Error submitting reschedule request. Please try again.";
        error_log("Reschedule request error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Request - Guidance Portal</title>
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

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .form-input {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            background-color: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navigation.php'; ?>
    
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#800000] mb-2">Reschedule Request</h1>
            <p class="text-gray-600">Request to reschedule your counseling sessions</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>
                <?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if (empty($scheduled_complaints)): ?>
            <div class="card p-8 text-center">
                <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Scheduled Sessions</h3>
                <p class="text-gray-500 mb-4">You don't have any scheduled counseling sessions to reschedule.</p>
                <a href="complaint-concern.php" class="btn-primary inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Submit a Complaint
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($scheduled_complaints as $complaint): ?>
                    <div class="card p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <?= ucwords(str_replace('_', ' ', $complaint['type'])) ?>
                                </h3>
                                <div class="flex items-center text-sm text-gray-600 mb-2">
                                    <i class="fas fa-calendar-alt text-[#800000] mr-2"></i>
                                    <span><?= date('F j, Y', strtotime($complaint['scheduled_date'])) ?></span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-clock text-[#800000] mr-2"></i>
                                    <span><?= date('g:i A', strtotime($complaint['scheduled_time'])) ?></span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                    Scheduled
                                </span>
                                <?php if ($complaint['reschedule_status']): ?>
                                    <?php if ($complaint['reschedule_status'] === 'pending'): ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-clock mr-1"></i>Reschedule Pending
                                        </span>
                                    <?php elseif ($complaint['reschedule_status'] === 'approved'): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check mr-1"></i>Reschedule Approved
                                        </span>
                                    <?php elseif ($complaint['reschedule_status'] === 'rejected'): ?>
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-times mr-1"></i>Reschedule Rejected
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($complaint['reschedule_status'] && ($complaint['reschedule_status'] === 'approved' || $complaint['reschedule_status'] === 'rejected')): ?>
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h4 class="font-medium text-gray-700 mb-2">
                                    <?= $complaint['reschedule_status'] === 'approved' ? 'Reschedule Details' : 'Last Reschedule Request' ?>
                                </h4>
                                <?php if ($complaint['reschedule_status'] === 'approved'): ?>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Your session was successfully rescheduled to:
                                    </p>
                                    <div class="text-sm text-gray-700">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fas fa-calendar text-green-600"></i>
                                            <span><?= date('F j, Y', strtotime($complaint['requested_date'])) ?></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-clock text-green-600"></i>
                                            <span><?= date('g:i A', strtotime($complaint['requested_time'])) ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($complaint['admin_response']): ?>
                                    <div class="mt-3">
                                        <p class="text-xs text-gray-500 mb-1">Admin Response:</p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($complaint['admin_response']) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($complaint['date_processed']): ?>
                                    <p class="text-xs text-gray-400 mt-2">
                                        Processed on: <?= date('F j, Y g:i A', strtotime($complaint['date_processed'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="border-t pt-4">
                            <?php if ($complaint['reschedule_status'] === 'pending'): ?>
                                <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg inline-flex items-center cursor-not-allowed">
                                    <i class="fas fa-clock mr-2"></i>
                                    Reschedule Request Pending
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Please wait for admin response before requesting another reschedule.</p>
                            <?php elseif ($complaint['reschedule_status'] === 'approved'): ?>
                                <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg inline-flex items-center cursor-not-allowed">
                                    <i class="fas fa-check mr-2"></i>
                                    Reschedule Completed
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Your session has been successfully rescheduled.</p>
                            <?php else: ?>
                                <button onclick="openRescheduleForm(<?= $complaint['id'] ?>, '<?= $complaint['scheduled_date'] ?>', '<?= $complaint['scheduled_time'] ?>')" 
                                        class="btn-primary inline-flex items-center">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    Request Reschedule
                                </button>
                                <?php if ($complaint['reschedule_status'] === 'rejected'): ?>
                                    <p class="text-xs text-gray-500 mt-2">Your previous reschedule request was rejected. You can submit a new request.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Reschedule Request Modal -->
    <div id="rescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto hidden z-50">
        <div class="relative top-20 mx-auto p-6 w-full max-w-md bg-white rounded-2xl shadow-xl">
            <div class="pb-4 border-b border-gray-100">
                <h3 class="text-xl font-semibold text-[#800000] flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    Request Reschedule
                </h3>
            </div>
            
            <form method="POST" class="py-6 space-y-4">
                <input type="hidden" id="complaint_id" name="complaint_id">
                
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
                    <label for="preferred_date" class="block text-sm font-medium text-gray-700 mb-2">Preferred New Date</label>
                    <input type="date" id="preferred_date" name="preferred_date" required 
                           class="form-input w-full" min="<?= date('Y-m-d') ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Time</label>
                    <div id="timeSlots" class="grid grid-cols-3 gap-3">
                        <!-- Time slots will be populated here -->
                    </div>
                    <input type="hidden" id="preferred_time" name="preferred_time" required>
                </div>

                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Reschedule</label>
                    <textarea id="reason" name="reason" rows="3" required 
                              class="form-input w-full" placeholder="Please explain why you need to reschedule..."></textarea>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" onclick="closeRescheduleModal()" 
                            class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" name="submit_reschedule" 
                            class="btn-primary flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
                    body: JSON.stringify({
                        date: date,
                        time: time
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                return data.available;
            } catch (error) {
                console.error('Error checking time slot:', error);
                return false;
            }
        }

        // Update time slots display
        async function updateTimeSlots(date) {
            const timeSlots = document.getElementById('timeSlots');
            timeSlots.innerHTML = '';
            
            if (!date) {
                return;
            }
            
            const slots = generateTimeSlots();
            
            for (const time of slots) {
                const isAvailable = await checkTimeSlotAvailability(date, time);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `px-3 py-2 rounded-md text-sm transition-colors ${
                    isAvailable 
                        ? 'bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer' 
                        : 'bg-red-100 text-red-800 cursor-not-allowed'
                }`;
                
                // Format time for display
                const displayTime = new Date('2000-01-01 ' + time).toLocaleTimeString('en-US', { 
                    hour: 'numeric', minute: '2-digit', hour12: true 
                });
                button.textContent = displayTime;
                button.disabled = !isAvailable;
                
                button.onclick = () => {
                    if (isAvailable) {
                        // Remove selection from all buttons
                        document.querySelectorAll('#timeSlots button').forEach(btn => {
                            btn.classList.remove('bg-[#800000]', 'text-white');
                            btn.classList.add('bg-green-100', 'text-green-800');
                        });
                        
                        // Select this button
                        button.classList.remove('bg-green-100', 'text-green-800');
                        button.classList.add('bg-[#800000]', 'text-white');
                        
                        // Set the hidden input value
                        document.getElementById('preferred_time').value = time;
                    }
                };
                
                timeSlots.appendChild(button);
            }
        }

        function openRescheduleForm(complaintId, currentDate, currentTime) {
            document.getElementById('complaint_id').value = complaintId;
            document.getElementById('currentDate').textContent = new Date(currentDate).toLocaleDateString('en-US', { 
                year: 'numeric', month: 'long', day: 'numeric' 
            });
            document.getElementById('currentTime').textContent = new Date('2000-01-01 ' + currentTime).toLocaleTimeString('en-US', { 
                hour: 'numeric', minute: '2-digit', hour12: true 
            });
            
            // Clear form
            document.getElementById('preferred_date').value = '';
            document.getElementById('preferred_time').value = '';
            document.getElementById('reason').value = '';
            document.getElementById('timeSlots').innerHTML = '';
            
            document.getElementById('rescheduleModal').classList.remove('hidden');
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').classList.add('hidden');
        }

        // Event listener for date change
        document.addEventListener('DOMContentLoaded', function() {
            const preferredDate = document.getElementById('preferred_date');
            preferredDate.addEventListener('change', () => {
                updateTimeSlots(preferredDate.value);
            });
        });

        // Close modal when clicking outside
        document.getElementById('rescheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRescheduleModal();
            }
        });
    </script>
</body>
</html>