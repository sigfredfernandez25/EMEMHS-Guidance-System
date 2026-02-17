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
    header("Location: " . ($role === 'admin' ? 'found-items.php' : 'lost_item.php'));
    exit();
}

// Get lost item details
try {
    $stmt = $pdo->prepare("
        SELECT li.*, s.first_name, s.last_name, s.grade_level, s.section, s.phone_number as student_phone
        FROM lost_items li
        JOIN students s ON li.student_id = s.id
        WHERE li.id = ?
    ");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header("Location: " . ($role === 'admin' ? 'found-items.php' : 'lost_item.php'));
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching lost item: " . $e->getMessage());
    header("Location: " . ($role === 'admin' ? 'found-items.php' : 'lost_item.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Item Details - <?php echo ucfirst($role); ?> Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-badge.found {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .status-badge.claimed {
            background-color: #D1FAE5;
            color: #065F46;
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

    <main class="max-w-5xl mx-auto px-4 py-8<?php echo ($role !== 'student') ? ' pl-[180px] lg:pl-[180px] md:pl-[0] sm:pl-[0]' : ''; ?>">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?php echo $role === 'admin' ? 'found-items.php' : 'lost_item.php'; ?>" 
               class="inline-flex items-center text-[#800000] hover:text-[#a52a2a] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Lost Items
            </a>
        </div>

        <!-- Item Details Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#800000] to-[#a52a2a] px-6 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-white">Lost Item Details</h1>
                    <span class="status-badge <?php echo strtolower($item['status']); ?>">
                        <?php echo htmlspecialchars($item['status']); ?>
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Item Information -->
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Item Information</h2>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Item Name</p>
                                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Category</p>
                                    <p class="text-base text-gray-900 capitalize"><?php echo htmlspecialchars($item['category']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Description</p>
                                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($item['description']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Loss Details -->
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Loss Details</h2>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date Lost</p>
                                    <p class="text-base text-gray-900"><?php echo date('F j, Y', strtotime($item['date_lost'])); ?></p>
                                </div>
                                <?php if ($item['time_lost']): ?>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Time Lost</p>
                                    <p class="text-base text-gray-900"><?php echo date('g:i A', strtotime($item['time_lost'])); ?></p>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Location</p>
                                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($item['location']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Student Information -->
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Student Information</h2>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Name</p>
                                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Grade & Section</p>
                                    <p class="text-base text-gray-900">Grade <?php echo htmlspecialchars($item['grade_level']); ?> - <?php echo htmlspecialchars($item['section']); ?></p>
                                </div>
                                <?php if ($item['phone_number']): ?>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Contact Number</p>
                                    <p class="text-base text-gray-900"><?php echo htmlspecialchars($item['phone_number']); ?></p>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">SMS Notifications</p>
                                    <p class="text-base text-gray-900">
                                        <?php echo $item['receive_sms'] ? '✓ Enabled' : '✗ Disabled'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Item Photo -->
                        <?php if ($item['photo'] && $item['mime_type']): ?>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Item Photo</h2>
                            <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>"
                                 alt="Item Photo"
                                 class="w-full h-auto rounded-lg shadow-md border border-gray-200" />
                        </div>
                        <?php endif; ?>

                        <!-- Report Details -->
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Report Details</h2>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Report Date</p>
                                    <p class="text-base text-gray-900"><?php echo date('F j, Y', strtotime($item['date'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Report Time</p>
                                    <p class="text-base text-gray-900"><?php echo date('g:i A', strtotime($item['time'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <p class="text-base">
                                        <span class="status-badge <?php echo strtolower($item['status']); ?>">
                                            <?php echo htmlspecialchars($item['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Actions -->
                        <?php if ($role === 'admin' && $item['status'] === 'pending'): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h2>
                            <button onclick="notifyStudent(<?php echo $item['id']; ?>)"
                                    class="w-full bg-[#800000] text-white px-4 py-2 rounded-lg hover:bg-[#a52a2a] transition-colors">
                                Mark as Found & Notify Student
                            </button>
                        </div>
                        <?php endif; ?>

                        <?php if ($item['status'] === 'found' && $item['claimed_at']): ?>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Claim Information</h2>
                            <p class="text-sm text-gray-600">
                                Claimed on <?php echo date('F j, Y g:i A', strtotime($item['claimed_at'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function notifyStudent(itemId) {
            if (!confirm('Are you sure you want to mark this item as found and notify the student?')) {
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
                alert('An error occurred while notifying the student.');
            });
        }
    </script>
</body>

</html>
