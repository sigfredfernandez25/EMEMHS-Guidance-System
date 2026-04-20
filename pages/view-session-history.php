<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/session_notes_logic.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn'] || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$complaint_id = isset($_GET['complaint_id']) ? intval($_GET['complaint_id']) : 0;

// Get complaint details
$stmt = $pdo->prepare("
    SELECT cc.*, s.first_name, s.last_name, s.grade_level, s.section, s.id as student_id
    FROM complaints_concerns cc
    JOIN students s ON cc.student_id = s.id
    WHERE cc.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    echo "<script>alert('Complaint not found'); window.location.href='all-complaints.php';</script>";
    exit();
}

// Get all sessions
$sessions = getSessionNotesByComplaint($complaint_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session History - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .session-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-left: 4px solid #800000;
        }
        .session-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -1px rgba(0, 0, 0, 0.15);
        }
        .btn-primary {
            background-color: #800000;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #600000;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #64748b;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }
        .timeline-line {
            position: absolute;
            left: 1.5rem;
            top: 3rem;
            bottom: -2rem;
            width: 2px;
            background: linear-gradient(to bottom, #800000, #e2e8f0);
        }
        .timeline-dot {
            position: absolute;
            left: 0.75rem;
            top: 1.5rem;
            width: 1.5rem;
            height: 1.5rem;
            background: #800000;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'; ?>

<div class="main-content">
    <main class="p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Session History</h1>
                        <p class="text-gray-600 mt-2">
                            <i class="fas fa-user mr-2"></i>
                            <?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?> - 
                            <?php echo htmlspecialchars($complaint['grade_level'] . ' ' . $complaint['section']); ?>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-tag mr-2"></i>
                            <?php echo htmlspecialchars(ucfirst($complaint['type'])); ?> | 
                            <span class="capitalize"><?php echo htmlspecialchars($complaint['severity'] ?? 'medium'); ?> Priority</span>
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="session-notes-form.php?complaint_id=<?php echo $complaint_id; ?>" class="btn-primary">
                            <i class="fas fa-plus mr-2"></i>Add New Session
                        </a>
                        <a href="print-progress-notes.php?complaint_id=<?php echo $complaint_id; ?>" target="_blank" class="btn-secondary">
                            <i class="fas fa-print mr-2"></i>Print All
                        </a>
                        <a href="all-complaints.php" class="btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sessions Timeline -->
            <?php if (empty($sessions)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Session Notes Yet</h3>
                    <p class="text-gray-500 mb-6">Start documenting counseling sessions for this complaint</p>
                    <a href="session-notes-form.php?complaint_id=<?php echo $complaint_id; ?>" class="btn-primary inline-block">
                        <i class="fas fa-plus mr-2"></i>Add First Session
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6 relative">
                    <?php foreach ($sessions as $index => $session): ?>
                        <div class="session-card p-6 relative ml-12">
                            <?php if ($index < count($sessions) - 1): ?>
                                <div class="timeline-line"></div>
                            <?php endif; ?>
                            <div class="timeline-dot"></div>
                            
                            <!-- Session Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-[#800000]">
                                        Session #<?php echo $session['session_number']; ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-calendar mr-2"></i>
                                        <?php echo date('F j, Y', strtotime($session['session_date'])); ?> at 
                                        <?php echo date('g:i A', strtotime($session['session_time'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-user-md mr-2"></i>
                                        <?php echo htmlspecialchars($session['counselor_name']); ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="session-notes-form.php?complaint_id=<?php echo $complaint_id; ?>&session_id=<?php echo $session['session_id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 p-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteSession(<?php echo $session['session_id']; ?>)" 
                                            class="text-red-600 hover:text-red-800 p-2" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Presenting Problems -->
                            <?php if (!empty($session['presenting_problem_1'])): ?>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Presenting Problems:</h4>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                                        <?php if (!empty($session['presenting_problem_1'])): ?>
                                            <li><?php echo htmlspecialchars($session['presenting_problem_1']); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($session['presenting_problem_2'])): ?>
                                            <li><?php echo htmlspecialchars($session['presenting_problem_2']); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($session['presenting_problem_3'])): ?>
                                            <li><?php echo htmlspecialchars($session['presenting_problem_3']); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- General Observations -->
                            <?php if (!empty($session['general_observations'])): ?>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">General Observations:</h4>
                                    <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">
                                        <?php echo nl2br(htmlspecialchars($session['general_observations'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Session Summary -->
                            <div class="mb-4">
                                <h4 class="font-semibold text-gray-700 mb-2">Session Summary:</h4>
                                <p class="text-gray-600 bg-blue-50 p-3 rounded-lg">
                                    <?php echo nl2br(htmlspecialchars($session['session_summary'])); ?>
                                </p>
                            </div>

                            <!-- Action Taken -->
                            <?php if (!empty($session['action_taken'])): ?>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Action Taken:</h4>
                                    <p class="text-gray-600 bg-green-50 p-3 rounded-lg">
                                        <?php echo nl2br(htmlspecialchars($session['action_taken'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Follow-up Recommendations -->
                            <?php if (!empty($session['follow_up_recommendations'])): ?>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Follow-up / Recommendations:</h4>
                                    <p class="text-gray-600 bg-yellow-50 p-3 rounded-lg">
                                        <?php echo nl2br(htmlspecialchars($session['follow_up_recommendations'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Next Appointment -->
                            <?php if (!empty($session['next_appointment_date'])): ?>
                                <div class="bg-purple-50 p-3 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 mb-1">Next Appointment:</h4>
                                    <p class="text-gray-600">
                                        <i class="fas fa-calendar-check mr-2"></i>
                                        <?php echo date('F j, Y', strtotime($session['next_appointment_date'])); ?>
                                        <?php if (!empty($session['next_appointment_time'])): ?>
                                            at <?php echo date('g:i A', strtotime($session['next_appointment_time'])); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function deleteSession(sessionId) {
    if (confirm('Are you sure you want to delete this session note? This action cannot be undone.')) {
        window.location.href = '../logic/delete_session_note_logic.php?session_id=' + sessionId + '&complaint_id=<?php echo $complaint_id; ?>';
    }
}
</script>

</body>
</html>
