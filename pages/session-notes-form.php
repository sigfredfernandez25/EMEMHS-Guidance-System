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
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
$is_edit = $session_id > 0;

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

// Get existing session data if editing
$session_data = null;
if ($is_edit) {
    $session_data = getSessionNote($session_id);
    if (!$session_data) {
        echo "<script>alert('Session note not found'); window.location.href='view-session-history.php?complaint_id=$complaint_id';</script>";
        exit();
    }
}

// Get all sessions for this complaint
$sessions = getSessionNotesByComplaint($complaint_id);
$next_session_number = count($sessions) + 1;

// Get counselor name from session
$counselor_name = $_SESSION['first_name'] ?? 'Counselor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Session Note - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .form-section {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }
        .section-header {
            color: #800000;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #800000;
        }
        .form-input, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
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
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'; ?>

<div class="main-content">
    <main class="p-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">
                            <?php echo $is_edit ? 'Edit Session Note' : 'Individual Progress / Session Notes'; ?>
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Session #<?php echo $is_edit ? $session_data['session_number'] : $next_session_number; ?> - 
                            <?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?>
                        </p>
                    </div>
                    <a href="view-session-history.php?complaint_id=<?php echo $complaint_id; ?>" class="btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to History
                    </a>
                </div>
            </div>

            <form action="../logic/add_session_note_logic.php" method="POST" class="space-y-6">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                    <input type="hidden" name="is_edit" value="1">
                <?php endif; ?>

                <!-- Student Information Header -->
                <div class="form-section bg-gradient-to-r from-[#800000] to-[#a52a2a] text-white">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-white/80 text-sm">Name</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-white/80 text-sm">Grade & Section</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($complaint['grade_level'] . ' - ' . $complaint['section']); ?></p>
                        </div>
                        <div>
                            <p class="text-white/80 text-sm">Session #</p>
                            <p class="font-semibold"><?php echo $is_edit ? $session_data['session_number'] : $next_session_number; ?></p>
                        </div>
                        <div>
                            <p class="text-white/80 text-sm">Date</p>
                            <p class="font-semibold"><?php echo date('F j, Y'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Session Date & Time -->
                <div class="form-section">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Session Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="session_date" required
                                   value="<?php echo $is_edit ? $session_data['session_date'] : date('Y-m-d'); ?>"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Session Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="session_time" required
                                   value="<?php echo $is_edit ? $session_data['session_time'] : date('H:i'); ?>"
                                   class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Presenting Problems -->
                <div class="form-section">
                    <h3 class="section-header">
                        <i class="fas fa-exclamation-circle mr-2"></i>PRESENTING PROBLEM / ISSUE / CONCERNS
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">1.</label>
                            <input type="text" name="presenting_problem_1" 
                                   value="<?php echo $is_edit ? htmlspecialchars($session_data['presenting_problem_1'] ?? '') : htmlspecialchars($complaint['description']); ?>"
                                   class="form-input" placeholder="First presenting problem">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">2.</label>
                            <input type="text" name="presenting_problem_2"
                                   value="<?php echo $is_edit ? htmlspecialchars($session_data['presenting_problem_2'] ?? '') : ''; ?>"
                                   class="form-input" placeholder="Second presenting problem (optional)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">3.</label>
                            <input type="text" name="presenting_problem_3"
                                   value="<?php echo $is_edit ? htmlspecialchars($session_data['presenting_problem_3'] ?? '') : ''; ?>"
                                   class="form-input" placeholder="Third presenting problem (optional)">
                        </div>
                    </div>
                </div>

                <!-- General Observations -->
                <div class="form-section">
                    <h3 class="section-header">
                        <i class="fas fa-eye mr-2"></i>A. GENERAL OBSERVATIONS / CONDITION of the client
                    </h3>
                    <textarea name="general_observations" rows="4" class="form-textarea"
                              placeholder="Describe the client's general condition, demeanor, and observations..."><?php echo $is_edit ? htmlspecialchars($session_data['general_observations'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Session Summary -->
                <div class="form-section">
                    <h3 class="section-header">
                        <i class="fas fa-clipboard-list mr-2"></i>B. SUMMARY (What transpired in the session) + Action taken <span class="text-red-500">*</span>
                    </h3>
                    <textarea name="session_summary" rows="6" required class="form-textarea"
                              placeholder="Provide a detailed summary of what happened during the session and actions taken..."><?php echo $is_edit ? htmlspecialchars($session_data['session_summary'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Action Taken -->
                <div class="form-section">
                    <h3 class="section-header">
                        <i class="fas fa-tasks mr-2"></i>Action Taken
                    </h3>
                    <textarea name="action_taken" rows="4" class="form-textarea"
                              placeholder="Specific actions taken during or after the session..."><?php echo $is_edit ? htmlspecialchars($session_data['action_taken'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Follow-up Recommendations -->
                <div class="form-section">
                    <h3 class="section-header">
                        <i class="fas fa-lightbulb mr-2"></i>C. FOR FOLLOW-UP / RECOMMENDATIONS / PLANS
                    </h3>
                    <textarea name="follow_up_recommendations" rows="5" class="form-textarea"
                              placeholder="Recommendations, plans, and follow-up actions..."><?php echo $is_edit ? htmlspecialchars($session_data['follow_up_recommendations'] ?? '') : ''; ?></textarea>
                </div>

                <!-- Next Appointment -->
                <div class="form-section">
                    <h3 class="section-header">
                        <i class="fas fa-calendar-alt mr-2"></i>D. NEXT APPOINTMENT
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input type="date" name="next_appointment_date"
                                   value="<?php echo $is_edit ? ($session_data['next_appointment_date'] ?? '') : ''; ?>"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                            <input type="time" name="next_appointment_time"
                                   value="<?php echo $is_edit ? ($session_data['next_appointment_time'] ?? '') : ''; ?>"
                                   class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Counselor Name -->
                <div class="form-section">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Counselor Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="counselor_name" required
                           value="<?php echo $is_edit ? htmlspecialchars($session_data['counselor_name'] ?? $counselor_name) : $counselor_name; ?>"
                           class="form-input">
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="view-session-history.php?complaint_id=<?php echo $complaint_id; ?>" class="btn-secondary">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i><?php echo $is_edit ? 'Update' : 'Save'; ?> Session Note
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>
