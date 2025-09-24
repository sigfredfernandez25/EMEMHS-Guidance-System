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

// Check if user is logged in and is admin
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
$foundItems = [];
$claimedItems = [];
try {
    // Get all found items (claimable)
    $stmt = $pdo->prepare(SQL_GET_CLAIMABLE_ITEMS);
    $stmt->execute();
    $foundItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all claimed items
    $stmt = $pdo->prepare(SQL_GET_CLAIMED_ITEMS);
    $stmt->execute();
    $claimedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching found items: " . $e->getMessage());
    $foundItems = [];
    $claimedItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Items - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        .header-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, #a52a2a 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-found {
            background-color: #dcfce7;
            color: #166534;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, #8b0000 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }

        .image-container {
            position: relative;
            width: 100%;
            height: 200px;
            background-color: #f3f4f6;
            overflow: hidden;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            cursor: pointer;
        }

        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            cursor: pointer;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90vh;
            margin: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: contain;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 1001;
        }

        .compact-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .compact-card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .compact-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.125rem;
        }

        .info-value {
            font-size: 0.875rem;
            color: #111827;
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
    </style>
</head>

<body class="min-h-screen bg-white">
    <?php include 'navigation-admin.php'; ?>
    <div class="pt-5 main-content">
        <main class="min-h-screen">
            <!-- Welcome Section -->
            <div class="mb-4 flex justify-between items-center px-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Found Items Management</h1>
                    <p class="text-gray-600">View and manage all found items in the system</p>
                </div>
            </div>

            <div class="p-8">
                <!-- Tabs for Found Items and Claimed Items -->
                <div class="tabs-container mb-6">
                    <div class="tabs-header">
                        <button class="tab-button active" data-tab="claimable">
                            <i class="fas fa-search"></i>
                            Available for Claim (<?= count($foundItems) ?>)
                        </button>
                        <button class="tab-button" data-tab="claimed">
                            <i class="fas fa-check-circle"></i>
                            Already Claimed (<?= count($claimedItems) ?>)
                        </button>
                    </div>
                </div>

                <!-- Claimable Items Tab -->
                <div id="claimable" class="tab-content active">
                    <?php if (empty($foundItems)): ?>
                        <div class="minimal-card p-8 text-center">
                            <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                                <i class="fas fa-box-open text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Items Available for Claim</h3>
                            <p class="text-gray-500">There are currently no items available for claiming.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($foundItems as $item): ?>
                                <div class="minimal-card overflow-hidden card-hover compact-card">
                                    <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                        <div class="image-container">
                                            <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                                                alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                onclick="openImageModal(this.src)" />
                                            <div class="absolute top-2 right-2">
                                                <span class="status-badge status-found">
                                                    <i class="fas fa-check-circle mr-1"></i> Found
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-6 compact-card-content">
                                        <div class="mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <?php echo htmlspecialchars($item['item_name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?php echo htmlspecialchars($item['category']); ?>
                                            </p>
                                        </div>

                                        <div class="compact-info mb-4">
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-align-left mr-1"></i> Description
                                                </span>
                                                <span class="info-value">
                                                    <?php echo htmlspecialchars($item['description'] ?? 'No description provided'); ?>
                                                </span>
                                            </div>

                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> Location
                                                </span>
                                                <span class="info-value">
                                                    <?php echo htmlspecialchars($item['location'] ?? 'Not specified'); ?>
                                                </span>
                                            </div>

                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-calendar-alt mr-1"></i> Date
                                                </span>
                                                <span class="info-value">
                                                    <?php echo date('F j, Y', strtotime($item['date'])); ?>
                                                </span>
                                            </div>

                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-clock mr-1"></i> Time
                                                </span>
                                                <span class="info-value">
                                                    <?php echo date('g:i A', strtotime($item['time'])); ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($item['student_id'])): ?>
                                                <div class="info-item col-span-2">
                                                    <span class="info-label">
                                                        <i class="fas fa-user mr-1"></i> Original Owner
                                                    </span>
                                                    <span class="info-value">
                                                        <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                                        <span class="text-gray-500 ml-1">
                                                            (ID: <?php echo htmlspecialchars($item['student_id']); ?>)
                                                        </span>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (empty($item['claimed_by_student_id'])): ?>
                                            <div class="mt-auto">
                                                <button onclick="claimItem(<?php echo (int)$item['id']; ?>, <?php echo (int)$item['student_id']; ?>, '<?php echo addslashes($item['item_name']); ?>')"
                                                    class="w-full btn-primary text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center mb-2">
                                                    <i class="fas fa-camera mr-2"></i>
                                                    Claim Item
                                                </button>
                                                <!-- <button onclick="notifyStudent(<?php echo $item['id']; ?>)"
                                                class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium flex items-center justify-center">
                                                <i class="fas fa-bell mr-2"></i>
                                                Notify Original Owner
                                            </button> -->
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-auto">
                                                <div class="bg-green-100 text-green-800 px-3 py-2 rounded-lg text-sm text-center">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Already Claimed
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Claimed Items Tab -->
                <div id="claimed" class="tab-content">
                    <?php if (empty($claimedItems)): ?>
                        <div class="minimal-card p-8 text-center">
                            <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                                <i class="fas fa-check-circle text-4xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Claimed Items</h3>
                            <p class="text-gray-500">No items have been claimed yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($claimedItems as $item): ?>
                                <div class="minimal-card overflow-hidden card-hover compact-card">
                                    <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                        <div class="image-container">
                                            <img src="data:<?= $item['mime_type'] ?>;base64,<?= base64_encode($item['photo']) ?>"
                                                alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                onclick="openImageModal(this.src)" />
                                            <div class="absolute top-2 right-2">
                                                <span class="status-badge status-found">
                                                    <i class="fas fa-check-circle mr-1"></i> Claimed
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-6 compact-card-content">
                                        <div class="mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <?php echo htmlspecialchars($item['item_name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?php echo htmlspecialchars($item['category']); ?>
                                            </p>
                                        </div>

                                        <div class="compact-info mb-4">
                                            <!-- <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-user mr-1"></i> Original Owner
                                                </span>
                                                <span class="info-value">
                                                    <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                                </span>
                                            </div> -->

                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-user-check mr-1"></i> Claimed By
                                                </span>
                                                <span class="info-value">
                                                    <?php echo htmlspecialchars($item['claimed_by_first_name'] . ' ' . $item['claimed_by_last_name']); ?>
                                                </span>
                                            </div>

                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-calendar-check mr-1"></i> Claimed Date
                                                </span>
                                                <span class="info-value">
                                                    <?php echo date('M d, Y g:i A', strtotime($item['claimed_at'])); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-auto">
                                            <div class="flex gap-2 mb-2">
                                                <?php if (!empty($item['claimant_photo']) && !empty($item['claimant_photo_mime_type'])): ?>
                                                    <button onclick="viewClaimantPhoto(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['item_name']); ?>')"
                                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg font-medium flex items-center justify-center text-sm">
                                                        <i class="fas fa-camera mr-1"></i>
                                                        View Claimant Photo
                                                    </button>
                                                <?php else: ?>
                                                    <div class="flex-1 bg-gray-100 text-gray-500 px-3 py-2 rounded-lg font-medium text-center text-sm">
                                                        <i class="fas fa-camera mr-1"></i>
                                                        No Photo Available
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="bg-green-100 text-green-800 px-3 py-2 rounded-lg text-sm text-center">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Successfully Claimed
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Full Screen Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="close-modal">&times;</span>
        <img id="modalImage" src="" alt="Full size image">
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
        // Global variables
        let cameraStream = null;

        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked button and corresponding content
                    button.classList.add('active');
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });

        function notifyStudent(itemId) {
            if (confirm('Are you sure you want to notify the student about this found item?')) {
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
                            alert('Student has been notified successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while notifying the student.');
                    });
            }
        }

        function claimItem(itemId, studentsId, itemName) {
            // Open camera modal
            openClaimModal(itemId, studentsId, itemName);
        }

        function openClaimModal(itemId, studentsId, itemName) {
            // Create modal HTML
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
                            <p class="text-gray-600 mb-4">Please take a photo as evidence that you are claiming this item.</p>

                            <div class="relative mb-4">
                                <video id="cameraFeed" class="w-full h-64 bg-gray-200 rounded-lg" autoplay playsinline></video>
                                <canvas id="photoCanvas" class="hidden w-full h-64 bg-gray-200 rounded-lg"></canvas>
                                <div id="cameraError" class="hidden mt-2 p-2 bg-red-100 text-red-700 rounded">
                                    Camera access denied or not available
                                </div>
                            </div>

                            <div class="flex gap-2 mb-4">
                                <button id="captureBtn" onclick="capturePhoto()"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-camera mr-2"></i>
                                    Take Photo
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

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Initialize camera
            initializeCamera();
        }

        function closeClaimModal() {
            const modal = document.getElementById('claimModal');
            if (modal) {
                modal.remove();
            }
            // Stop camera stream
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        }

        async function initializeCamera() {
            try {
                const video = document.getElementById('cameraFeed');
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    }
                });
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

            // Show preview and hide camera
            document.getElementById('cameraFeed').classList.add('hidden');
            document.getElementById('photoCanvas').classList.remove('hidden');
            document.getElementById('photoPreview').classList.remove('hidden');

            // Update buttons
            document.getElementById('captureBtn').classList.add('hidden');
            document.getElementById('retakeBtn').classList.remove('hidden');
            document.getElementById('confirmClaimBtn').disabled = false;

            // Store photo data
            window.capturedPhoto = imageData;
        }

        function retakePhoto() {
            // Hide preview and show camera
            document.getElementById('cameraFeed').classList.remove('hidden');
            document.getElementById('photoCanvas').classList.add('hidden');
            document.getElementById('photoPreview').classList.add('hidden');

            // Update buttons
            document.getElementById('captureBtn').classList.remove('hidden');
            document.getElementById('retakeBtn').classList.add('hidden');
            document.getElementById('confirmClaimBtn').disabled = true;
        }

        // Image modal functions
        function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = "block";
            modalImg.src = imageSrc;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = "none";
        }

        // Close modals when pressing Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeImageModal();
                closeClaimantPhotoModal();
            }
        });

        function confirmClaim(itemId, studentsId) {
            console.log("itemId", itemId)
            if (!window.capturedPhoto) {
                alert('Please take a photo first');
                return;
            }

            // Show loading
            const confirmBtn = document.getElementById('confirmClaimBtn');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            confirmBtn.disabled = true;

            // Prepare form data
            const formData = new FormData();
            formData.append('item_id', itemId);
            formData.append('students_id', studentsId);
            formData.append('claimant_photo_data', window.capturedPhoto);
            formData.append('claim_evidence', 'Item claimed with photo evidence');

            // Send claim request
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

        function viewClaimantPhoto(itemId, itemName) {
            // Set modal title
            document.getElementById('claimantPhotoTitle').textContent = `Claimant Photo - ${itemName}`;

            // Set loading state
            document.getElementById('claimantPhotoInfo').textContent = 'Loading photo...';
            document.getElementById('claimantPhotoImage').src = '';

            // Show modal
            document.getElementById('claimantPhotoModal').classList.remove('hidden');

            // Fetch claimant photo via AJAX
            fetch('get_claimant_photo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'item_id=' + itemId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.photo && data.mime_type) {
                        // Set photo source
                        const photoSrc = `data:${data.mime_type};base64,${data.photo}`;
                        document.getElementById('claimantPhotoImage').src = photoSrc;

                        // Set photo info
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
    </script>
</body>

</html>