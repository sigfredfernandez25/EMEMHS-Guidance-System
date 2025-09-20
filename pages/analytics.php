<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get analytics data
$analyticsData = [];

try {
    // Time-based metrics
    $stmt = $pdo->prepare(SQL_GET_COMPLAINTS_TODAY);
    $stmt->execute();
    $analyticsData['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->prepare(SQL_GET_COMPLAINTS_LAST_7_DAYS);
    $stmt->execute();
    $analyticsData['last_7_days'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->prepare(SQL_GET_COMPLAINTS_LAST_MONTH);
    $stmt->execute();
    $analyticsData['last_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->prepare(SQL_GET_COMPLAINTS_LAST_3_MONTHS);
    $stmt->execute();
    $analyticsData['last_3_months'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->prepare(SQL_GET_COMPLAINTS_LAST_YEAR);
    $stmt->execute();
    $analyticsData['last_year'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Complaint type distribution
    $stmt = $pdo->prepare(SQL_GET_COMPLAINT_TYPE_DISTRIBUTION);
    $stmt->execute();
    $analyticsData['type_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly trends
    $stmt = $pdo->prepare(SQL_GET_MONTHLY_COMPLAINT_TRENDS);
    $stmt->execute();
    $analyticsData['monthly_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Peak months
    $stmt = $pdo->prepare(SQL_GET_PEAK_MONTHS);
    $stmt->execute();
    $analyticsData['peak_months'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching analytics data: " . $e->getMessage());
    $analyticsData = [
        'today' => 0,
        'last_7_days' => 0,
        'last_month' => 0,
        'last_3_months' => 0,
        'last_year' => 0,
        'type_distribution' => [],
        'monthly_trends' => [],
        'peak_months' => []
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - EMEMHS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .header-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, #a52a2a 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <?php include 'navigation-admin.php'; ?>

    <div class="pt-5 main-content">
        <main class="min-h-screen">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center px-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Analytics Dashboard</h1>
                    <p class="text-gray-600">Monitor complaints and concerns trends</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Last updated</p>
                    <p class="text-lg font-semibold text-gray-800">
                        <?php echo date('M d, Y H:i'); ?>
                    </p>
                </div>
            </div>

            <div class="p-8">
                <!-- Time-based Metrics -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Complaint Trends</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="metric-card bg-blue-50 border-l-4 border-blue-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-600 font-medium">Today</p>
                                    <p class="text-2xl font-bold text-blue-900">
                                        <?php echo number_format($analyticsData['today']); ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-day text-blue-600"></i>
                                </div>
                            </div>
                        </div>

                        <div class="metric-card bg-green-50 border-l-4 border-green-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-green-600 font-medium">Last 7 Days</p>
                                    <p class="text-2xl font-bold text-green-900">
                                        <?php echo number_format($analyticsData['last_7_days']); ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-week text-green-600"></i>
                                </div>
                            </div>
                        </div>

                        <div class="metric-card bg-yellow-50 border-l-4 border-yellow-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-yellow-600 font-medium">Last Month</p>
                                    <p class="text-2xl font-bold text-yellow-900">
                                        <?php echo number_format($analyticsData['last_month']); ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-yellow-600"></i>
                                </div>
                            </div>
                        </div>

                        <div class="metric-card bg-purple-50 border-l-4 border-purple-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-purple-600 font-medium">Last 3 Months</p>
                                    <p class="text-2xl font-bold text-purple-900">
                                        <?php echo number_format($analyticsData['last_3_months']); ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-purple-600"></i>
                                </div>
                            </div>
                        </div>

                        <div class="metric-card bg-red-50 border-l-4 border-red-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-red-600 font-medium">Last Year</p>
                                    <p class="text-2xl font-bold text-red-900">
                                        <?php echo number_format($analyticsData['last_year']); ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-year text-red-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Complaint Type Distribution -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Complaint Type Distribution</h3>
                        <div class="h-64">
                            <canvas id="complaintTypeChart"></canvas>
                        </div>
                    </div>

                    <!-- Monthly Trends -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Trends</h3>
                        <div class="h-64">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Peak Months Table -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Peak Months Analysis</h3>
                    <?php if (!empty($analyticsData['peak_months'])): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Complaints</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $rank = 1; ?>
                                    <?php foreach ($analyticsData['peak_months'] as $month): ?>
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($month['month_name']); ?>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($month['year']); ?>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo number_format($month['total_complaints']); ?>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                #<?php echo $rank++; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No data available for peak months analysis</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Complaint Type Distribution Chart
        const typeDistributionData = <?php echo json_encode($analyticsData['type_distribution']); ?>;
        const typeLabels = typeDistributionData.map(item => item.type);
        const typeData = typeDistributionData.map(item => parseFloat(item.count));
        const typeColors = [
            '#800000', '#a52a2a', '#d2691e', '#daa520', '#32cd32',
            '#00ced1', '#4169e1', '#9932cc', '#ff69b4', '#dc143c'
        ];

        const complaintTypeCtx = document.getElementById('complaintTypeChart').getContext('2d');
        new Chart(complaintTypeCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: typeColors.slice(0, typeLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Monthly Trends Chart
        const monthlyTrendsData = <?php echo json_encode($analyticsData['monthly_trends']); ?>;
        const monthlyLabels = [...new Set(monthlyTrendsData.map(item => `${item.year}-${item.month.toString().padStart(2, '0')}`))].slice(-12);
        const monthlyDatasets = {};

        // Group by complaint type
        const types = [...new Set(monthlyTrendsData.map(item => item.type))];
        types.forEach((type, index) => {
            monthlyDatasets[type] = monthlyLabels.map(label => {
                const item = monthlyTrendsData.find(d => `${d.year}-${d.month.toString().padStart(2, '0')}` === label && d.type === type);
                return item ? parseInt(item.complaint_count) : 0;
            });
        });

        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels.map(label => {
                    const [year, month] = label.split('-');
                    return new Date(year, month - 1).toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
                }),
                datasets: types.slice(0, 5).map((type, index) => ({
                    label: type,
                    data: monthlyDatasets[type],
                    borderColor: typeColors[index],
                    backgroundColor: typeColors[index] + '20',
                    tension: 0.4,
                    fill: false
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: types.length <= 5,
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>