<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/notification_logic.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
}
$student_id = $_SESSION['student_id'];

// Get student details - handle case where student_id might be null
if ($student_id) {
    $stmt = $pdo->prepare("SELECT first_name FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // If no student_id in session, try to get student by user_id
    $stmt = $pdo->prepare("SELECT id, first_name FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        $_SESSION['student_id'] = $student['id'];
        $student_id = $student['id'];
    }
}

// If still no student found, set default values
if (!$student) {
    $student = ['first_name' => 'Student'];
    $student_id = null;
}

// Get notification count
$unread_count = getUnreadNotificationsCount($_SESSION['user']);

// Get recent notifications
$stmt = $pdo->prepare(SQL_GET_STUDENT_NOTIFICATIONS);
$stmt->execute([$_SESSION['user']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize default values
$recent_complaints = [];
$recent_lost_items = [];
$total_complaints = 0;
$pending_complaints = 0;
$scheduled_complaints = 0;
$resolved_complaints = 0;
$pending_lost_items = 0;
$found_lost_items = 0;

// Only fetch data if we have a valid student_id
if ($student_id) {
    // Get recent complaints
    $stmt = $pdo->prepare("SELECT * FROM complaints_concerns WHERE student_id = ? ORDER BY date_created DESC, time_created DESC LIMIT 3");
    $stmt->execute([$student_id]);
    $recent_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent lost items
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE student_id = ? ORDER BY date DESC, time DESC LIMIT 3");
    $stmt->execute([$student_id]);
    $recent_lost_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT);
    $stmt->execute([$student_id]);
    $total_complaints = $stmt->fetchColumn();

    $stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT_STATUS);
    $stmt->execute([$student_id, 'pending']);
    $pending_complaints = $stmt->fetchColumn();

    $stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT_STATUS);
    $stmt->execute([$student_id, 'scheduled']);
    $scheduled_complaints = $stmt->fetchColumn();

    $stmt = $pdo->prepare(SQL_SUM_LIST_COMPLAINTS_CONCERNS_BY_STUDENT_STATUS);
    $stmt->execute([$student_id, 'resolved']);
    $resolved_complaints = $stmt->fetchColumn();
}

$stmt = $pdo->prepare(SQL_SUM_LIST_LOST_ITEMS_BY_STUDENT);
$stmt->execute([$student_id]);
$total_lost_items = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_LOST_ITEMS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'pending']);
$pending_lost_items = $stmt->fetchColumn();

$stmt = $pdo->prepare(SQL_SUM_LIST_LOST_ITEMS_BY_STUDENT_STATUS);
$stmt->execute([$student_id, 'found']);
$found_lost_items = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EMEMHS Guidance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Mobile-first card design */
        .dashboard-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            border: 1px solid #f1f5f9;
        }

        .dashboard-card:active {
            transform: scale(0.98);
        }

        @media (min-width: 768px) {
            .dashboard-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.15);
            }
        }

        /* Mobile-optimized stats */
        .stat-card {
            background: linear-gradient(135deg, #800000 0%, #600000 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
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

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 1;
        }

        @media (min-width: 640px) {
            .stat-value {
                font-size: 2.25rem;
            }
        }

        /* Status badges */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-scheduled {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-resolved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-found {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* Action buttons */
        .action-btn {
            background: white;
            border: 2px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .action-btn:active {
            transform: scale(0.98);
            border-color: #800000;
        }

        @media (min-width: 768px) {
            .action-btn:hover {
                border-color: #800000;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(128, 0, 0, 0.15);
            }
        }

        /* Mobile-optimized spacing */
        .mobile-section {
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .mobile-section {
                margin-bottom: 2rem;
            }
        }

        /* Touch-friendly elements */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        /* Responsive text */
        .responsive-text {
            font-size: 0.875rem;
            line-height: 1.5;
        }

        @media (min-width: 640px) {
            .responsive-text {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="px-4 py-4 sm:py-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Mobile-First Welcome Section -->
        <div class="mobile-section">
            <div class="text-center sm:text-left">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                    Welcome back, <?= htmlspecialchars($student['first_name']) ?>! 👋
                </h1>
                <p class="text-sm sm:text-base text-gray-600 mb-4">
                    <?php if ($pending_complaints > 0 || $pending_lost_items > 0): ?>
                        You have <?= $pending_complaints ?> pending concerns and <?= $pending_lost_items ?> lost item reports.
                    <?php else: ?>
                        Everything looks good! No pending items to review.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Mobile-First Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mobile-section">
            <div class="stat-card">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <span class="stat-value"><?= $total_complaints ?></span>
                    </div>
                    <h3 class="font-semibold text-white/90 mb-2">Total Complaints</h3>
                    <div class="flex items-center text-xs text-white/80">
                        <span><?= $resolved_complaints ?> resolved</span>
                        <span class="mx-2">•</span>
                        <span><?= $pending_complaints ?> pending</span>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <span class="stat-value"><?= $total_lost_items ?></span>
                    </div>
                    <h3 class="font-semibold text-white/90 mb-2">Lost Items</h3>
                    <div class="flex items-center text-xs text-white/80">
                        <span><?= $found_lost_items ?> found</span>
                        <span class="mx-2">•</span>
                        <span><?= $pending_lost_items ?> pending</span>
                    </div>
                </div>
            </div>

            <div class="stat-card sm:col-span-2 lg:col-span-1">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="stat-value"><?= $scheduled_complaints ?></span>
                    </div>
                    <h3 class="font-semibold text-white/90 mb-2">Scheduled Sessions</h3>
                    <div class="text-xs text-white/80">
                        <?= $scheduled_complaints > 0 ? 'Next session scheduled' : 'No upcoming sessions' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile-First Quick Actions -->
        <div class="mobile-section">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="complaint-concern-form.php" class="action-btn touch-target">
                    <div class="p-3 bg-[#800000]/10 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">Submit Complaint</h3>
                        <p class="text-sm text-gray-600">Report a concern or issue</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="lost-item-form.php" class="action-btn touch-target">
                    <div class="p-3 bg-[#800000]/10 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">Report Lost Item</h3>
                        <p class="text-sm text-gray-600">Lost or found something?</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Mobile-First Content Sections -->
        <div class="space-y-6">
            <!-- Recent Activity Section -->
            <div class="dashboard-card p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Recent Complaints</h2>
                    <a href="complaint-concern.php" class="text-sm text-[#800000] font-medium hover:underline">
                        View All →
                    </a>
                </div>
                
                <div class="space-y-3">
                    <?php if (empty($recent_complaints)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No complaints submitted yet</p>
                            <a href="complaint-concern-form.php" class="text-[#800000] text-sm font-medium hover:underline mt-2 inline-block">
                                Submit your first complaint
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_complaints as $complaint): ?>
                            <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex-1 mb-3 sm:mb-0">
                                        <h3 class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($complaint['type']) ?></h3>
                                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                            <?= htmlspecialchars(substr($complaint['description'], 0, 120)) ?>...
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= date('M d, Y', strtotime($complaint['date_created'])) ?>
                                        </p>
                                    </div>
                                    <span class="status-badge status-<?= strtolower($complaint['status']) ?> self-start">
                                        <?= ucfirst($complaint['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lost Items Section -->
            <div class="dashboard-card p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Recent Lost Items</h2>
                    <a href="lost_item.php" class="text-sm text-[#800000] font-medium hover:underline">
                        View All →
                    </a>
                </div>
                
                <div class="space-y-3">
                    <?php if (empty($recent_lost_items)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No lost items reported</p>
                            <a href="lost-item-form.php" class="text-[#800000] text-sm font-medium hover:underline mt-2 inline-block">
                                Report a lost item
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_lost_items as $item): ?>
                            <div class="border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex-1 mb-3 sm:mb-0">
                                        <h3 class="font-medium text-gray-900 mb-1"><?= htmlspecialchars($item['item_name']) ?></h3>
                                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($item['description']) ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?= date('M d, Y', strtotime($item['date'])) ?>
                                        </p>
                                    </div>
                                    <span class="status-badge status-<?= strtolower($item['status']) ?> self-start">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notifications & Quick Links -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Notifications -->
                <div class="dashboard-card p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
                        <?php if ($unread_count > 0): ?>
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                <?= $unread_count ?> new
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="space-y-3">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-6">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <p class="text-gray-500 text-sm">No notifications</p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                                <div class="border-l-4 border-[#800000] bg-gray-50 p-3 rounded-r-lg">
                                    <p class="text-sm text-gray-800 responsive-text"><?= htmlspecialchars($notification['message']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?= date('M d, Y', strtotime($notification['date_created'])) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                            <a href="notifications.php" class="block text-center text-sm text-[#800000] font-medium hover:underline mt-3">
                                View all notifications
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="dashboard-card p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
                    <div class="space-y-3">
                        <a href="profile.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors touch-target">
                            <div class="p-2 bg-gray-100 rounded-lg mr-3">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">My Profile</span>
                        </a>
                        
                        <a href="reschedule-request.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors touch-target">
                            <div class="p-2 bg-gray-100 rounded-lg mr-3">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Reschedule Request</span>
                        </a>
                        
                        <a href="found-items.php" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors touch-target">
                            <div class="p-2 bg-gray-100 rounded-lg mr-3">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">Found Items</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>

</html>