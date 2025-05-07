<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
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
try {
    // Get all found items
    $stmt = $pdo->prepare("
        SELECT li.*, s.first_name, s.last_name, s.student_id as student_number
        FROM " . TBL_LOST_ITEMS . " li
        LEFT JOIN " . TBL_STUDENTS . " s ON li.student_id = s.id
        WHERE li.status = 'found'
        ORDER BY li.date_created DESC
    ");
    $stmt->execute();
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
    <title>Lost Items</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .table-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 55, 72, 0.2);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-found {
            background-color: #dcfce7;
            color: #166534;
        }

        .item-card {
            transition: transform 0.2s ease-in-out;
        }
        .item-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation-admin.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <div class="mt-8">
            <h2 class="text-3xl font-bold text-[#800000] mb-2">Found Items</h2>
            <p class="text-gray-600">View and manage all found items in the system</p>
        </div>

        <?php if (empty($foundItems)): ?>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Found Items</h3>
                <p class="text-gray-500">There are currently no found items in the system.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($foundItems as $item): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden item-card">
                        <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                                <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                     class="w-full h-48 object-cover" />
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Category: <?php echo htmlspecialchars($item['category']); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                    Found
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Description</p>
                                    <p class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['description'] ?? 'No description provided'); ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Location Found</p>
                                    <p class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['location_found'] ?? 'Not specified'); ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date Found</p>
                                    <p class="text-sm text-gray-900">
                                        <?php echo date('F j, Y', strtotime($item['date_created'])); ?>
                                    </p>
                                </div>

                                <?php if (!empty($item['student_id'])): ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Claimed By</p>
                                        <p class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                            <span class="text-gray-500">(<?php echo htmlspecialchars($item['student_number']); ?>)</span>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($item['student_id'])): ?>
                                <div class="mt-4">
                                    <button onclick="notifyStudent(<?php echo $item['id']; ?>)" 
                                            class="w-full bg-[#800000] text-white px-4 py-2 rounded-lg hover:bg-[#a52a2a] transition-colors">
                                        Notify Student
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
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
    </script>
</body>

</html>