<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today
$complaint_type = $_GET['type'] ?? 'all';
$severity = $_GET['severity'] ?? 'all';
$grade_level = $_GET['grade'] ?? 'all';

// Build query with filters
$query = "
    SELECT cc.*,
           COALESCE(cc.severity, 'medium') as severity,
           s.first_name, s.last_name, s.grade_level, s.section
    FROM " . TBL_COMPLAINTS_CONCERNS . " cc
    JOIN " . TBL_STUDENTS . " s ON cc.student_id = s.id
    WHERE cc.date_created BETWEEN :start_date AND :end_date
";

if ($complaint_type !== 'all') {
    $query .= " AND cc.type = :type";
}
if ($severity !== 'all') {
    $query .= " AND COALESCE(cc.severity, 'medium') = :severity";
}
if ($grade_level !== 'all') {
    $query .= " AND s.grade_level = :grade_level";
}

$query .= " ORDER BY cc.date_created DESC, cc.time_created DESC";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
if ($complaint_type !== 'all') {
    $stmt->bindParam(':type', $complaint_type);
}
if ($severity !== 'all') {
    $stmt->bindParam(':severity', $severity);
}
if ($grade_level !== 'all') {
    $stmt->bindParam(':grade_level', $grade_level);
}
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_complaints = count($complaints);
$by_type = [];
$by_severity = [];
$by_status = [];
$by_grade = [];

foreach ($complaints as $complaint) {
    // By type
    $type = $complaint['type'];
    $by_type[$type] = ($by_type[$type] ?? 0) + 1;
    
    // By severity
    $sev = $complaint['severity'];
    $by_severity[$sev] = ($by_severity[$sev] ?? 0) + 1;
    
    // By status
    $status = $complaint['status'];
    $by_status[$status] = ($by_status[$status] ?? 0) + 1;
    
    // By grade
    $grade = $complaint['grade_level'];
    $by_grade[$grade] = ($by_grade[$grade] ?? 0) + 1;
}

// Get unique values for filters
$types_query = $pdo->query("SELECT DISTINCT type FROM " . TBL_COMPLAINTS_CONCERNS . " ORDER BY type");
$types = $types_query->fetchAll(PDO::FETCH_COLUMN);

$grades_query = $pdo->query("SELECT DISTINCT grade_level FROM " . TBL_STUDENTS . " ORDER BY grade_level");
$grades = $grades_query->fetchAll(PDO::FETCH_COLUMN);

// Check if this is a print request
$isPrintView = isset($_GET['print']) && $_GET['print'] == '1';

// If print view, render simplified version
if ($isPrintView) {
    include 'reports_print.php';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #800000;
            --primary-hover: #600000;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .print-container {
                max-width: 100%;
                padding: 20px !important;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            table {
                font-size: 10pt;
            }
            
            .chart-container {
                page-break-inside: avoid;
            }
            
            /* Hide any remaining UI elements */
            button, .hover\:bg-gray-50 {
                display: none !important;
            }
        }

        .stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .compact-filter {
            transition: max-height 0.3s ease;
            overflow: hidden;
        }

        .compact-filter.collapsed {
            max-height: 0;
        }

        .compact-filter.expanded {
            max-height: 500px;
        }

        .mini-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: white;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }

        .progress-bar-thin {
            height: 6px;
        }

        .table-compact td, .table-compact th {
            padding: 0.5rem 0.75rem;
        }
    </style>
</head>
<body class="min-h-screen">
<div class="no-print">
    <?php include 'navigation-admin.php'?>
</div>
<div class="main-content">
    <main class="min-h-screen">
        <div class="p-4 md:p-6 print-container">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4 no-print">
                <div class="flex items-center gap-3">
                    <div class="bg-[#800000]/10 text-[#800000] rounded-lg p-2.5">
                        <i class="fas fa-chart-bar text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Reports & Analytics</h1>
                        <p class="text-gray-500 text-xs">Period: <?= date('M d', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="toggleFilters()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2 text-sm">
                        <i class="fas fa-filter"></i>
                        <span class="hidden sm:inline">Filters</span>
                    </button>
                    <button onclick="printReport()" class="bg-[#800000] hover:bg-[#600000] text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2 text-sm">
                        <i class="fas fa-print"></i>
                        <span class="hidden sm:inline">Print</span>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div id="filterPanel" class="compact-filter collapsed no-print mb-4">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" 
                                   class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" 
                                   class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                            <select name="type" class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                <option value="all" <?= $complaint_type === 'all' ? 'selected' : '' ?>>All Types</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>" <?= $complaint_type === $type ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Severity</label>
                            <select name="severity" class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                <option value="all" <?= $severity === 'all' ? 'selected' : '' ?>>All Severities</option>
                                <option value="low" <?= $severity === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $severity === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $severity === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= $severity === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Grade Level</label>
                            <select name="grade" class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                <option value="all" <?= $grade_level === 'all' ? 'selected' : '' ?>>All Grades</option>
                                <?php foreach ($grades as $grade): ?>
                                    <option value="<?= htmlspecialchars($grade) ?>" <?= $grade_level === $grade ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($grade) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-5 flex gap-2">
                            <button type="submit" class="bg-[#800000] hover:bg-[#600000] text-white px-4 py-1.5 rounded-lg transition-colors duration-200 text-sm">
                                <i class="fas fa-check mr-1"></i>Apply
                            </button>
                            <a href="reports.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg transition-colors duration-200 text-sm">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function toggleFilters() {
                    const panel = document.getElementById('filterPanel');
                    panel.classList.toggle('collapsed');
                    panel.classList.toggle('expanded');
                }

                function printReport() {
                    // Get current URL parameters
                    const params = new URLSearchParams(window.location.search);
                    params.set('print', '1');
                    
                    // Open print view in new window
                    const printUrl = 'reports.php?' + params.toString();
                    window.open(printUrl, '_blank');
                }
            </script>

            <!-- Print Header -->
            <div class="hidden print:block mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">EMEMHS Guidance Office</h1>
                <h2 class="text-xl font-semibold text-gray-700 mt-2">Complaints & Concerns Report</h2>
                <p class="text-gray-600 mt-2">Period: <?= date('F d, Y', strtotime($start_date)) ?> - <?= date('F d, Y', strtotime($end_date)) ?></p>
                <p class="text-gray-600">Generated: <?= date('F d, Y h:i A') ?></p>
            </div>

            <!-- Summary Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <div class="stat-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-0.5">Total</p>
                            <h3 class="text-2xl font-bold text-[#800000]"><?= $total_complaints ?></h3>
                        </div>
                        <div class="bg-[#800000]/10 rounded-lg p-2">
                            <i class="fas fa-clipboard-list text-xl text-[#800000]"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-0.5">Pending</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?= $by_status['pending'] ?? 0 ?></h3>
                        </div>
                        <div class="bg-orange-100 rounded-lg p-2">
                            <i class="fas fa-clock text-xl text-orange-600"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-0.5">Scheduled</p>
                            <h3 class="text-2xl font-bold text-blue-600"><?= $by_status['scheduled'] ?? 0 ?></h3>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-2">
                            <i class="fas fa-calendar-check text-xl text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-0.5">Resolved</p>
                            <h3 class="text-2xl font-bold text-green-600"><?= $by_status['resolved'] ?? 0 ?></h3>
                        </div>
                        <div class="bg-green-100 rounded-lg p-2">
                            <i class="fas fa-check-circle text-xl text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- By Type -->
                <div class="bg-white p-6 rounded-lg shadow-sm chart-container">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Complaints by Type</h3>
                    <div class="space-y-3">
                        <?php 
                        arsort($by_type);
                        foreach ($by_type as $type => $count): 
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700"><?= htmlspecialchars($type) ?></span>
                                    <span class="text-gray-600"><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-[#800000] h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- By Severity -->
                <div class="bg-white p-6 rounded-lg shadow-sm chart-container">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Complaints by Severity</h3>
                    <div class="space-y-3">
                        <?php 
                        $severity_order = ['urgent', 'high', 'medium', 'low'];
                        $severity_colors = [
                            'urgent' => '#a16207',
                            'high' => '#dc2626',
                            'medium' => '#d97706',
                            'low' => '#16a34a'
                        ];
                        foreach ($severity_order as $sev): 
                            if (!isset($by_severity[$sev])) continue;
                            $count = $by_severity[$sev];
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700"><?= ucfirst($sev) ?></span>
                                    <span class="text-gray-600"><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: <?= $percentage ?>%; background-color: <?= $severity_colors[$sev] ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- By Grade Level -->
                <div class="bg-white p-6 rounded-lg shadow-sm chart-container">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Complaints by Grade Level</h3>
                    <div class="space-y-3">
                        <?php 
                        ksort($by_grade);
                        foreach ($by_grade as $grade => $count): 
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700">Grade <?= htmlspecialchars($grade) ?></span>
                                    <span class="text-gray-600"><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- By Status -->
                <div class="bg-white p-6 rounded-lg shadow-sm chart-container">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Complaints by Status</h3>
                    <div class="space-y-3">
                        <?php 
                        $status_colors = [
                            'pending' => '#d97706',
                            'scheduled' => '#2563eb',
                            'resolved' => '#16a34a',
                            'unresolved' => '#dc2626'
                        ];
                        foreach ($by_status as $status => $count): 
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700"><?= ucfirst($status) ?></span>
                                    <span class="text-gray-600"><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: <?= $percentage ?>%; background-color: <?= $status_colors[$status] ?? '#6b7280' ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Charts Section - Compact Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- By Type -->
                <div class="bg-white p-4 rounded-lg shadow-sm chart-container">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-tags text-[#800000]"></i>
                        By Type
                    </h3>
                    <div class="space-y-2">
                        <?php 
                        arsort($by_type);
                        foreach ($by_type as $type => $count): 
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700 truncate"><?= htmlspecialchars($type) ?></span>
                                    <span class="text-gray-600 ml-2"><?= $count ?> <span class="text-gray-400">(<?= number_format($percentage, 0) ?>%)</span></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full progress-bar-thin">
                                    <div class="bg-[#800000] progress-bar-thin rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- By Severity -->
                <div class="bg-white p-4 rounded-lg shadow-sm chart-container">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        By Severity
                    </h3>
                    <div class="space-y-2">
                        <?php 
                        $severity_order = ['urgent', 'high', 'medium', 'low'];
                        $severity_colors = [
                            'urgent' => '#a16207',
                            'high' => '#dc2626',
                            'medium' => '#d97706',
                            'low' => '#16a34a'
                        ];
                        foreach ($severity_order as $sev): 
                            if (!isset($by_severity[$sev])) continue;
                            $count = $by_severity[$sev];
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700"><?= ucfirst($sev) ?></span>
                                    <span class="text-gray-600"><?= $count ?> <span class="text-gray-400">(<?= number_format($percentage, 0) ?>%)</span></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full progress-bar-thin">
                                    <div class="progress-bar-thin rounded-full" style="width: <?= $percentage ?>%; background-color: <?= $severity_colors[$sev] ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- By Grade Level -->
                <div class="bg-white p-4 rounded-lg shadow-sm chart-container">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-graduation-cap text-blue-600"></i>
                        By Grade Level
                    </h3>
                    <div class="space-y-2">
                        <?php 
                        ksort($by_grade);
                        foreach ($by_grade as $grade => $count): 
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700">Grade <?= htmlspecialchars($grade) ?></span>
                                    <span class="text-gray-600"><?= $count ?> <span class="text-gray-400">(<?= number_format($percentage, 0) ?>%)</span></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full progress-bar-thin">
                                    <div class="bg-blue-600 progress-bar-thin rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- By Status -->
                <div class="bg-white p-4 rounded-lg shadow-sm chart-container">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-tasks text-green-600"></i>
                        By Status
                    </h3>
                    <div class="space-y-2">
                        <?php 
                        $status_colors = [
                            'pending' => '#d97706',
                            'scheduled' => '#2563eb',
                            'resolved' => '#16a34a',
                            'unresolved' => '#dc2626'
                        ];
                        foreach ($by_status as $status => $count): 
                            $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                        ?>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700"><?= ucfirst($status) ?></span>
                                    <span class="text-gray-600"><?= $count ?> <span class="text-gray-400">(<?= number_format($percentage, 0) ?>%)</span></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full progress-bar-thin">
                                    <div class="progress-bar-thin rounded-full" style="width: <?= $percentage ?>%; background-color: <?= $status_colors[$status] ?? '#6b7280' ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recommendations Section -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-4 page-break">
                <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-lightbulb text-yellow-500"></i>
                    Key Recommendations
                </h3>
                <div class="space-y-2">
                    <?php
                    // Generate recommendations based on data
                    $recommendations = [];
                    
                    // Check for high-frequency complaint types
                    arsort($by_type);
                    $top_types = array_slice($by_type, 0, 3, true);
                    foreach ($top_types as $type => $count) {
                        if ($count >= 5) {
                            $recommendations[] = [
                                'priority' => 'high',
                                'title' => "Seminar on " . htmlspecialchars($type),
                                'description' => "There are {$count} reported cases of {$type}. Consider organizing a school-wide seminar to address this issue.",
                                'icon' => 'fa-users-class'
                            ];
                        }
                    }
                    
                    // Check for high severity cases
                    $high_severity = ($by_severity['urgent'] ?? 0) + ($by_severity['high'] ?? 0);
                    if ($high_severity > 0) {
                        $recommendations[] = [
                            'priority' => 'urgent',
                            'title' => "Immediate Attention Required",
                            'description' => "There are {$high_severity} high-priority cases that require immediate intervention and follow-up.",
                            'icon' => 'fa-exclamation-triangle'
                        ];
                    }
                    
                    // Check for grade-specific issues
                    arsort($by_grade);
                    $top_grade = array_key_first($by_grade);
                    if ($by_grade[$top_grade] >= 5) {
                        $recommendations[] = [
                            'priority' => 'medium',
                            'title' => "Grade {$top_grade} Intervention",
                            'description' => "Grade {$top_grade} has the highest number of complaints ({$by_grade[$top_grade]} cases). Consider targeted interventions for this grade level.",
                            'icon' => 'fa-school'
                        ];
                    }
                    
                    // Check pending cases
                    $pending = $by_status['pending'] ?? 0;
                    if ($pending > 10) {
                        $recommendations[] = [
                            'priority' => 'medium',
                            'title' => "Pending Cases Backlog",
                            'description' => "There are {$pending} pending cases awaiting scheduling. Consider allocating additional counseling time slots.",
                            'icon' => 'fa-calendar-plus'
                        ];
                    }
                    
                    if (empty($recommendations)) {
                        $recommendations[] = [
                            'priority' => 'low',
                            'title' => "Maintain Current Programs",
                            'description' => "Current intervention programs appear to be effective. Continue monitoring and maintain existing support systems.",
                            'icon' => 'fa-check-circle'
                        ];
                    }
                    
                    $priority_colors = [
                        'urgent' => 'bg-red-50 border-red-200 text-red-800',
                        'high' => 'bg-orange-50 border-orange-200 text-orange-800',
                        'medium' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                        'low' => 'bg-green-50 border-green-200 text-green-800'
                    ];
                    
                    foreach ($recommendations as $rec):
                    ?>
                        <div class="border-l-4 p-3 rounded <?= $priority_colors[$rec['priority']] ?>">
                            <div class="flex items-start gap-2">
                                <i class="fas <?= $rec['icon'] ?> text-base mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm mb-0.5"><?= $rec['title'] ?></h4>
                                    <p class="text-xs leading-relaxed"><?= $rec['description'] ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Detailed Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden page-break">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-list text-gray-600"></i>
                        Detailed List (<?= $total_complaints ?> records)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-compact">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Student</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Grade</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Severity</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php if (!empty($complaints)): ?>
                                <?php foreach ($complaints as $complaint): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 text-xs text-gray-700 whitespace-nowrap">
                                            <?= date('M d, Y', strtotime($complaint['date_created'])) ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-900 font-medium">
                                            <?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-700">
                                            <?= htmlspecialchars($complaint['grade_level'] . '-' . $complaint['section']) ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-700">
                                            <?= htmlspecialchars($complaint['type']) ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs">
                                            <span class="px-2 py-0.5 text-xs rounded-full font-medium <?php
                                                $sev = $complaint['severity'];
                                                echo $sev === 'urgent' ? 'bg-red-100 text-red-700' :
                                                     ($sev === 'high' ? 'bg-orange-100 text-orange-700' :
                                                     ($sev === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'));
                                            ?>">
                                                <?= ucfirst($complaint['severity']) ?>
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-xs">
                                            <span class="px-2 py-0.5 text-xs rounded-full font-medium <?php
                                                $status = $complaint['status'];
                                                echo $status === 'pending' ? 'bg-orange-100 text-orange-700' :
                                                     ($status === 'scheduled' ? 'bg-blue-100 text-blue-700' :
                                                     ($status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'));
                                            ?>">
                                                <?= ucfirst($complaint['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                        <p>No complaints found for the selected filters.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer for Print -->
            <div class="hidden print:block mt-8 pt-4 border-t border-gray-300 text-center text-sm text-gray-600">
                <p>EMEMHS Guidance Office - Confidential Report</p>
                <p>This document contains sensitive student information and should be handled accordingly.</p>
            </div>
        </div>
    </main>
</div>
</body>
</html>
