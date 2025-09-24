<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/notification_logic.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: index.php");
    exit();
}

// Get reschedule requests (prioritize pending, but show recent processed ones too)
$stmt = $pdo->prepare("
    SELECT rr.*, 
           cc.type as complaint_type, cc.scheduled_date, cc.scheduled_time,
           s.first_name, s.last_name, s.grade_level, s.section,
           u.email as student_email
    FROM reschedule_requests rr
    JOIN " . TBL_COMPLAINTS_CONCERNS . " cc ON rr.complaint_id = cc.id
    JOIN " . TBL_STUDENTS . " s ON rr.student_id = s.id
    JOIN " . TBL_USERS . " u ON s.user_id = u.id
    WHERE rr.id IN (
        SELECT MAX(id) 
        FROM reschedule_requests 
        GROUP BY complaint_id
    )
    ORDER BY 
        CASE WHEN rr.status = 'pending' THEN 1 ELSE 2 END,
        rr.date_requested DESC
");
$stmt->execute();
$reschedule_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $admin_response = $_POST['admin_response'] ?? '';
    
    try {
        if ($action === 'approve') {
            // Get request details
            $stmt = $pdo->prepare("SELECT * FROM reschedule_requests WHERE id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                // Update complaint schedule
                $stmt = $pdo->prepare("
                    UPDATE " . TBL_COMPLAINTS_CONCERNS . " 
                    SET scheduled_date = ?, scheduled_time = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$request['preferred_date'], $request['preferred_time'], $request['complaint_id']]);
                
                // Update request status
                $stmt = $pdo->prepare("
                    UPDATE reschedule_requests 
                    SET status = 'approved', admin_response = ?, date_processed = NOW(), processed_by = ?
                    WHERE id = ?
                ");
                $stmt->execute([$admin_response, $_SESSION['user'], $request_id]);
                
                // Get the user_id for the student
                $stmt_user = $pdo->prepare("SELECT user_id FROM " . TBL_STUDENTS . " WHERE id = ?");
                $stmt_user->execute([$request['student_id']]);
                $student_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
                
                if ($student_user) {
                    // Notify student
                    $message = "Your reschedule request has been approved. New schedule: " . 
                              date('F j, Y', strtotime($request['preferred_date'])) . " at " . 
                              date('g:i A', strtotime($request['preferred_time']));
                    
                    createNotification($student_user['user_id'], $request['complaint_id'], 'complaint', 'rescheduled', $message);
                }
                
                $success_message = "Reschedule request approved successfully.";
            }
        } elseif ($action === 'reject') {
            // Update request status
            $stmt = $pdo->prepare("
                UPDATE reschedule_requests 
                SET status = 'rejected', admin_response = ?, date_processed = NOW(), processed_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$admin_response, $_SESSION['user'], $request_id]);
            
            // Get student info for notification
            $stmt = $pdo->prepare("SELECT rr.student_id, rr.complaint_id, s.user_id FROM reschedule_requests rr JOIN " . TBL_STUDENTS . " s ON rr.student_id = s.id WHERE rr.id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                // Notify student
                $message = "Your reschedule request has been rejected. Reason: " . $admin_response;
                createNotification($request['user_id'], $request['complaint_id'], 'complaint', 'reschedule_rejected', $message);
            }
            
            $success_message = "Reschedule request rejected.";
        }
        
        // Refresh the page to show updated data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        $error_message = "Error processing request: " . $e->getMessage();
        error_log("Reschedule request processing error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Requests - Guidance Portal</title>
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

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .status-approved {
            background-color: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .status-rejected {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-approve {
            background-color: #22c55e;
            color: white;
        }

        .btn-approve:hover {
            background-color: #16a34a;
        }

        .btn-reject {
            background-color: #ef4444;
            color: white;
        }

        .btn-reject:hover {
            background-color: #dc2626;
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Reschedule Requests</h1>
                    <p class="text-gray-600">Manage student reschedule requests</p>
                </div>
                <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= $success_message ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <?php if (empty($reschedule_requests)): ?>
                <div class="card p-8 text-center">
                    <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Reschedule Requests</h3>
                    <p class="text-gray-500">There are currently no reschedule requests to review.</p>
                </div>
            <?php else: ?>
                <div class="grid gap-6">
                    <?php foreach ($reschedule_requests as $request): ?>
                        <div class="card p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-3">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?>
                                        </h3>
                                        <span class="text-sm text-gray-500">
                                            <?= htmlspecialchars($request['grade_level'] . ' ' . $request['section']) ?>
                                        </span>
                                        <span class="status-badge status-<?= $request['status'] ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-6 mb-4">
                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2">Current Schedule</h4>
                                            <div class="text-sm text-gray-600">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <i class="fas fa-calendar text-[#800000]"></i>
                                                    <span><?= date('F j, Y', strtotime($request['scheduled_date'])) ?></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-clock text-[#800000]"></i>
                                                    <span><?= date('g:i A', strtotime($request['scheduled_time'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2">Requested Schedule</h4>
                                            <div class="text-sm text-gray-600">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <i class="fas fa-calendar text-blue-600"></i>
                                                    <span><?= date('F j, Y', strtotime($request['preferred_date'])) ?></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-clock text-blue-600"></i>
                                                    <span><?= date('g:i A', strtotime($request['preferred_time'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h4 class="font-medium text-gray-700 mb-2">Reason</h4>
                                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                            <?= htmlspecialchars($request['reason']) ?>
                                        </p>
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Requested on: <?= date('F j, Y g:i A', strtotime($request['date_requested'])) ?>
                                    </div>

                                    <?php if ($request['admin_response']): ?>
                                        <div class="mt-3">
                                            <h4 class="font-medium text-gray-700 mb-2">Admin Response</h4>
                                            <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                <?= htmlspecialchars($request['admin_response']) ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($request['status'] === 'pending'): ?>
                                <div class="border-t pt-4 flex gap-3">
                                    <button onclick="openResponseModal(<?= $request['id'] ?>, 'approve')" 
                                            class="btn btn-approve">
                                        <i class="fas fa-check"></i>
                                        Approve
                                    </button>
                                    <button onclick="openResponseModal(<?= $request['id'] ?>, 'reject')" 
                                            class="btn btn-reject">
                                        <i class="fas fa-times"></i>
                                        Reject
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Response Modal -->
<div id="responseModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto hidden z-50">
    <div class="relative top-20 mx-auto p-6 w-full max-w-md modal-content">
        <div class="pb-4 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-[#800000]" id="modalTitle">Respond to Request</h3>
        </div>
        
        <form method="POST" class="py-6">
            <input type="hidden" id="request_id" name="request_id">
            <input type="hidden" id="action" name="action">
            
            <div class="mb-4">
                <label for="admin_response" class="block text-sm font-medium text-gray-700 mb-2">Response Message</label>
                <textarea id="admin_response" name="admin_response" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#800000]" 
                          placeholder="Provide a response to the student..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeResponseModal()" 
                        class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition duration-200">
                    Cancel
                </button>
                <button type="submit" id="submitBtn" 
                        class="px-4 py-2 rounded-lg text-white transition duration-200">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResponseModal(requestId, action) {
        document.getElementById('request_id').value = requestId;
        document.getElementById('action').value = action;
        
        const modal = document.getElementById('responseModal');
        const title = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('submitBtn');
        
        if (action === 'approve') {
            title.textContent = 'Approve Reschedule Request';
            submitBtn.textContent = 'Approve Request';
            submitBtn.className = 'px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white transition duration-200';
        } else {
            title.textContent = 'Reject Reschedule Request';
            submitBtn.textContent = 'Reject Request';
            submitBtn.className = 'px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition duration-200';
        }
        
        document.getElementById('admin_response').value = '';
        modal.classList.remove('hidden');
    }

    function closeResponseModal() {
        document.getElementById('responseModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('responseModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResponseModal();
        }
    });
</script>
</body>
</html>