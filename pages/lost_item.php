<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])) {
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
}
$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare(SQL_LIST_LOST_ITEMS_BY_STUDENT);
$stmt->execute([$student_id]);
$lost_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$foundItems = [];
try {
    // Get all found items
    $stmt = $pdo->prepare("
        SELECT * from lost_items where student_id = ? AND status = 'found'
    ");
    $stmt->execute([$student_id]);
    $foundItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching found items: " . $e->getMessage());
    $foundItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Items - EMEMHS Guidance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Mobile-first card design */
        .item-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            border: 1px solid #f1f5f9;
        }

        .item-card:active {
            transform: scale(0.98);
        }

        @media (min-width: 768px) {
            .item-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.15);
            }
        }

        /* Mobile-optimized buttons */
        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #600000 100%);
            transition: all 0.2s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        @media (min-width: 768px) {
            .btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
            }
        }

        .btn-secondary {
            background: #6b7280;
            transition: all 0.2s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:active {
            transform: scale(0.98);
        }

        @media (min-width: 768px) {
            .btn-secondary:hover {
                background: #4b5563;
                transform: translateY(-1px);
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

        .status-found {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* Mobile-optimized filters */
        .filter-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
        }

        /* Touch-friendly elements */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        /* Mobile table alternative */
        .mobile-item {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #f1f5f9;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        /* Image modal */
        .image-modal {
            backdrop-filter: blur(4px);
        }

        /* Responsive spacing */
        .mobile-section {
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .mobile-section {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="px-4 py-4 sm:py-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Mobile-First Header -->
        <div class="mobile-section">
            <div class="text-center sm:text-left mb-4">
                <h1 class="text-xl sm:text-2xl lg:text-2xl font-bold text-gray-900 mb-2">
                    Lost Items
                </h1>
                <p class="text-xs sm:text-base text-gray-600">
                    Track your lost items and view recovered items
                </p>
            </div>
            <div class="flex justify-end">
                <a href="lost-item-form.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold text-center hidden lg:block">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Report Lost Item
                </a>
            </div>

            <!-- Mobile-optimized action button -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="lost-item-form.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold text-center lg:hidden">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Report Lost Item
                </a>
                <button id="toggleView" class="btn-secondary text-white px-6 py-3 rounded-lg font-semibold text-center sm:hidden">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    List View
                </button>
            </div>
        </div>

        <!-- Mobile-First Filter Section -->
        <div class="filter-container p-4 mobile-section">
            <!-- Search Bar -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search lost items..."
                        class="w-full pl-10 pr-10 py-3 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20">
                    <svg class="absolute left-3 top-3.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <button id="clearSearch" class="absolute right-3 top-3.5 hidden text-gray-400 hover:text-[#800000] touch-target">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Expandable Filters -->
            <div class="space-y-3">
                <button id="toggleFilters" class="flex items-center justify-between w-full text-left text-sm font-medium text-gray-700 hover:text-[#800000]">
                    <span>Advanced Filters</span>
                    <svg id="filterChevron" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="advancedFilters" class="hidden space-y-4">
                    <!-- Date Range -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" id="startDate" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#800000]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" id="endDate" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#800000]">
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Sort By</label>
                        <div class="flex flex-wrap gap-2">
                            <button id="sortName" class="px-3 py-2 text-xs font-medium border border-gray-200 rounded-lg hover:border-[#800000] hover:text-[#800000] transition-colors">
                                Name
                            </button>
                            <button id="sortDate" class="px-3 py-2 text-xs font-medium border border-gray-200 rounded-lg hover:border-[#800000] hover:text-[#800000] transition-colors">
                                Date
                            </button>
                            <button id="sortStatus" class="px-3 py-2 text-xs font-medium border border-gray-200 rounded-lg hover:border-[#800000] hover:text-[#800000] transition-colors">
                                Status
                            </button>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <button id="clearAllFilters" class="w-full sm:w-auto px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Clear All Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile-First Items Display -->
        <div class="mobile-section">
            <!-- Mobile Card View (Default) -->
            <div id="cardView" class="space-y-4">
                <?php if (!empty($lost_items)): ?>
                    <?php foreach ($lost_items as $item): ?>
                        <div class="mobile-item" data-item-name="<?= strtolower($item['item_name']) ?>" data-date="<?= $item['date'] ?>" data-status="<?= strtolower($item['status']) ?>">
                            <div class="flex p-4">
                                <!-- Image -->
                                <div class="flex-shrink-0 mr-4">
                                    <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                        <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                                            alt="<?= htmlspecialchars($item['item_name']) ?>"
                                            class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg cursor-pointer"
                                            onclick="openImageView(this)" />
                                    <?php else: ?>
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-2">
                                        <div class="flex-1">
                                            <h3 class="text-base font-semibold text-gray-900 mb-1">
                                                <?= htmlspecialchars($item['item_name']) ?>
                                            </h3>
                                            <p class="text-sm text-gray-600 mb-2">
                                                <?= htmlspecialchars($item['category']) ?>
                                            </p>
                                        </div>
                                        <span class="status-badge status-<?= strtolower($item['status']) ?> self-start mb-2 sm:mb-0">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                            <?= htmlspecialchars($item['description']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center text-xs text-gray-500 mb-3 sm:mb-0">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <?= date('M d, Y', strtotime($item['date'])) ?>
                                        </div>

                                        <form action="lost-item-form.php" method="POST" class="inline">
                                            <input type="hidden" name="user" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg text-sm font-medium">
                                                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Lost Items</h3>
                        <p class="text-gray-500 mb-4">You haven't reported any lost items yet.</p>
                        <a href="lost-item-form.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Report Your First Item
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Desktop Table View (Hidden on Mobile) -->
            <div id="tableView" class="hidden lg:block item-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="lostItemsTableBody">
                            <?php if (!empty($lost_items)): ?>
                                <?php foreach ($lost_items as $item): ?>
                                    <tr class="hover:bg-gray-50" data-item-name="<?= strtolower($item['item_name']) ?>" data-date="<?= $item['date'] ?>" data-status="<?= strtolower($item['status']) ?>">
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['item_name']) ?></div>
                                                <div class="text-sm text-gray-500">ID: <?= $item['id'] ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($item['category']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-<?= strtolower($item['status']) ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y', strtotime($item['date'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                                <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                                                    alt="Item Photo"
                                                    class="w-12 h-12 object-cover rounded-lg cursor-pointer"
                                                    onclick="openImageView(this)" />
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form action="lost-item-form.php" method="POST" class="inline">
                                                <input type="hidden" name="user" value="<?= $item['id'] ?>">
                                                <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg text-sm">
                                                    Edit
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Found Items Section -->
        <div class="mobile-section">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-8 w-1 bg-[#800000] rounded-full"></div>
                <div>
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Found Items ✅</h2>
                    <p class="text-sm text-gray-600">Items recovered and ready for collection</p>
                </div>
            </div>
        </div>

        <?php if (empty($foundItems)): ?>
            <div class="item-card p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-50 flex items-center justify-center">
                    <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Found Items Yet</h3>
                <p class="text-sm text-gray-600">When your lost items are found, they'll appear here for collection.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($foundItems as $item): ?>
                    <div class="item-card overflow-hidden group">
                        <div class="relative aspect-[4/3] bg-gray-50 cursor-pointer" onclick="openImageView(this)">
                            <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                                    alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                    class="w-full h-full object-cover transition-transform duration-300" />
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Found ✅
                                </span>
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                <svg class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($item['category']); ?>
                                    </p>
                                </div>
                                <span class="text-xs text-gray-500 ml-2">
                                    <?php echo date('M d', strtotime($item['date'])); ?>
                                </span>
                            </div>

                            <?php if (!empty($item['description'])): ?>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate">
                                        <?php echo htmlspecialchars($item['location'] ?? 'Guidance Office'); ?>
                                    </span>
                                </div>

                                <div class="text-xs font-medium text-green-600">
                                    Ready for pickup
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Mobile-Optimized Image Modal -->
        <div id="imageViewModal" class="fixed inset-0 bg-black/95 z-50 hidden items-center justify-center image-modal">
            <button onclick="closeImageView()" class="absolute top-4 right-4 z-10 p-2 text-white hover:text-gray-300 touch-target">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="w-full h-full flex items-center justify-center p-4">
                <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full object-contain" />
            </div>
            <div class="absolute bottom-4 left-4 right-4 text-center">
                <p class="text-white text-sm opacity-75">Tap outside image to close</p>
            </div>
        </div>
    </main>

    <script>
        // Mobile-first filtering and sorting
        let currentSort = {
            column: null,
            direction: 'asc'
        };
        let isCardView = true;

        // Toggle between card and table view (mobile only)
        function toggleView() {
            const cardView = document.getElementById('cardView');
            const tableView = document.getElementById('tableView');
            const toggleBtn = document.getElementById('toggleView');

            if (isCardView) {
                cardView.classList.add('hidden');
                tableView.classList.remove('hidden');
                toggleBtn.innerHTML = `
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Card View
                `;
                isCardView = false;
            } else {
                cardView.classList.remove('hidden');
                tableView.classList.add('hidden');
                toggleBtn.innerHTML = `
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    List View
                `;
                isCardView = true;
            }
        }

        // Toggle advanced filters
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advancedFilters');
            const chevron = document.getElementById('filterChevron');

            if (filters.classList.contains('hidden')) {
                filters.classList.remove('hidden');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                filters.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Filter and sort items
        function filterAndSortItems() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            // Get all items (both card and table view)
            const cardItems = document.querySelectorAll('#cardView .mobile-item');
            const tableRows = document.querySelectorAll('#tableView tbody tr');

            // Filter card view items
            cardItems.forEach(item => {
                const itemName = item.dataset.itemName || '';
                const itemDate = item.dataset.date || '';
                let show = true;

                if (searchTerm && !itemName.includes(searchTerm)) show = false;
                if (startDate && itemDate < startDate) show = false;
                if (endDate && itemDate > endDate) show = false;

                item.style.display = show ? 'block' : 'none';
            });

            // Filter table view items
            tableRows.forEach(row => {
                if (!row.dataset.itemName) return; // Skip header/empty rows

                const itemName = row.dataset.itemName || '';
                const itemDate = row.dataset.date || '';
                let show = true;

                if (searchTerm && !itemName.includes(searchTerm)) show = false;
                if (startDate && itemDate < startDate) show = false;
                if (endDate && itemDate > endDate) show = false;

                row.style.display = show ? '' : 'none';
            });

            // Update clear search button
            const clearSearch = document.getElementById('clearSearch');
            clearSearch.style.display = searchTerm ? 'block' : 'none';
        }

        // Sort items
        function sortItems(column) {
            const isActive = currentSort.column === column;
            currentSort.column = column;
            currentSort.direction = isActive ? (currentSort.direction === 'asc' ? 'desc' : 'asc') : 'asc';

            // Update sort button states
            document.querySelectorAll('[id^="sort"]').forEach(btn => {
                btn.classList.remove('border-[#800000]', 'text-[#800000]', 'bg-[#800000]/5');
                btn.classList.add('border-gray-200', 'text-gray-600');
            });

            const activeBtn = document.getElementById(`sort${column.charAt(0).toUpperCase() + column.slice(1)}`);
            if (activeBtn) {
                activeBtn.classList.add('border-[#800000]', 'text-[#800000]', 'bg-[#800000]/5');
                activeBtn.classList.remove('border-gray-200', 'text-gray-600');
            }

            // Sort logic would go here - for now just visual feedback
            filterAndSortItems();
        }

        // Clear all filters
        function clearAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            currentSort = {
                column: null,
                direction: 'asc'
            };

            // Reset sort button states
            document.querySelectorAll('[id^="sort"]').forEach(btn => {
                btn.classList.remove('border-[#800000]', 'text-[#800000]', 'bg-[#800000]/5');
                btn.classList.add('border-gray-200', 'text-gray-600');
            });

            filterAndSortItems();
        }

        // Image modal functions
        function openImageView(element) {
            const modal = document.getElementById('imageViewModal');
            const modalImage = document.getElementById('modalImage');
            const image = element.tagName === 'IMG' ? element : element.querySelector('img');

            if (image && image.src) {
                modalImage.src = image.src;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImageView() {
            const modal = document.getElementById('imageViewModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Search input
            document.getElementById('searchInput').addEventListener('input', filterAndSortItems);

            // Date inputs
            document.getElementById('startDate').addEventListener('change', filterAndSortItems);
            document.getElementById('endDate').addEventListener('change', filterAndSortItems);

            // Clear search
            document.getElementById('clearSearch').addEventListener('click', () => {
                document.getElementById('searchInput').value = '';
                filterAndSortItems();
            });

            // Toggle filters
            document.getElementById('toggleFilters').addEventListener('click', toggleAdvancedFilters);

            // Sort buttons
            document.getElementById('sortName').addEventListener('click', () => sortItems('name'));
            document.getElementById('sortDate').addEventListener('click', () => sortItems('date'));
            document.getElementById('sortStatus').addEventListener('click', () => sortItems('status'));

            // Clear all filters
            document.getElementById('clearAllFilters').addEventListener('click', clearAllFilters);

            // Toggle view (mobile only)
            const toggleBtn = document.getElementById('toggleView');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleView);
            }

            // Modal controls
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeImageView();
            });

            document.getElementById('imageViewModal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) closeImageView();
            });
        });
    </script>
</body>

</html>