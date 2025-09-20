<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'student') {
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
    exit();
}

$item_id = $_GET['item_id'] ?? null;

if (!$item_id) {
    echo "<script>alert('No item specified for claiming.'); window.location.href = 'student_dashboard.php';</script>";
    exit();
}

// Get item details
$stmt = $pdo->prepare(SQL_GET_LOST_ITEM_CLAIM_DETAILS);
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "<script>alert('Item not found or not available for claiming.'); window.location.href = 'student_dashboard.php';</script>";
    exit();
}

if ($item['status'] !== 'found') {
    echo "<script>alert('Item is not available for claiming.'); window.location.href = 'student_dashboard.php';</script>";
    exit();
}

// Check if item is already claimed
if (!empty($item['claimed_by_student_id'])) {
    echo "<script>alert('Item has already been claimed.'); window.location.href = 'student_dashboard.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item - EMEMHS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .claim-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }

        .camera-container {
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .camera-container.active {
            border-color: #800000;
            background-color: rgba(128, 0, 0, 0.05);
        }

        .preview-image {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .preview-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="claim-container p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-[#800000] mb-4">Claim Lost Item</h1>
                <p class="text-gray-600">Please take a photo as evidence that you are claiming this item</p>
            </div>

            <!-- Item Details -->
            <div class="bg-gray-50 p-6 rounded-lg mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Item Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Item Name</p>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Category</p>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['category']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Location Found</p>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['location']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date Found</p>
                        <p class="font-medium text-gray-900"><?php echo date('M d, Y', strtotime($item['date'])); ?></p>
                    </div>
                </div>
                <?php if (!empty($item['description'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Description</p>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($item['description']); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Camera Section -->
            <div class="camera-container p-6 rounded-lg mb-8" id="cameraContainer">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Take Your Photo</h3>

                <div class="relative mb-4">
                    <video id="cameraFeed" class="w-full h-64 bg-gray-200 rounded-lg" autoplay playsinline></video>
                    <canvas id="photoCanvas" class="hidden w-full h-64 bg-gray-200 rounded-lg"></canvas>
                    <div id="cameraError" class="hidden mt-2 p-3 bg-red-100 text-red-700 rounded-lg">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Camera access denied or not available. Please check your browser permissions.
                    </div>
                </div>

                <div class="flex gap-3 mb-4">
                    <button id="captureBtn" onclick="capturePhoto()"
                        class="flex-1 btn-primary text-white px-6 py-3 rounded-lg font-semibold flex items-center justify-center">
                        <i class="fas fa-camera mr-2"></i>
                        Take Photo
                    </button>
                    <button id="retakeBtn" onclick="retakePhoto()" style="display: none;"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold">
                        Retake Photo
                    </button>
                </div>

                <div id="photoPreview" class="hidden">
                    <p class="text-sm text-gray-600 mb-2">Your Photo:</p>
                    <img id="previewImage" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                </div>
            </div>

            <!-- Claim Form -->
            <form id="claimForm" action="../logic/claim_item_logic.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                <input type="hidden" name="claimant_photo_data" id="claimantPhotoData">
                <input type="hidden" name="claim_evidence" value="Item claimed by student with photo evidence">

                <div class="flex justify-between">
                    <a href="student_dashboard.php"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-semibold">
                        Cancel
                    </a>
                    <button type="submit" id="confirmClaimBtn" disabled
                        class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-check mr-2"></i>
                        Confirm Claim
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        let cameraStream = null;
        let capturedPhoto = null;

        document.addEventListener('DOMContentLoaded', function() {
            initializeCamera();
        });

        async function initializeCamera() {
            try {
                const video = document.getElementById('cameraFeed');
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    }
                });
                cameraStream = stream;
                video.srcObject = stream;

                document.getElementById('cameraContainer').classList.add('active');
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

            const imageData = canvas.toDataURL('image/jpeg', 0.8);
            preview.src = imageData;
            capturedPhoto = imageData;

            // Show preview and hide camera
            document.getElementById('cameraFeed').classList.add('hidden');
            document.getElementById('photoCanvas').classList.remove('hidden');
            document.getElementById('photoPreview').classList.remove('hidden');

            // Update buttons
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'inline-flex';
            document.getElementById('confirmClaimBtn').disabled = false;

            // Store photo data for form submission
            document.getElementById('claimantPhotoData').value = capturedPhoto;
        }

        function retakePhoto() {
            // Hide preview and show camera
            document.getElementById('cameraFeed').classList.remove('hidden');
            document.getElementById('photoCanvas').classList.add('hidden');
            document.getElementById('photoPreview').classList.add('hidden');

            // Update buttons
            document.getElementById('captureBtn').style.display = 'inline-flex';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('confirmClaimBtn').disabled = true;

            capturedPhoto = null;
            document.getElementById('claimantPhotoData').value = '';
        }

        // Handle form submission
        document.getElementById('claimForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!capturedPhoto) {
                alert('Please take a photo first');
                return;
            }

            const submitBtn = document.getElementById('confirmClaimBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('../logic/claim_item_logic.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Item claimed successfully!');
                    window.location.href = 'student_dashboard.php';
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while claiming the item');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>