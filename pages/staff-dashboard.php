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

// Get total number of complaints
$stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STATUS);
$stmt->execute(['pending']);
$pending_complaints = $stmt->fetchColumn();

// Get total number of lost items
$stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TBL_LOST_ITEMS . " WHERE status = 'found'");
$stmt->execute();
$found_items  = $stmt->fetchColumn();

// Get total number of students
$stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TBL_STUDENTS);
$stmt->execute();
$total_students = $stmt->fetchColumn();

// Get recent complaints
$stmt = $pdo->prepare("
    SELECT c.*, s.first_name, s.last_name 
    FROM " . TBL_COMPLAINTS_CONCERNS . " c 
    JOIN " . TBL_STUDENTS . " s ON c.student_id = s.id 
    ORDER BY c.date_created DESC 
    LIMIT 5
");
$stmt->execute();
$recent_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent lost items
$stmt = $pdo->prepare("
    SELECT l.*, s.first_name, s.last_name 
    FROM " . TBL_LOST_ITEMS . " l 
    JOIN " . TBL_STUDENTS . " s ON l.student_id = s.id 
    ORDER BY l.date DESC 
    LIMIT 5
");
$stmt->execute();
$recent_lost_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent notifications
$stmt = $pdo->prepare("
    SELECT n.*, s.first_name, s.last_name 
    FROM " . TBL_NOTIFICATIONS . " n 
    JOIN " . TBL_STUDENTS . " s ON n.user_id = s.id 
    ORDER BY n.date_created DESC 
    LIMIT 5
");
$stmt->execute();
$recent_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unread notifications count
$unread_count = getUnreadNotificationsCount($_SESSION['user']);
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
            animation: pulse 2s infinite;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .notification-icon {
            position: relative;
            transition: all 0.3s ease;
        }
        .notification-icon:hover {
            transform: scale(1.1);
        }
        .notification-icon.has-notifications {
            color: #800000;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="min-h-screen">

   <?php include 'navigation-admin.php'?>
    <main class="ml-64 pt-16 min-h-screen">
             <!-- Welcome Section with Notification Bell -->
             <div class="mb-8 flex justify-between items-center">
            <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Welcome, <?php echo $_SESSION['staff_name'] ?? 'Staff'; ?></h1>
            </div>
            <a href="notifications.php" class="notification-icon <?php echo $unread_count > 0 ? 'has-notifications' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600 hover:text-[#800000] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <?php if ($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="p-8">

           
            
            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pending Complaints/Concerns</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?=$pending_complaints?></h3>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Found Items</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?=$found_items?></h3>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-box text-blue-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Students</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?=$total_students?></h3>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-users text-green-500 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="complaint-concern-admin.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-clipboard-list mr-2"></i>View Complaints/Concerns
                    </a>
                    <a href="found-items.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-box mr-2"></i>View found Items
                    </a>
           
                    <a href="students-list.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-users mr-2"></i>View All Students
                    </a>
                </div>
            </div>

            <!-- Recent Activities and Lost Items -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Concerns</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concern Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($recent_complaints as $complaint): ?>
                                        <tr>
                                            <td class="px-6 py-4"><?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?></td>
                                            <td class="px-6 py-4"><?= htmlspecialchars($complaint['type']) ?>created_at</td>
                                            <td class="px-6 py-4"><?= date('m/d/Y', strtotime($complaint['date_created'])) ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 text-xs rounded-full <?= 
                                                    $complaint['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($complaint['status'] === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')
                                                ?>">
                                                    <?= ucfirst($complaint['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <a href="complaint-concern-admin.php?id=<?= $complaint['id'] ?>" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Lost Items</h2>
                        <div class="space-y-4">
                            <?php foreach ($recent_lost_items as $item): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                            <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                                <img src="data:<?= $item['mime_type'] ?>;base64,<?= base64_encode($item['photo']) ?>" 
                                                     alt="Item Photo" 
                                                     class="w-10 h-10 object-cover rounded-lg mr-2">
                                            <?php else: ?>
                                        <i class="fas fa-image text-gray-400 mr-2"></i>
                                            <?php endif; ?>
                                            <span class="font-medium"><?= htmlspecialchars($item['item_name']) ?></span>
                                    </div>
                                        <span class="text-sm text-gray-500"><?= date('m/d/Y', strtotime($item['date'])) ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                        <span class="px-2 py-1 text-xs rounded-full <?= 
                                            $item['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'
                                        ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                        <button onclick="notifyStudent(<?= $item['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-bell"></i> Notify
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>  

            <!-- Notifications Panel -->
            <div class="fixed right-0 top-16 bottom-0 w-80 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out" id="notificationsPanel">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">System Notifications</h2>
                </div>
                    <div class="p-4 space-y-4">
                    <?php foreach ($recent_notifications as $notification): ?>
                    <div class="border-b pb-4">
                            <div class="text-sm text-gray-500">
                                <?= date('h:i A', strtotime($notification['time_created'])) ?>
                    </div>
                            <p class="text-gray-800">
                                <?= htmlspecialchars($notification['message']) ?>
                            </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Add any necessary JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Profile dropdown toggle
            const profileButton = document.querySelector('.relative button');
            const profileDropdown = document.querySelector('.relative .absolute');
            
            if (profileButton && profileDropdown) {
                profileButton.addEventListener('click', function() {
                    profileDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function(event) {
                    if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                        profileDropdown.classList.add('hidden');
                    }
                });
            }

            // Toggle notifications panel
            const notificationsButton = document.querySelector('.fa-bell').parentElement;
            const notificationsPanel = document.getElementById('notificationsPanel');
            
            notificationsButton.addEventListener('click', function() {
                notificationsPanel.classList.toggle('translate-x-full');
            });

            // Close notifications panel when clicking outside
            document.addEventListener('click', function(event) {
                if (!notificationsButton.contains(event.target) && !notificationsPanel.contains(event.target)) {
                    notificationsPanel.classList.add('translate-x-full');
                }
            });

            // Add click event listeners to all notify buttons
            const notifyButtons = document.querySelectorAll('.notify-btn');
            notifyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    if (itemId) {
                        notifyStudent(itemId);
                    }
                });
            });
        });

        // Function to notify student about a found item
        function notifyStudent(itemId) {
            if (!confirm('Are you sure you want to notify the student about this found item?')) {
                return;
            }

            const formData = new FormData();
            formData.append('item_id', itemId);

            fetch('notify_student.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Refresh the page or update the UI as needed
                    location.reload();
                } else {
                    alert(data.message || 'Failed to notify student');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while notifying the student');
            });
        }
    </script>
</body>
</html> 