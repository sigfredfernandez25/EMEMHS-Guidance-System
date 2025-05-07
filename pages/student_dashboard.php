<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/notification_logic.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
}
$student_id = $_SESSION['student_id'];

// Get notification count
$unread_count = getUnreadNotificationsCount($student_id);

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
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .stat-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #800000, #a52a2a);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(128, 0, 0, 0.1) 0%, rgba(165, 42, 42, 0.1) 100%);
        }

        .section-title {
            position: relative;
            padding-left: 1rem;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(to bottom, #800000, #a52a2a);
            border-radius: 2px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(128, 0, 0, 0.1) 0%, rgba(165, 42, 42, 0.1) 100%);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section with Notification Bell -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-[#800000] mb-2">Welcome Back!</h1>
                <p class="text-gray-600">Here's what's happening with your complaints and lost items.</p>
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

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="quick-actions">
                <a href="complaint-concern-form.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">New Complaint</h3>
                        <p class="text-sm text-gray-600">Submit a new complaint or concern</p>
                    </div>
                </a>
                <a href="lost-item-form.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Report Lost Item</h3>
                        <p class="text-sm text-gray-600">Report a lost item</p>
                    </div>
                </a>
                <a href="notifications.php" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                        <p class="text-sm text-gray-600">Check your updates</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Complaint Statistics Section -->
        <section class="mb-8">
            <h2 class="section-title text-xl md:text-2xl font-semibold text-gray-800 mb-6">Complaint Statistics</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $total_complaints ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Total Complaints</h3>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $pending_complaints ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Pending</h3>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $scheduled_complaints ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Scheduled</h3>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $resolved_complaints ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Resolved</h3>
                </div>
            </div>
        </section>

        <!-- Lost Item Statistics Section -->
        <section>
            <h2 class="section-title text-xl md:text-2xl font-semibold text-gray-800 mb-6">Lost Item Statistics</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $total_lost_items ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Total Lost Items</h3>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $pending_lost_items ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Pending</h3>
                </div>

                <div class="stat-card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#800000]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-[#800000]"><?= $found_lost_items ?></span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Found</h3>
                </div>
            </div>
        </section>
    </main>

    <script src="js/mobile-menu.js"></script>
</body>

</html>