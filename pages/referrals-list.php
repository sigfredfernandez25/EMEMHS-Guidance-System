<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: login.php");
    exit();
}

// Get all referrals with student and complaint details
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        s.first_name,
        s.last_name,
        s.grade_level,
        s.section,
        cc.type as complaint_type,
        cc.description as complaint_description
    FROM referrals r
    JOIN students s ON r.student_id = s.id
    JOIN complaints_concerns cc ON r.complaint_id = cc.id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count by status
$pending_count = 0;
$completed_count = 0;
foreach ($referrals as $referral) {
    if ($referral['status'] === 'pending') $pending_count++;
    elseif ($referral['status'] === 'completed') $completed_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrals - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .minimal-card {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .minimal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .status-completed {
            background-color: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'?>
<div class="main-content">
    <main class="min-h-screen">
        <div class="p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="bg-[#800000]/10 text-[#800000] rounded-full p-3 mr-4">
                        <i class="fas fa-share-square text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Referrals</h1>
                        <p class="text-gray-600 text-sm">Cases referred to other offices</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="minimal-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Total Referrals</p>
                            <h3 class="text-2xl font-bold text-[#800000]"><?= count($referrals) ?></h3>
                        </div>
                        <i class="fas fa-share-square text-2xl text-[#800000]/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Pending</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?= $pending_count ?></h3>
                        </div>
                        <i class="fas fa-clock text-2xl text-orange-600/20"></i>
                    </div>
                </div>
                <div class="minimal-card p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs font-medium mb-1">Completed</p>
                            <h3 class="text-2xl font-bold text-green-600"><?= $completed_count ?></h3>
                        </div>
                        <i class="fas fa-check-circle text-2xl text-green-600/20"></i>
                    </div>
                </div>
            </div>

            <!-- Referrals Table -->
            <div class="minimal-card rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Grade & Section</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Complaint Type</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Referred To</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($referrals)): ?>
                                <?php foreach ($referrals as $referral): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($referral['grade_level'] . ' ' . $referral['section']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($referral['complaint_type']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?= htmlspecialchars($referral['referred_to']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= date('M d, Y', strtotime($referral['referral_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-<?= strtolower($referral['status']) ?>">
                                                <?php if ($referral['status'] === 'pending'): ?>
                                                    <i class="fas fa-clock"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php endif; ?>
                                                <?= ucfirst($referral['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center justify-center gap-2">
                                                <button onclick="viewReferral(<?= htmlspecialchars(json_encode($referral)) ?>)" 
                                                        class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg transition-colors group relative">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                        View Details
                                                    </span>
                                                </button>
                                                <?php if ($referral['status'] === 'pending'): ?>
                                                    <button onclick="markCompleted(<?= $referral['id'] ?>)" 
                                                            class="bg-green-600 hover:bg-green-700 text-white p-2 rounded-lg transition-colors group relative">
                                                        <i class="fas fa-check"></i>
                                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                            Mark as Completed
                                                        </span>
                                                    </button>
                                                <?php endif; ?>
                                                <a href="print-referral-slip.php?id=<?= $referral['id'] ?>" 
                                                   target="_blank"
                                                   class="bg-[#800000] hover:bg-[#600000] text-white p-2 rounded-lg transition-colors group relative">
                                                    <i class="fas fa-print"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                        Print Intake Sheet
                                                    </span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Referrals Yet</h3>
                                            <p class="text-sm text-gray-500">Referrals will appear here when created</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- View Referral Modal -->
<div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-2xl shadow-lg rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b">
            <h3 class="text-xl font-semibold text-[#800000]">
                <i class="fas fa-share-square mr-2"></i>
                Referral Details
            </h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="py-6" id="modalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="pt-4 border-t flex justify-end gap-2">
            <button onclick="closeModal()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function viewReferral(referral) {
    const modal = document.getElementById('viewModal');
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Student Name</p>
                    <p class="font-medium">${referral.first_name} ${referral.last_name}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Grade & Section</p>
                    <p class="font-medium">${referral.grade_level} ${referral.section}</p>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Complaint Type</p>
                <p class="font-medium">${referral.complaint_type}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Referred To</p>
                <p class="font-medium">${referral.referred_to}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Reason for Referral</p>
                <p class="font-medium whitespace-pre-wrap">${referral.reason}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Referral Date</p>
                <p class="font-medium">${new Date(referral.referral_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
            </div>
            ${referral.notes ? `
                <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-[#800000]">
                    <p class="text-sm text-gray-500 mb-1">Notes</p>
                    <p class="font-medium whitespace-pre-wrap">${referral.notes}</p>
                </div>
            ` : ''}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function markCompleted(referralId) {
    const notes = prompt('Add notes (optional):');
    
    if (notes === null) return; // User cancelled
    
    const formData = new FormData();
    formData.append('referral_id', referralId);
    formData.append('status', 'completed');
    formData.append('notes', notes);
    
    fetch('../logic/update_referral_logic.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Referral marked as completed');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error updating referral');
        console.error(error);
    });
}
</script>

</body>
</html>
