<?php
session_start();
require_once '../logic/db_connection.php';
require_once '../logic/sql_querries.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    header("Location: " . ($role === 'admin' ? 'admin-lost-items.php' : 'lost_item.php'));
    exit();
}

// Get lost item details
try {
    $stmt = $pdo->prepare("
        SELECT li.*, 
               s.first_name, 
               s.last_name, 
               s.grade_level, 
               s.section, 
               s.phone_number as student_phone,
               claimed_s.first_name as claimed_by_first_name,
               claimed_s.last_name as claimed_by_last_name
        FROM lost_items li
        JOIN students s ON li.student_id = s.id
        LEFT JOIN students claimed_s ON li.claimed_by_student_id = claimed_s.id
        WHERE li.id = ?
    ");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header("Location: " . ($role === 'admin' ? 'admin-lost-items.php' : 'lost_item.php'));
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching lost item: " . $e->getMessage());
    header("Location: " . ($role === 'admin' ? 'admin-lost-items.php' : 'lost_item.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Item Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

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
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-claimed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .info-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.875rem;
            color: #64748b;
            min-width: 120px;
            flex-shrink: 0;
        }

        .info-value {
            font-size: 0.875rem;
            color: #0f172a;
            font-weight: 500;
        }
    </style>
</head>

<body class="min-h-screen">
    <?php
    if ($role === 'student') {
        include 'navigation.php';
    } else {
        include 'navigation-admin.php';
    }
    ?>

    <main class="max-w-4xl mx-auto px-4 py-6 sm:py-8<?php echo ($role !== 'student') ? ' pl-[180px] lg:pl-[180px] md:pl-[0] sm:pl-[0]' : ''; ?>">
        <!-- Back Button -->
        <a href="<?php echo $role === 'admin' ? 'admin-lost-items.php' : 'lost_item.php'; ?>" 
           class="inline-flex items-center text-sm text-gray-600 hover:text-[#800000] transition-colors mb-4">
            <svg class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back
        </a>

        <!-- Compact Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Minimal Header -->
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 bg-gray-50 border-b border-gray-200">
                <h1 class="text-base sm:text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></h1>
                <span class="status-badge status-<?php echo strtolower($item['status']); ?>">
                    <?php echo htmlspecialchars($item['status']); ?>
                </span>
            </div>

            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Photo (if exists) -->
                    <?php if ($item['photo'] && $item['mime_type']): ?>
                    <div class="lg:col-span-1">
                        <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                             alt="Item Photo"
                             class="w-full h-auto rounded-lg border border-gray-200" />
                    </div>
                    <?php endif; ?>

                    <!-- Details -->
                    <div class="<?php echo ($item['photo'] && $item['mime_type']) ? 'lg:col-span-2' : 'lg:col-span-3'; ?>">
                        <div class="space-y-4">
                            <!-- Item Info -->
                            <div>
                                <div class="info-row">
                                    <span class="info-label">Category</span>
                                    <span class="info-value capitalize"><?php echo htmlspecialchars($item['category']); ?></span>
                                </div>
                                <?php if ($item['description']): ?>
                                <div class="info-row">
                                    <span class="info-label">Description</span>
                                    <span class="info-value"><?php echo htmlspecialchars($item['description']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="info-row">
                                    <span class="info-label">Date Lost</span>
                                    <span class="info-value"><?php echo date('M j, Y', strtotime($item['date_lost'])); ?></span>
                                </div>
                                <?php if ($item['time_lost']): ?>
                                <div class="info-row">
                                    <span class="info-label">Time Lost</span>
                                    <span class="info-value"><?php echo date('g:i A', strtotime($item['time_lost'])); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="info-row">
                                    <span class="info-label">Location</span>
                                    <span class="info-value"><?php echo htmlspecialchars($item['location']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Reported</span>
                                    <span class="info-value"><?php echo date('M j, Y g:i A', strtotime($item['date'] . ' ' . $item['time'])); ?></span>
                                </div>
                                <?php if ($role === 'admin' || $role === 'student'): ?>
                                <div class="info-row">
                                    <span class="info-label">Student</span>
                                    <span class="info-value"><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?> (Grade <?php echo htmlspecialchars($item['grade_level']); ?> - <?php echo htmlspecialchars($item['section']); ?>)</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Admin Actions -->
                            <?php if ($role === 'admin' && $item['status'] === 'pending'): ?>
                            <div class="pt-4 border-t border-gray-200">
                                <button onclick="notifyStudent(<?php echo $item['id']; ?>)"
                                        class="w-full sm:w-auto bg-[#800000] text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-[#600000] transition-colors">
                                    Mark as Found & Notify
                                </button>
                            </div>
                            <?php endif; ?>

                            <?php if ($item['status'] === 'claimed' && $item['claimed_at']): ?>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 text-sm text-green-700 mb-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">Claimed on <?php echo date('M j, Y g:i A', strtotime($item['claimed_at'])); ?></span>
                                    </div>
                                    
                                    <?php if (!empty($item['claimed_by_first_name'])): ?>
                                    <div class="text-sm text-gray-700 mb-3">
                                        <span class="font-medium">Claimed by:</span> 
                                        <?php echo htmlspecialchars($item['claimed_by_first_name'] . ' ' . $item['claimed_by_last_name']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['claimant_photo']) && !empty($item['claimant_photo_mime_type'])): ?>
                                    <div>
                                        <p class="text-xs text-gray-600 mb-2 font-medium">Claimant Photo:</p>
                                        <img src="data:<?php echo $item['claimant_photo_mime_type']; ?>;base64,<?php echo base64_encode($item['claimant_photo']); ?>"
                                             alt="Claimant Photo"
                                             class="w-full max-w-xs h-auto rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openClaimantPhotoModal(this.src, '<?php echo htmlspecialchars($item['claimed_by_first_name'] . ' ' . $item['claimed_by_last_name']); ?>', '<?php echo date('M j, Y g:i A', strtotime($item['claimed_at'])); ?>')">
                                    </div>
                                    <?php else: ?>
                                    <div class="text-xs text-gray-500 italic">
                                        No claimant photo available
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact Timeline -->
        <div class="mt-6">
            <?php 
            require_once __DIR__ . '/components/timeline.php';
            renderTimeline('lost_item', $item_id);
            ?>
        </div>
    </main>

    <!-- Claimant Photo Modal -->
    <div id="claimantPhotoModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center" onclick="closeClaimantPhotoModal()">
        <div class="relative max-w-4xl mx-4" onclick="event.stopPropagation()">
            <button onclick="closeClaimantPhotoModal()" class="absolute -top-10 right-0 text-white text-2xl hover:text-gray-300">
                &times;
            </button>
            <img id="claimantPhotoImage" src="" alt="Claimant Photo" class="max-w-full max-h-[80vh] rounded-lg">
            <div class="mt-4 text-center">
                <p id="claimantPhotoInfo" class="text-white text-sm"></p>
            </div>
        </div>
    </div>

    <script>
        function openClaimantPhotoModal(imageSrc, claimedBy, claimedDate) {
            const modal = document.getElementById('claimantPhotoModal');
            const image = document.getElementById('claimantPhotoImage');
            const info = document.getElementById('claimantPhotoInfo');
            
            image.src = imageSrc;
            info.textContent = `Claimed by: ${claimedBy} on ${claimedDate}`;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeClaimantPhotoModal() {
            const modal = document.getElementById('claimantPhotoModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeClaimantPhotoModal();
            }
        });

        function notifyStudent(itemId) {
            if (!confirm('Mark this item as found and notify the student?')) {
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
    </script>
</body>

</html>
