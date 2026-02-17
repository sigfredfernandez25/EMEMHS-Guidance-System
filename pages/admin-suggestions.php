<?php
session_start();
require_once '../logic/db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle mark as read/unread
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        if ($_POST['action'] === 'mark_read') {
            $stmt = $pdo->prepare("UPDATE anonymous_suggestions SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($_POST['action'] === 'mark_unread') {
            $stmt = $pdo->prepare("UPDATE anonymous_suggestions SET is_read = 0 WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM anonymous_suggestions WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    
    header("Location: admin-suggestions.php");
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query based on filter
$query = "SELECT * FROM anonymous_suggestions";
if ($filter === 'unread') {
    $query .= " WHERE is_read = 0";
} elseif ($filter === 'read') {
    $query .= " WHERE is_read = 1";
}
$query .= " ORDER BY submitted_at DESC";

$stmt = $pdo->query($query);
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts
$totalCount = $pdo->query("SELECT COUNT(*) FROM anonymous_suggestions")->fetchColumn();
$unreadCount = $pdo->query("SELECT COUNT(*) FROM anonymous_suggestions WHERE is_read = 0")->fetchColumn();
$readCount = $pdo->query("SELECT COUNT(*) FROM anonymous_suggestions WHERE is_read = 1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Suggestions - EMEMHS Guidance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fafafa;
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navigation-admin.php'; ?>

    <div class="main-content p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Anonymous Suggestions</h1>
                <p class="text-gray-600">View and manage suggestions from students</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Suggestions</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $totalCount; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Unread</p>
                            <p class="text-3xl font-bold text-orange-600"><?php echo $unreadCount; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Read</p>
                            <p class="text-3xl font-bold text-green-600"><?php echo $readCount; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="flex border-b border-gray-200">
                    <a href="?filter=all" class="px-6 py-4 text-sm font-medium <?php echo $filter === 'all' ? 'text-[#800000] border-b-2 border-[#800000]' : 'text-gray-600 hover:text-gray-900'; ?>">
                        All (<?php echo $totalCount; ?>)
                    </a>
                    <a href="?filter=unread" class="px-6 py-4 text-sm font-medium <?php echo $filter === 'unread' ? 'text-[#800000] border-b-2 border-[#800000]' : 'text-gray-600 hover:text-gray-900'; ?>">
                        Unread (<?php echo $unreadCount; ?>)
                    </a>
                    <a href="?filter=read" class="px-6 py-4 text-sm font-medium <?php echo $filter === 'read' ? 'text-[#800000] border-b-2 border-[#800000]' : 'text-gray-600 hover:text-gray-900'; ?>">
                        Read (<?php echo $readCount; ?>)
                    </a>
                </div>
            </div>

            <!-- Suggestions List -->
            <div class="space-y-4">
                <?php if (empty($suggestions)): ?>
                    <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-100">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-gray-600 text-lg">No suggestions found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($suggestions as $suggestion): ?>
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 <?php echo $suggestion['is_read'] ? 'opacity-75' : ''; ?>">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <?php if (!$suggestion['is_read']): ?>
                                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                                    <?php endif; ?>
                                    <span class="text-sm text-gray-500">
                                        <?php echo date('F j, Y \a\t g:i A', strtotime($suggestion['submitted_at'])); ?>
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $suggestion['id']; ?>">
                                        <input type="hidden" name="action" value="<?php echo $suggestion['is_read'] ? 'mark_unread' : 'mark_read'; ?>">
                                        <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                            <?php echo $suggestion['is_read'] ? 'Mark Unread' : 'Mark Read'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this suggestion?');">
                                        <input type="hidden" name="id" value="<?php echo $suggestion['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-gray-800 leading-relaxed"><?php echo nl2br(htmlspecialchars($suggestion['suggestion'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
