<?php
session_start();
require_once '../logic/db_connection.php';
require_once '../logic/sql_querries.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get all lost items with student information
try {
    $stmt = $pdo->prepare("
        SELECT li.*, 
               s.first_name, 
               s.last_name, 
               s.grade_level, 
               s.section,
               s.phone_number as student_phone
        FROM lost_items li
        JOIN students s ON li.student_id = s.id
        ORDER BY 
            CASE li.status
                WHEN 'pending' THEN 1
                WHEN 'found' THEN 2
                WHEN 'claimed' THEN 3
            END,
            li.date DESC, 
            li.time DESC
    ");
    $stmt->execute();
    $allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Separate by status
    $pendingItems = array_filter($allItems, fn($item) => $item['status'] === 'pending');
    $foundItems = array_filter($allItems, fn($item) => $item['status'] === 'found');
    $claimedItems = array_filter($allItems, fn($item) => $item['status'] === 'claimed');
    
    // Get claimable items (found items not yet claimed)
    $stmt = $pdo->prepare(SQL_GET_CLAIMABLE_ITEMS);
    $stmt->execute();
    $claimableItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching lost items: " . $e->getMessage());
    $allItems = $pendingItems = $foundItems = $claimedItems = $claimableItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Items Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .tab-button {
            padding: 0.625rem 1.25rem;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            color: #64748b;
            white-space: nowrap;
        }

        .tab-button.active {
            color: #800000;
            border-bottom-color: #800000;
        }

        .tab-button:hover {
            color: #800000;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .status-badge {
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-found {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-claimed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .item-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .item-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
            border-color: #d1d5db;
        }

        .image-placeholder {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card {
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navigation-admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-6 sm:py-8 lg:ml-[260px] transition-all duration-300" id="mainContent">
        <!-- Header -->
        <div class="mb-5">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Lost Items Management</h1>
            <p class="text-sm text-gray-600">View and manage all reported lost items</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div class="stat-card bg-yellow-50 border border-yellow-200 rounded-lg p-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-yellow-700 font-medium mb-0.5">Pending</p>
                        <p class="text-2xl font-bold text-yellow-900"><?= count($pendingItems) ?></p>
                    </div>
                    <div class="p-2.5 bg-yellow-100 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-blue-50 border border-blue-200 rounded-lg p-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-blue-700 font-medium mb-0.5">Found</p>
                        <p class="text-2xl font-bold text-blue-900"><?= count($foundItems) ?></p>
                    </div>
                    <div class="p-2.5 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-green-50 border border-green-200 rounded-lg p-3.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-700 font-medium mb-0.5">Claimed</p>
                        <p class="text-2xl font-bold text-green-900"><?= count($claimedItems) ?></p>
                    </div>
                    <div class="p-2.5 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs with Filter Button -->
        <div class="bg-white border-b border-gray-200 rounded-t-lg mb-5">
            <div class="flex items-center justify-between px-4 py-2">
                <div class="flex overflow-x-auto">
                    <button class="tab-button active" data-tab="pending">
                        <i class="fas fa-clock mr-1.5"></i>Pending (<?= count($pendingItems) ?>)
                    </button>
                    <button class="tab-button" data-tab="claimable">
                        <i class="fas fa-hand-holding mr-1.5"></i>Claim (<?= count($claimableItems) ?>)
                    </button>
                    <button class="tab-button" data-tab="claimed">
                        <i class="fas fa-check-circle mr-1.5"></i>Claimed (<?= count($claimedItems) ?>)
                    </button>
                </div>
                <button id="toggleFilterBtn" class="inline-flex items-center px-3 py-1.5 bg-gray-50 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-100 transition-colors ml-4">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
            </div>
        </div>

        <!-- Filter Panel (Hidden by default) -->
        <div id="filterPanel" class="hidden bg-white border border-gray-200 rounded-lg p-4 mb-5 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Search</label>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search by item name, student, or ID..."
                            class="w-full pl-9 pr-9 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clearSearch" class="absolute right-3 top-2.5 hidden text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Category</label>
                    <select id="categoryFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        <option value="">All Categories</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="accessories">Accessories</option>
                        <option value="books">Books</option>
                        <option value="id">ID/Cards</option>
                        <option value="bag">Bag</option>
                        <option value="others">Others</option>
                    </select>
                </div>

                <!-- Grade Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Grade Level</label>
                    <select id="gradeFilter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                        <option value="">All Grades</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                <button id="clearFilters" class="text-sm text-gray-600 hover:text-[#800000] font-medium">
                    Clear All Filters
                </button>
                <div class="text-xs text-gray-500">
                    <span id="resultCount">Showing all items</span>
                </div>
            </div>
        </div>

        <!-- Pending Items Tab -->
        <div id="pending" class="tab-content active">
            <?php if (empty($pendingItems)): ?>
                <div class="bg-white rounded-lg p-12 text-center">
                    <p class="text-gray-500">No pending items</p>
                </div>
            <?php else: ?>
                <?php renderItemsList($pendingItems); ?>
            <?php endif; ?>
        </div>

        <!-- Found Items Tab -->
        <div id="found" class="tab-content">
            <?php if (empty($foundItems)): ?>
                <div class="bg-white rounded-lg p-12 text-center">
                    <p class="text-gray-500">No found items</p>
                </div>
            <?php else: ?>
                <?php renderItemsList($foundItems); ?>
            <?php endif; ?>
        </div>

        <!-- Claimable Items Tab (with claiming functionality) -->
        <div id="claimable" class="tab-content">
            <?php if (empty($claimableItems)): ?>
                <div class="bg-white rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Items Ready for Claim</h3>
                    <p class="text-gray-500">There are currently no items available for claiming.</p>
                </div>
            <?php else: ?>
                <?php renderClaimableItems($claimableItems); ?>
            <?php endif; ?>
        </div>

        <!-- Claimed Items Tab -->
        <div id="claimed" class="tab-content">
            <?php if (empty($claimedItems)): ?>
                <div class="bg-white rounded-lg p-12 text-center">
                    <p class="text-gray-500">No claimed items</p>
                </div>
            <?php else: ?>
                <?php renderItemsList($claimedItems); ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center" onclick="closeImageModal()">
        <span class="absolute top-4 right-4 text-white text-3xl cursor-pointer">&times;</span>
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full object-contain">
    </div>

    <!-- Claimant Photo Modal -->
    <div id="claimantPhotoModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="claimantPhotoTitle">Claimant Photo</h3>
                <button onclick="closeClaimantPhotoModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="text-center">
                <div id="claimantPhotoContainer" class="mb-4">
                    <img id="claimantPhotoImage" src="" alt="Claimant photo" class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg">
                </div>
                <p class="text-sm text-gray-600" id="claimantPhotoInfo">Loading photo...</p>
            </div>
        </div>
    </div>

    <script>
        let cameraStream = null;
        let searchTimeout = null;

        // Toggle filter panel
        document.getElementById('toggleFilterBtn').addEventListener('click', function() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('hidden');
        });

        // Debounced search function
        function debounceSearch(func, delay) {
            return function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(func, delay);
            };
        }

        // Filter items function
        function filterItems() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value.toLowerCase();
            const grade = document.getElementById('gradeFilter').value;

            // Get all item cards in the active tab
            const activeTab = document.querySelector('.tab-content.active');
            const items = activeTab.querySelectorAll('.item-card');
            
            let visibleCount = 0;

            items.forEach(item => {
                const itemName = item.querySelector('h3')?.textContent.toLowerCase() || '';
                const itemCategory = item.querySelector('.text-xs.text-gray-600')?.textContent.toLowerCase() || '';
                const studentName = item.querySelector('.text-gray-900.font-medium')?.textContent.toLowerCase() || '';
                const itemId = item.querySelector('.text-xs.text-gray-600')?.textContent || '';
                const gradeText = item.textContent;
                
                let show = true;

                // Search filter
                if (searchTerm && !(itemName.includes(searchTerm) || studentName.includes(searchTerm) || itemId.includes(searchTerm))) {
                    show = false;
                }

                // Category filter
                if (category && !itemCategory.includes(category)) {
                    show = false;
                }

                // Grade filter
                if (grade && !gradeText.includes('G' + grade + '-') && !gradeText.includes('Grade ' + grade)) {
                    show = false;
                }

                item.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            // Update result count
            const resultCount = document.getElementById('resultCount');
            resultCount.textContent = `Showing ${visibleCount} of ${items.length} items`;

            // Show/hide clear search button
            const clearBtn = document.getElementById('clearSearch');
            clearBtn.style.display = searchTerm ? 'block' : 'none';
        }

        // Add event listeners
        document.getElementById('searchInput').addEventListener('input', debounceSearch(filterItems, 300));
        document.getElementById('categoryFilter').addEventListener('change', filterItems);
        document.getElementById('gradeFilter').addEventListener('change', filterItems);

        // Clear search
        document.getElementById('clearSearch').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            filterItems();
        });

        // Clear all filters
        document.getElementById('clearFilters').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            filterItems();
        });

        // Re-apply filters when switching tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(filterItems, 50); // Small delay to ensure tab content is visible
            });
        });
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                // Add active to clicked
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });

        // Handle sidebar state changes
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            // Function to update main content margin
            function updateMainContentMargin() {
                if (!mainContent) return; // Add null check
                
                if (window.innerWidth >= 1025) {
                    if (sidebar && sidebar.classList.contains('collapsed')) {
                        mainContent.style.marginLeft = '70px';
                    } else {
                        mainContent.style.marginLeft = '260px';
                    }
                } else {
                    mainContent.style.marginLeft = '0';
                }
            }

            // Initial update
            updateMainContentMargin();

            // Listen for sidebar toggle
            const toggleBtn = document.getElementById('toggleSidebar');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    setTimeout(updateMainContentMargin, 50);
                });
            }

            // Listen for window resize
            window.addEventListener('resize', updateMainContentMargin);
        });

        // Mark item as found
        function markAsFound(itemId) {
            if (!confirm('Mark this item as found and notify the student?')) {
                return;
            }

            fetch('notify_student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'item_id=' + itemId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item marked as found and student notified!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        }

        // Claiming functions
        function claimItem(itemId, studentsId, itemName) {
            openClaimModal(itemId, studentsId, itemName);
        }

        function openClaimModal(itemId, studentsId, itemName) {
            const modalHTML = `
                <div id="claimModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Claim Item: ${itemName}</h3>
                            <button onclick="closeClaimModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="mb-4">
                            <p class="text-gray-600 mb-4">Take a photo as evidence of claiming this item.</p>
                            <div class="relative mb-4">
                                <video id="cameraFeed" class="w-full h-64 bg-gray-200 rounded-lg" autoplay playsinline></video>
                                <canvas id="photoCanvas" class="hidden w-full h-64 bg-gray-200 rounded-lg"></canvas>
                                <div id="cameraError" class="hidden mt-2 p-2 bg-red-100 text-red-700 rounded text-sm">
                                    Camera access denied or not available
                                </div>
                            </div>
                            <div class="flex gap-2 mb-4">
                                <button id="captureBtn" onclick="capturePhoto()"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-camera mr-2"></i>Take Photo
                                </button>
                                <button id="retakeBtn" onclick="retakePhoto()" class="hidden flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                    Retake
                                </button>
                            </div>
                            <div id="photoPreview" class="hidden mb-4">
                                <p class="text-sm text-gray-600 mb-2">Photo Preview:</p>
                                <img id="previewImage" class="w-full h-32 object-cover rounded-lg border">
                            </div>
                            <div class="flex gap-2">
                                <button id="cancelClaimBtn" onclick="closeClaimModal()"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                                    Cancel
                                </button>
                                <button id="confirmClaimBtn" onclick="confirmClaim(${itemId}, ${studentsId})" disabled
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                    Confirm Claim
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            initializeCamera();
        }

        function closeClaimModal() {
            const modal = document.getElementById('claimModal');
            if (modal) modal.remove();
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        }

        async function initializeCamera() {
            try {
                const video = document.getElementById('cameraFeed');
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                cameraStream = stream;
                video.srcObject = stream;
            } catch (error) {
                console.error('Camera access error:', error);
                document.getElementById('cameraError').classList.remove('hidden');
                document.getElementById('captureBtn').disabled = true;
            }
        }

        function capturePhoto() {
            const video = document.getElementById('cameraFeed');
            const canvas = document.getElementById('photoCanvas');
            const preview = document.getElementById('previewImage');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            const imageData = canvas.toDataURL('image/jpeg');
            preview.src = imageData;
            document.getElementById('cameraFeed').classList.add('hidden');
            document.getElementById('photoCanvas').classList.remove('hidden');
            document.getElementById('photoPreview').classList.remove('hidden');
            document.getElementById('captureBtn').classList.add('hidden');
            document.getElementById('retakeBtn').classList.remove('hidden');
            document.getElementById('confirmClaimBtn').disabled = false;
            window.capturedPhoto = imageData;
        }

        function retakePhoto() {
            document.getElementById('cameraFeed').classList.remove('hidden');
            document.getElementById('photoCanvas').classList.add('hidden');
            document.getElementById('photoPreview').classList.add('hidden');
            document.getElementById('captureBtn').classList.remove('hidden');
            document.getElementById('retakeBtn').classList.add('hidden');
            document.getElementById('confirmClaimBtn').disabled = true;
        }

        function confirmClaim(itemId, studentsId) {
            if (!window.capturedPhoto) {
                alert('Please take a photo first');
                return;
            }
            const confirmBtn = document.getElementById('confirmClaimBtn');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            confirmBtn.disabled = true;
            const formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('students_id', studentsId);
            formData.append('claimant_photo_data', window.capturedPhoto);
            formData.append('claim_evidence', 'Item claimed with photo evidence');
            fetch('../logic/claim_item_logic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Item claimed successfully!');
                    closeClaimModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    confirmBtn.innerHTML = originalText;
                    confirmBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while claiming the item');
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            });
        }

        function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modalImg.src = imageSrc;
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function viewClaimantPhoto(itemId, itemName) {
            document.getElementById('claimantPhotoTitle').textContent = `Claimant Photo - ${itemName}`;
            document.getElementById('claimantPhotoInfo').textContent = 'Loading photo...';
            document.getElementById('claimantPhotoImage').src = '';
            document.getElementById('claimantPhotoModal').classList.remove('hidden');
            fetch('get_claimant_photo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'item_id=' + itemId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.photo && data.mime_type) {
                    const photoSrc = `data:${data.mime_type};base64,${data.photo}`;
                    document.getElementById('claimantPhotoImage').src = photoSrc;
                    const claimDate = new Date(data.claimed_at);
                    document.getElementById('claimantPhotoInfo').textContent =
                        `Claimed by: ${data.claimed_by_first_name} ${data.claimed_by_last_name} on ${claimDate.toLocaleDateString()} at ${claimDate.toLocaleTimeString()}`;
                } else {
                    document.getElementById('claimantPhotoInfo').textContent = 'No photo available or error loading photo.';
                    document.getElementById('claimantPhotoImage').src = '';
                }
            })
            .catch(error => {
                console.error('Error loading claimant photo:', error);
                document.getElementById('claimantPhotoInfo').textContent = 'Error loading photo. Please try again.';
                document.getElementById('claimantPhotoImage').src = '';
            });
        }

        function closeClaimantPhotoModal() {
            document.getElementById('claimantPhotoModal').classList.add('hidden');
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeImageModal();
                closeClaimantPhotoModal();
            }
        });
    </script>
</body>
</html>

<?php
function renderItemsList($items) {
    ?>
    <div class="space-y-2.5">
        <?php foreach ($items as $item): ?>
            <div class="item-card p-3.5">
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Image with Placeholder -->
                    <div class="flex-shrink-0 mx-auto sm:mx-0">
                        <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                            <img src="data:<?= $item['mime_type'] ?>;base64,<?= base64_encode($item['photo']) ?>"
                                 alt="<?= htmlspecialchars($item['item_name']) ?>"
                                 class="w-20 h-20 sm:w-20 sm:h-20 object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity"
                                 onclick="openImageModal(this.src)">
                        <?php else: ?>
                            <div class="w-20 h-20 sm:w-20 sm:h-20 rounded-lg border border-gray-200 image-placeholder">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1.5 mb-2">
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900 mb-0.5">
                                    <?= htmlspecialchars($item['item_name']) ?>
                                </h3>
                                <p class="text-xs text-gray-600">
                                    <?= htmlspecialchars($item['category']) ?> • ID: <?= $item['id'] ?>
                                </p>
                            </div>
                            <span class="status-badge status-<?= strtolower($item['status']) ?> self-start">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs mb-2.5 pb-2.5 border-b border-gray-100">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-gray-900 font-medium truncate">
                                    <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="text-gray-900 font-medium">
                                    G<?= htmlspecialchars($item['grade_level']) ?>-<?= htmlspecialchars($item['section']) ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-900 font-medium">
                                    <?= date('M j, Y', strtotime($item['date_lost'])) ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-gray-900 font-medium truncate">
                                    <?= htmlspecialchars($item['location']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="view-lost-item.php?id=<?= $item['id'] ?>" 
                               class="inline-flex items-center px-3 py-1.5 bg-[#800000] text-white rounded-md text-xs font-medium hover:bg-[#600000] transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
                            </a>
                            <?php if ($item['status'] === 'pending'): ?>
                            <button onclick="markAsFound(<?= $item['id'] ?>)"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs font-medium hover:bg-blue-700 transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Mark Found
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function renderClaimableItems($items) {
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
        <?php foreach ($items as $item): ?>
            <div class="item-card overflow-hidden">
                <!-- Image with Placeholder -->
                <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                    <div class="relative h-44 bg-gray-100">
                        <img src="data:<?= $item['mime_type'] ?>;base64,<?= base64_encode($item['photo']) ?>"
                             alt="<?= htmlspecialchars($item['item_name']) ?>"
                             onclick="openImageModal(this.src)"
                             class="w-full h-full object-contain cursor-pointer hover:opacity-90 transition-opacity">
                        <div class="absolute top-2 right-2">
                            <span class="status-badge status-found">
                                <i class="fas fa-check-circle mr-1"></i>Found
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="relative h-44 image-placeholder">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div class="absolute top-2 right-2">
                            <span class="status-badge status-found">
                                <i class="fas fa-check-circle mr-1"></i>Found
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="p-3.5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-0.5 truncate">
                        <?= htmlspecialchars($item['item_name']) ?>
                    </h3>
                    <p class="text-xs text-gray-600 mb-3">
                        <i class="fas fa-tag mr-1"></i><?= htmlspecialchars($item['category']) ?>
                    </p>

                    <div class="space-y-1.5 text-xs mb-3.5 pb-3.5 border-b border-gray-100">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-user w-4 mr-2 text-gray-400"></i>
                            <span class="truncate"><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt w-4 mr-2 text-gray-400"></i>
                            <span class="truncate"><?= htmlspecialchars($item['location'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar w-4 mr-2 text-gray-400"></i>
                            <span><?= date('M j, Y', strtotime($item['date'])) ?></span>
                        </div>
                    </div>

                    <?php if (empty($item['claimed_by_student_id'])): ?>
                        <button onclick="claimItem(<?= (int)$item['id'] ?>, <?= (int)$item['student_id'] ?>, '<?= addslashes($item['item_name']) ?>')"
                                class="w-full bg-[#800000] hover:bg-[#600000] text-white px-3 py-2 rounded-md text-xs font-medium flex items-center justify-center transition-colors">
                            <i class="fas fa-camera mr-1.5"></i>Claim Item
                        </button>
                    <?php else: ?>
                        <div class="bg-green-100 text-green-800 px-3 py-2 rounded-md text-xs text-center font-medium">
                            <i class="fas fa-check-circle mr-1"></i>Already Claimed
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>
