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

// Get monthly complaints data for the chart
$stmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(date_created, '%Y-%m') as month,
        COUNT(*) as count
    FROM " . TBL_COMPLAINTS_CONCERNS . "
    WHERE date_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date_created, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthly_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get complaints data for different time periods
$time_periods = [
    'today' => "DATE(date_created) = CURDATE()",
    'week' => "date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
    'month' => "date_created >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
    '3months' => "date_created >= DATE_SUB(NOW(), INTERVAL 3 MONTH)",
    'year' => "date_created >= DATE_SUB(NOW(), INTERVAL 1 YEAR)"
];

$complaints_by_period = [];
foreach ($time_periods as $period => $condition) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM " . TBL_COMPLAINTS_CONCERNS . "
        WHERE $condition
    ");
    $stmt->execute();
    $complaints_by_period[$period] = $stmt->fetchColumn();
}

// Get monthly peak analysis for the last 12 months
$stmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(date_created, '%Y-%m') as month,
        COUNT(*) as count,
        RANK() OVER (ORDER BY COUNT(*) DESC) as rank
    FROM " . TBL_COMPLAINTS_CONCERNS . "
    WHERE date_created >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(date_created, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthly_peak_analysis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get complaints by type distribution (all time)
$stmt = $pdo->prepare("
    SELECT
        type,
        COUNT(*) as count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . TBL_COMPLAINTS_CONCERNS . ")), 2) as percentage
    FROM " . TBL_COMPLAINTS_CONCERNS . "
    GROUP BY type
    ORDER BY count DESC
");
$stmt->execute();
$complaints_by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get complaints by severity distribution
$stmt = $pdo->prepare("
    SELECT
        severity,
        COUNT(*) as count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . TBL_COMPLAINTS_CONCERNS . ")), 2) as percentage
    FROM " . TBL_COMPLAINTS_CONCERNS . "
    GROUP BY severity
    ORDER BY
        CASE
            WHEN severity = 'urgent' THEN 1
            WHEN severity = 'high' THEN 2
            WHEN severity = 'medium' THEN 3
            WHEN severity = 'low' THEN 4
            ELSE 5
        END
");
$stmt->execute();
$complaints_by_severity = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily complaints data for the last 7 days for accurate trend
$stmt = $pdo->prepare("
    SELECT
        DATE(date_created) as date,
        COUNT(*) as count
    FROM " . TBL_COMPLAINTS_CONCERNS . "
    WHERE date_created >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(date_created)
    ORDER BY date ASC
");
$stmt->execute();
$daily_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get weekly complaints data for the last 4 weeks for accurate trend
$stmt = $pdo->prepare("
    SELECT
        CONCAT('Week of ', DATE_FORMAT(DATE_SUB(date_created, INTERVAL WEEKDAY(date_created) DAY), '%M %d')) as week,
        COUNT(*) as count
    FROM " . TBL_COMPLAINTS_CONCERNS . "
    WHERE date_created >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
    GROUP BY YEAR(date_created), WEEK(date_created)
    ORDER BY MIN(date_created) ASC
");
$stmt->execute();
$weekly_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get lost items distribution
$stmt = $pdo->prepare("
    SELECT 
        category,
        COUNT(*) as count
    FROM " . TBL_LOST_ITEMS . "
    GROUP BY category
    ORDER BY count DESC
    LIMIT 4
");
$stmt->execute();
$lost_items_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #800000;
            --primary-hover: #600000;
            --secondary-color: #64748b;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --background-color: #ffffff;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
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

        .minimal-btn {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .minimal-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .minimal-btn i {
            transition: transform 0.2s ease;
        }

        .minimal-btn:hover i {
            transform: translateX(4px);
        }

        .progress-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            transition: width 0.5s ease;
            position: relative;
        }

        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(128,0,0,0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::after {
            opacity: 1;
        }

        .chart-container {
            position: relative;
            padding: 1rem;
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border-radius: 0.5rem;
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }

        .activity-item {
            position: relative;
            padding-left: 1.5rem;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary-color);
            opacity: 0.2;
        }

        .activity-item:last-child::before {
            height: 50%;
        }

        .activity-item::after {
            content: '';
            position: absolute;
            left: -4px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-color);
            opacity: 0.2;
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

        .sidebar {
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .sidebar-item {
            transition: all 0.2s ease;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
        }

        .sidebar-item:hover {
            background-color: #f1f5f9;
            transform: translateX(4px);
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

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 9999px;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
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
            color: var(--primary-color);
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
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

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-scheduled {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .table-container {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-row {
            transition: all 0.2s ease;
        }

        .table-row:hover {
            background-color: #f8fafc;
        }

        .gradient-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .progress-ring {
            transform: rotate(-90deg);
        }

        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform-origin: 50% 50%;
        }

        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .analytics-card {
            position: relative;
            overflow: hidden;
        }

        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
        }

        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .analytics-select {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            color: var(--secondary-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .analytics-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .analytics-select:hover {
            border-color: var(--primary-color);
        }

        .peak-indicator {
            display: inline-flex;
            align-items: center;
            margin-right: 0.5rem;
        }

        .peak-indicator .dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            margin-right: 0.25rem;
        }

        .peak-indicator.top .dot {
            background-color: var(--primary-color);
        }

        .peak-indicator.normal .dot {
            background-color: #80000060;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 1rem;
            gap: 1rem;
        }

        .chart-legend-item {
            display: flex;
            align-items: center;
            font-size: 0.75rem;
            color: var(--secondary-color);
        }

        .chart-legend-color {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            margin-right: 0.25rem;
        }

        .analytics-stat {
            text-align: center;
            margin-top: 1rem;
        }

        .analytics-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }

        .analytics-stat-label {
            font-size: 0.75rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .chart-tooltip {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 0.75rem;
        }

        .chart-tooltip-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }

        .chart-tooltip-value {
            color: var(--secondary-color);
        }
    </style>
</head>
<body class="min-h-screen bg-white">

<?php include 'navigation-admin.php'?>
<div class="main-content">
    <main class="pt-16 min-h-screen">
        <!-- Welcome Section with Notification Bell -->
        <div class="mb-8 flex justify-between items-center px-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome back, <?php echo $_SESSION['staff_name'] ?? 'Staff'; ?></h1>
                <p class="text-gray-600">Here's what's happening today</p>
            </div>
            <a href="notifications.php" class="notification-icon <?php echo $unread_count > 0 ? 'has-notifications' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600 hover:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <?php if ($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="p-8">
            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="minimal-card p-6 stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium mb-1">Pending Complaints</p>
                            <h3 class="text-3xl font-bold text-[#800000]"><?=$pending_complaints?></h3>
                            <div class="progress-bar mt-2">
                                <div class="progress-bar-fill bg-[#800000]" style="width: <?= min(($pending_complaints / max($total_students, 1)) * 100, 100) ?>%"></div>
                            </div>
                        </div>
                        <div class="text-[#800000] transform hover:scale-110 transition-transform">
                            <i class="fas fa-exclamation-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="minimal-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium mb-1">Found Items</p>
                            <h3 class="text-3xl font-bold text-[#800000]"><?=$found_items?></h3>
                            <div class="progress-bar mt-2">
                                <div class="progress-bar-fill bg-[#800000]" style="width: <?= min(($found_items / max($total_students, 1)) * 100, 100) ?>%"></div>
                            </div>
                        </div>
                        <div class="text-[#800000]">
                            <i class="fas fa-box text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="minimal-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium mb-1">Total Students</p>
                            <h3 class="text-3xl font-bold text-[#800000]"><?=$total_students?></h3>
                            <div class="progress-bar mt-2">
                                <div class="progress-bar-fill bg-[#800000]" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="text-[#800000]">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Complaints Analytics -->
                <div class="minimal-card chart-container analytics-card">
                    <div class="analytics-header">
                        <h2 class="section-title text-sm font-medium text-[#800000]">Complaints & Concerns Analytics</h2>
                        <select id="timePeriodSelect" class="analytics-select">
                            <option value="today">Today</option>
                            <option value="week" selected>Last 7 Days</option>
                            <option value="month">Last Month</option>
                            <option value="3months">Last 3 Months</option>
                            <option value="year">Last Year</option>
                        </select>
                    </div>
                    <div class="h-48">
                        <canvas id="complaintsChart"></canvas>
                    </div>
                    <div class="analytics-stat">
                        <div class="analytics-stat-number" id="totalComplaints">0</div>
                        <div class="analytics-stat-label">Total Complaints & Concerns</div>
                    </div>
                </div>

                <!-- Monthly Peak Analysis -->
                <div class="minimal-card chart-container analytics-card">
                    <h2 class="section-title text-sm font-medium text-[#800000]">Monthly Peak Analysis</h2>
                    <div class="h-48">
                        <canvas id="monthlyPeakChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-gray-600 mb-2">Peak Months (Last 12 Months)</div>
                        <div class="space-y-1" id="peakMonthsList">
                            <!-- Peak months will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaints by Type and Severity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="minimal-card chart-container analytics-card">
                    <h2 class="section-title text-sm font-medium text-[#800000]">Complaints by Type</h2>
                    <div class="h-48">
                        <canvas id="complaintsTypeChart"></canvas>
                    </div>
                </div>

                <div class="minimal-card chart-container analytics-card">
                    <h2 class="section-title text-sm font-medium text-[#800000]">Complaints by Severity</h2>
                    <div class="h-48">
                        <canvas id="complaintsSeverityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Lost Items Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-8">
                <div class="minimal-card chart-container analytics-card">
                    <h2 class="section-title text-sm font-medium text-[#800000]">Lost Items Distribution</h2>
                    <div class="h-48">
                        <canvas id="lostItemsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="minimal-card p-6 mb-8">
                <h2 class="section-title text-sm font-medium text-[#800000]">Quick Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="complaint-concern-admin.php" class="minimal-btn px-4 py-2 rounded-lg flex items-center hover:bg-[#800000] hover:text-white transition-all duration-300">
                        <i class="fas fa-clipboard-list mr-2"></i>View Complaints
                    </a>
                    <a href="found-items.php" class="minimal-btn px-4 py-2 rounded-lg flex items-center hover:bg-[#800000] hover:text-white transition-all duration-300">
                        <i class="fas fa-box mr-2"></i>View Found Items
                    </a>
                    <a href="students-list.php" class="minimal-btn px-4 py-2 rounded-lg flex items-center hover:bg-[#800000] hover:text-white transition-all duration-300">
                        <i class="fas fa-users mr-2"></i>View Students
                    </a>
                </div>
            </div>


            <!-- Recent Activities and Lost Items -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="minimal-card p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="section-title text-sm font-medium text-[#800000]">Recent Concerns</h2>
                            <a href="complaint-concern-admin.php" class="text-xs text-[#800000] hover:text-[#800000]/80 flex items-center">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($recent_complaints as $complaint): ?>
                                <div class="minimal-card p-3 border border-gray-100 activity-item">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <?php
                                            $icon_class = '';
                                            $text_class = 'text-[#800000]';
                                            switch($complaint['type']) {
                                                case 'family_problems':
                                                    $icon_class = 'fas fa-home';
                                                    break;
                                                case 'academic_stress':
                                                    $icon_class = 'fas fa-book';
                                                    break;
                                                case 'peer_relationship':
                                                    $icon_class = 'fas fa-users';
                                                    break;
                                                default:
                                                    $icon_class = 'fas fa-comment';
                                            }
                                            ?>
                                            <div class="<?= $text_class ?>">
                                                <i class="<?= $icon_class ?> text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                                    <?php 
                                                    $type_display = $complaint['type'];
                                                    if ($type_display === 'peer_relationship') {
                                                        $type_display = 'Peer Pressure';
                                                    } else {
                                                        $type_display = ucwords(str_replace('_', ' ', $type_display));
                                                    }
                                                    echo $type_display;
                                                    ?>
                                                </h3>
                                                <span class="text-xs px-2 py-1 rounded-full <?= 
                                                    $complaint['status'] === 'pending' ? 'bg-[#800000]/10 text-[#800000]' : 
                                                    ($complaint['status'] === 'scheduled' ? 'bg-[#800000]/10 text-[#800000]' : 'bg-[#800000]/10 text-[#800000]')
                                                ?>">
                                                    <?= ucfirst($complaint['status']) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                                                <?= htmlspecialchars($complaint['description'] ?? 'No description provided') ?>
                                            </p>
                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                <div class="flex items-center space-x-3">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-user-circle mr-1"></i>
                                                        <?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?>
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-calendar-alt mr-1"></i>
                                                        <?= date('M d, Y', strtotime($complaint['date_created'])) ?>
                                                    </span>
                                                </div>
                                                <a href="complaint-concern-admin.php?id=<?= $complaint['id'] ?>" 
                                                   class="text-[#800000] hover:text-[#800000]/80">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="minimal-card p-4">
                        <h2 class="section-title text-sm font-medium text-[#800000] mb-4">Recent Lost Items</h2>
                        <div class="space-y-3">
                            <?php foreach ($recent_lost_items as $item): ?>
                                <div class="minimal-card p-3 border border-gray-100 activity-item">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                                <img src="data:<?= $item['mime_type'] ?>;base64,<?= base64_encode($item['photo']) ?>" 
                                                     alt="Item Photo" 
                                                     class="w-12 h-12 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-50 rounded flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h3 class="text-sm font-medium text-gray-900 truncate">
                                                    <?= htmlspecialchars($item['item_name']) ?>
                                                </h3>
                                                <span class="text-xs px-2 py-1 rounded-full <?= 
                                                    $item['status'] === 'pending' ? 'bg-[#800000]/10 text-[#800000]' : 'bg-[#800000]/10 text-[#800000]'
                                                ?>">
                                                    <?= ucfirst($item['status']) ?>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                <span class="flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    <?= date('M d, Y', strtotime($item['date'])) ?>
                                                </span>
                                                <button onclick="notifyStudent(<?= $item['id'] ?>)" 
                                                        class="text-[#800000] hover:text-[#800000]/80">
                                                    <i class="fas fa-bell mr-1"></i> Notify
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Panel -->
            <div class="fixed right-0 top-16 bottom-0 w-96 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out" id="notificationsPanel">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">System Notifications</h2>
                </div>
                <div class="p-6 space-y-4 overflow-y-auto max-h-[calc(100vh-8rem)]">
                    <?php foreach ($recent_notifications as $notification): ?>
                    <div class="card p-4 hover:shadow-md transition-shadow">
                        <div class="text-sm text-gray-500 mb-2">
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
</div>

    <script>
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

            // Prepare chart data from PHP variables
            const monthlyData = <?= json_encode($monthly_complaints) ?>;
            const lostItemsData = <?= json_encode($lost_items_distribution) ?>;
            const complaintsByPeriod = <?= json_encode($complaints_by_period) ?>;
            const monthlyPeakData = <?= json_encode($monthly_peak_analysis) ?>;
            const complaintsByType = <?= json_encode($complaints_by_type) ?>;
            const dailyComplaintsData = <?= json_encode($daily_complaints) ?>;
            const weeklyComplaintsData = <?= json_encode($weekly_complaints) ?>;
            const complaintsBySeverity = <?= json_encode($complaints_by_severity) ?>;

            // Complaints Analytics Chart
            const complaintsCtx = document.getElementById('complaintsChart').getContext('2d');
            let complaintsChart = null;

            function createComplaintsChart(period = 'week') {
                const data = complaintsByPeriod[period] || 0;
                document.getElementById('totalComplaints').textContent = data;

                if (complaintsChart) {
                    complaintsChart.destroy();
                }

                // Use actual database data for accurate visualization
                let chartData = [];
                let chartLabels = [];

                if (period === 'today') {
                    chartLabels = ['Today'];
                    chartData = [data];
                } else if (period === 'week' && dailyComplaintsData.length > 0) {
                    // Use actual daily data for the last 7 days
                    chartLabels = dailyComplaintsData.map(item => {
                        const date = new Date(item.date);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    });
                    chartData = dailyComplaintsData.map(item => parseInt(item.count));
                } else if (period === 'month' && weeklyComplaintsData.length > 0) {
                    // Use actual weekly data for the last 4 weeks
                    chartLabels = weeklyComplaintsData.map(item => item.week);
                    chartData = weeklyComplaintsData.map(item => parseInt(item.count));
                } else if (period === '3months') {
                    // For 3 months, show monthly totals
                    const monthlyTotals = {};
                    monthlyPeakData.forEach(item => {
                        const monthKey = item.month;
                        if (!monthlyTotals[monthKey]) {
                            monthlyTotals[monthKey] = 0;
                        }
                        monthlyTotals[monthKey] += parseInt(item.count);
                    });

                    chartLabels = Object.keys(monthlyTotals).map(month => {
                        const date = new Date(month + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }).slice(-3); // Last 3 months

                    chartData = Object.values(monthlyTotals).slice(-3);
                } else if (period === 'year') {
                    // For yearly view, show monthly data
                    const yearlyData = {};
                    monthlyPeakData.forEach(item => {
                        const monthKey = item.month;
                        if (!yearlyData[monthKey]) {
                            yearlyData[monthKey] = 0;
                        }
                        yearlyData[monthKey] += parseInt(item.count);
                    });

                    chartLabels = Object.keys(yearlyData).map(month => {
                        const date = new Date(month + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short' });
                    }).slice(-12); // Last 12 months

                    chartData = Object.values(yearlyData).slice(-12);
                } else {
                    chartLabels = ['Current Period'];
                    chartData = [data];
                }

                complaintsChart = new Chart(complaintsCtx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'Complaints & Concerns',
                            data: chartData,
                            borderColor: '#800000',
                            backgroundColor: 'rgba(128, 0, 0, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#800000',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f1f5f9'
                                },
                                ticks: {
                                    font: {
                                        size: 10,
                                        family: "'Inter', sans-serif"
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10,
                                        family: "'Inter', sans-serif"
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        }
                    }
                });
            }

            // Monthly Peak Analysis Chart
            const monthlyPeakCtx = document.getElementById('monthlyPeakChart').getContext('2d');
            new Chart(monthlyPeakCtx, {
                type: 'bar',
                data: {
                    labels: monthlyPeakData.map(item => {
                        const date = new Date(item.month + '-01');
                        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Complaints',
                        data: monthlyPeakData.map(item => item.count),
                        backgroundColor: [
                            '#800000', '#a52a2a', '#dc143c', '#b22222', '#8b0000',
                            '#80000080', '#a52a2a80', '#dc143c80', '#b2222280', '#8b000080',
                            '#80000060', '#a52a2a60'
                        ],
                        borderColor: [
                            '#600000', '#8b1c1c', '#b91c1c', '#991b1b', '#7f1d1d',
                            '#60000080', '#8b1c1c80', '#b91c1c80', '#991b1b80', '#7f1d1d80',
                            '#60000060', '#8b1c1c60'
                        ],
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: "'Inter', sans-serif"
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });

            // Complaints by Type Chart
            const complaintsTypeCtx = document.getElementById('complaintsTypeChart').getContext('2d');
            new Chart(complaintsTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: complaintsByType.map(item => {
                        const type = item.type.replace(/_/g, ' ');
                        return type.charAt(0).toUpperCase() + type.slice(1);
                    }),
                    datasets: [{
                        data: complaintsByType.map(item => item.count),
                        backgroundColor: [
                            '#800000',
                            '#a52a2a',
                            '#dc143c',
                            '#b22222',
                            '#8b0000',
                            '#80000080',
                            '#a52a2a80',
                            '#dc143c80'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 10,
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    },
                    cutout: '60%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });

            // Complaints by Severity Chart
            const complaintsSeverityCtx = document.getElementById('complaintsSeverityChart').getContext('2d');
            new Chart(complaintsSeverityCtx, {
                type: 'doughnut',
                data: {
                    labels: complaintsBySeverity.map(item => {
                        const severityLabels = {
                            'low': 'Low Priority',
                            'medium': 'Medium Priority',
                            'high': 'High Priority',
                            'urgent': 'Urgent Priority'
                        };
                        return severityLabels[item.severity] || item.severity;
                    }),
                    datasets: [{
                        data: complaintsBySeverity.map(item => item.count),
                        backgroundColor: [
                            '#22c55e', // Low - Green
                            '#eab308', // Medium - Yellow
                            '#f97316', // High - Orange
                            '#dc2626'  // Urgent - Red
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 10,
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    },
                    cutout: '60%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });

            // Lost Items Chart with enhanced styling
            const lostItemsCtx = document.getElementById('lostItemsChart').getContext('2d');
            new Chart(lostItemsCtx, {
                type: 'doughnut',
                data: {
                    labels: lostItemsData.map(item => item.category),
                    datasets: [{
                        data: lostItemsData.map(item => item.count),
                        backgroundColor: [
                            '#800000',
                            '#80000080',
                            '#80000060',
                            '#80000040'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 10,
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    },
                    cutout: '75%',
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });

            // Populate peak months list
            function populatePeakMonths() {
                const peakMonthsList = document.getElementById('peakMonthsList');
                const topMonths = monthlyPeakData
                    .sort((a, b) => b.count - a.count)
                    .slice(0, 5);

                peakMonthsList.innerHTML = topMonths.map((item, index) => {
                    const date = new Date(item.month + '-01');
                    const monthName = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                    const isTop = item.rank <= 3;
                    const colors = ['#800000', '#a52a2a', '#dc143c', '#b22222', '#8b0000'];
                    const color = colors[index] || '#800000';
                    return `
                        <div class="flex justify-between items-center text-xs">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full mr-2" style="background-color: ${color}"></span>
                                <span class="text-gray-600">${monthName}</span>
                            </div>
                            <span class="font-medium" style="color: ${color}">${item.count}</span>
                        </div>
                    `;
                }).join('');
            }

            // Time period dropdown functionality
            const timePeriodSelect = document.getElementById('timePeriodSelect');
            timePeriodSelect.addEventListener('change', function() {
                createComplaintsChart(this.value);
            });

            // Initialize charts
            createComplaintsChart('week');
            populatePeakMonths();
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

        // Automatic SMS Notification Functions
        // function showSMSResult(message, isSuccess = true) {
        //     // Create notification element if it doesn't exist
        //     let resultDiv = document.getElementById('smsResult');
        //     if (!resultDiv) {
        //         resultDiv = document.createElement('div');
        //         resultDiv.id = 'smsResult';
        //         resultDiv.className = 'fixed top-20 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm hidden';
        //         document.body.appendChild(resultDiv);
        //     }

        //     const resultContent = document.getElementById('smsResultContent') || resultDiv;

        //     resultContent.innerHTML = `
        //         <div class="flex items-center ${isSuccess ? 'text-green-700' : 'text-red-700'}">
        //             <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
        //             <div>
        //                 <div class="font-medium">SMS Notification</div>
        //                 <div class="text-sm">${message}</div>
        //             </div>
        //         </div>
        //     `;
        //     resultDiv.className = `fixed top-20 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${isSuccess ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`;
        //     resultDiv.classList.remove('hidden');

        //     // Auto-hide after 8 seconds
        //     setTimeout(() => {
        //         resultDiv.classList.add('hidden');
        //     }, 8000);
        // }

        // function checkAndSendAutomaticNotifications() {
        //     const formData = new FormData();
        //     formData.append('action', 'check_and_send_notifications');

        //     fetch('../logic/admin_sms_notifications.php', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => {
        //         // Check if response is ok
        //         if (!response.ok) {
        //             throw new Error(`HTTP error! status: ${response.status}`);
        //         }

        //         // Try to parse JSON
        //         return response.json();
        //     })
        //     .then(data => {
        //         console.log('SMS Check response:', data);

        //         if (data.success && data.notifications_sent > 0) {
        //             showSMSResult(`${data.notifications_sent} automatic SMS notification(s) sent successfully`, true);
        //         } else if (data.success) {
        //             // No notifications needed - silent success
        //             console.log('SMS Check completed:', data.message);
        //         } else {
        //             console.error('SMS Check failed:', data.message);
        //             if (data.error) {
        //                 console.error('Error details:', data.error);
        //             }
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error checking for automatic notifications:', error);

        //         // Show user-friendly error message
        //         showSMSResult('Failed to check for notifications. Please check console for details.', false);
        //     });
        // }

        // // Test connection function (for debugging)
        // function testSMSConnection() {
        //     const formData = new FormData();
        //     formData.append('action', 'test_connection');

        //     fetch('../logic/admin_sms_notifications.php', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         console.log('Connection test result:', data);
        //         if (data.success) {
        //             showSMSResult('Connection test successful! Check console for details.', true);
        //         } else {
        //             showSMSResult('Connection test failed: ' + data.message, false);
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Connection test error:', error);
        //         showSMSResult('Connection test failed. Check console for details.', false);
        //     });
        // }

        // // Test basic database connection
        // function testBasicConnection() {
        //     fetch('../logic/test_sms.php', {
        //         method: 'GET'
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         console.log('Basic connection test result:', data);
        //         if (data.success) {
        //             showSMSResult('Basic database connection successful! Admin count: ' + data.admin_count, true);
        //         } else {
        //             showSMSResult('Basic connection failed: ' + data.message, false);
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Basic connection test error:', error);
        //         showSMSResult('Basic connection test failed. Check console for details.', false);
        //     });
        // }

        // // Debug individual components
        // function debugSMSComponents() {
        //     const formData = new FormData();
        //     formData.append('action', 'debug_check');

        //     fetch('../logic/admin_sms_notifications.php', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         console.log('Debug check result:', data);
        //         if (data.success) {
        //             showSMSResult('Debug check successful! Check console for component details.', true);
        //         } else {
        //             showSMSResult('Debug check failed: ' + data.message, false);
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Debug check error:', error);
        //         showSMSResult('Debug check failed. Check console for details.', false);
        //     });
        // }

        // // Realistic workday notification timing
        // function getNextNotificationTime() {
        //     const now = new Date();
        //     const currentHour = now.getHours();
        //     const currentMinute = now.getMinutes();

        //     // Morning notification: 8:00 AM
        //     const morningTime = new Date();
        //     morningTime.setHours(8, 0, 0, 0);

        //     // Afternoon notification: 2:00 PM
        //     const afternoonTime = new Date();
        //     afternoonTime.setHours(14, 0, 0, 0);

        //     // If it's before 8 AM, next check is 8 AM
        //     if (currentHour < 8) {
        //         return morningTime;
        //     }
        //     // If it's between 8 AM and 2 PM, next check is 2 PM
        //     else if (currentHour < 14) {
        //         return afternoonTime;
        //     }
        //     // If it's after 2 PM, next check is tomorrow 8 AM
        //     else {
        //         const tomorrowMorning = new Date();
        //         tomorrowMorning.setDate(tomorrowMorning.getDate() + 1);
        //         tomorrowMorning.setHours(8, 0, 0, 0);
        //         return tomorrowMorning;
        //     }
        // }

        // function scheduleNextNotification() {
        //     const nextTime = getNextNotificationTime();
        //     const now = new Date();
        //     const timeUntilNext = nextTime.getTime() - now.getTime();

        //     console.log('Next SMS notification scheduled for:', nextTime.toLocaleString());

        //     setTimeout(() => {
        //         checkAndSendAutomaticNotifications();
        //         // Schedule the next one after this check
        //         scheduleNextNotification();
        //     }, timeUntilNext);
        // }

        // // Check for notifications on page load
        // document.addEventListener('DOMContentLoaded', function() {
        //     // Initial check after page loads (with a small delay)
        //     setTimeout(checkAndSendAutomaticNotifications, 2000);

        //     // Schedule notifications at realistic times (8 AM and 2 PM)
        //     scheduleNextNotification();

        //     // Make test functions available globally for debugging
        //     window.testSMSConnection = testSMSConnection;
        //     window.testBasicConnection = testBasicConnection;
        //     window.debugSMSComponents = debugSMSComponents;
        // });
   
let notifSentToday = false; // track if already sent

function checkAndSendNotif() {
    let now = new Date();
    let hour = now.getHours();
    let minute = now.getMinutes();

    console.log("⏰ Current time:", hour + ":" + minute, "SentToday:", notifSentToday);

    // Reset flag after 8 AM so it works again next day
    if (hour >= 8) {
        notifSentToday = false;
        return;
    }

    // Only between 7:00 - 7:59 AM
    if (hour === 7 && !notifSentToday) {
        // Check if minute is exactly 0 or 30
        if (minute === 0 || minute === 30) {
            // Mark as sent so it won't send again
            notifSentToday = true;

            // Send AJAX request to PHP backend
            fetch("../logic/admin_sms_notif.php", { method: "POST" })
                .then(response => response.text())
                .then(data => {
                    console.log("✅ Notification Sent:", data);
                })
                .catch(error => {
                    console.error("❌ Error sending notification:", error);
                });
        }
    }
}

// Check every minute
setInterval(checkAndSendNotif, 10000);

// Run immediately on load too
checkAndSendNotif();


    </script>
</body>
</html> 