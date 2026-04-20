<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
require_once '../logic/session_notes_logic.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
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
        :root {
            --primary-color: #800000;
            --primary-hover: #600000;
            --secondary-color: #64748b;
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

        .minimal-btn {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
        }

        .minimal-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .minimal-btn i {
            transition: transform 0.2s ease;
        }

        .minimal-btn:hover i {
            transform: translateX(4px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .section-title {
            position: relative;
            display: inline-block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.875rem;
        }

        .session-header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .action-btn {
            color: var(--secondary-color);
            transition: all 0.2s ease;
            padding: 0.5rem;
            border-radius: 0.375rem;
            position: relative;
        }

        .action-btn:hover {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .action-btn.delete:hover {
            color: #ef4444;
        }

        .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #1e293b;
            color: white;
            font-size: 0.75rem;
            border-radius: 0.375rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .action-btn:hover .tooltip {
            opacity: 1;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'; ?>

<div class="main-content">
    <main class="p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="minimal-card p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-3">Session History</h1>
                        <p class="text-gray-600 text-sm mb-1">
                            <i class="fas fa-user mr-2"></i>
                            <?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?> - 
                            <?php echo htmlspecialchars($complaint['grade_level'] . ' ' . $complaint['section']); ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-tag mr-2"></i>
                            <?php echo htmlspecialchars(ucfirst($complaint['type'])); ?> | 
                            <span class="capitalize"><?php echo htmlspecialchars($complaint['severity'] ?? 'medium'); ?> Priority</span>
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="session-notes-form.php?complaint_id=<?php echo $complaint_id; ?>" class="btn-primary">
                            <i class="fas fa-plus mr-2"></i>Add New Session
                        </a>
                        <a href="print-progress-notes.php?complaint_id=<?php echo $complaint_id; ?>" target="_blank" class="minimal-btn">
                            <i class="fas fa-print mr-2"></i>Print All
                        </a>
                        <a href="all-complaints.php" class="minimal-btn">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sessions Timeline -->
            <?php if (empty($sessions)): ?>
                <div class="minimal-card empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Session Notes Yet</h3>
                    <p>Start documenting counseling sessions for this complaint</p>
                    <a href="session-notes-form.php?complaint_id=<?php echo $complaint_id; ?>" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>Add First Session
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($sessions as $index => $session): ?>
                        <div class="minimal-card p-6">
                            <!-- Session Header -->
                            <div class="session-header">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-[#800000] mb-2">
                                            Session #<?php echo $session['session_number']; ?>
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-1">
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
                                           class="action-btn">
                                            <i class="fas fa-edit"></i>
                                            <span class="tooltip">Edit Session</span>
                                        </a>
                                        <button onclick="deleteSession(<?php echo $session['session_id']; ?>)" 
                                                class="action-btn delete">
                                            <i class="fas fa-trash"></i>
                                            <span class="tooltip">Delete Session</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Presenting Problems -->
                            <?php if (!empty($session['presenting_problem_1'])): ?>
                                <div class="mb-4">
                                    <h4 class="section-title mb-2">Presenting Problems</h4>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600 text-sm">
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
                                    <h4 class="section-title mb-2">General Observations</h4>
                                    <div class="info-box text-gray-600">
                                        <?php echo nl2br(htmlspecialchars($session['general_observations'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Session Summary -->
                            <div class="mb-4">
                                <h4 class="section-title mb-2">Session Summary</h4>
                                <div class="info-box text-gray-600" style="background: #eff6ff; border-color: #dbeafe;">
                                    <?php echo nl2br(htmlspecialchars($session['session_summary'])); ?>
                                </div>
                            </div>

                            <!-- Action Taken -->
                            <?php if (!empty($session['action_taken'])): ?>
                                <div class="mb-4">
                                    <h4 class="section-title mb-2">Action Taken</h4>
                                    <div class="info-box text-gray-600" style="background: #f0fdf4; border-color: #dcfce7;">
                                        <?php echo nl2br(htmlspecialchars($session['action_taken'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Follow-up Recommendations -->
                            <?php if (!empty($session['follow_up_recommendations'])): ?>
                                <div class="mb-4">
                                    <h4 class="section-title mb-2">Follow-up / Recommendations</h4>
                                    <div class="info-box text-gray-600" style="background: #fefce8; border-color: #fef3c7;">
                                        <?php echo nl2br(htmlspecialchars($session['follow_up_recommendations'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Next Appointment -->
                            <?php if (!empty($session['next_appointment_date'])): ?>
                                <div class="info-box" style="background: #faf5ff; border-color: #f3e8ff;">
                                    <h4 class="section-title mb-1">Next Appointment</h4>
                                    <p class="text-gray-600 text-sm">
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
