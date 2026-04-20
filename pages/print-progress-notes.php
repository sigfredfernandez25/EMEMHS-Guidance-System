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
    echo "<script>alert('Complaint not found'); window.close();</script>";
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
    <title>Individual Progress / Session Notes - <?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></title>
    <style>
        @media print {
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 5px 0;
            font-size: 14pt;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 16pt;
            font-weight: bold;
        }
        
        .student-info {
            margin: 20px 0;
            border: 1px solid #000;
            padding: 10px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        
        .session-section {
            margin: 30px 0;
            border: 2px solid #000;
            padding: 15px;
        }
        
        .section-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
            text-decoration: underline;
        }
        
        .content-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 80px;
            margin: 5px 0;
        }
        
        .signature-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 300px;
            margin: 30px 0 5px auto;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #800000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14pt;
        }
        
        .print-button:hover {
            background-color: #600000;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print
    </button>

    <?php foreach ($sessions as $index => $session): ?>
        <?php if ($index > 0): ?>
            <div class="page-break"></div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Republic of the Philippines</h1>
            <h1>Department of Education</h1>
            <h1>Region VI – Western Visayas</h1>
            <h1>Division of Negros Occidental</h1>
            <h1>HINIGARAN NATIONAL HIGH SCHOOL</h1>
            <h1>Hinigaran, Negros Occidental</h1>
            <h2>INDIVIDUAL PROGRESS / SESSION NOTES</h2>
        </div>

        <div class="student-info">
            <div class="info-row">
                <span class="info-label">NAME:</span>
                <span><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Grade & Section:</span>
                <span><?php echo htmlspecialchars($complaint['grade_level'] . ' - ' . $complaint['section']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Session #:</span>
                <span><?php echo $session['session_number']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">DATE:</span>
                <span><?php echo date('F j, Y', strtotime($session['session_date'])); ?></span>
            </div>
        </div>

        <div class="session-section">
            <div class="section-title">PRESENTING PROBLEM / ISSUE / CONCERNS</div>
            <div class="content-box">
                <?php if (!empty($session['presenting_problem_1'])): ?>
                    1. <?php echo htmlspecialchars($session['presenting_problem_1']); ?><br>
                <?php endif; ?>
                <?php if (!empty($session['presenting_problem_2'])): ?>
                    2. <?php echo htmlspecialchars($session['presenting_problem_2']); ?><br>
                <?php endif; ?>
                <?php if (!empty($session['presenting_problem_3'])): ?>
                    3. <?php echo htmlspecialchars($session['presenting_problem_3']); ?><br>
                <?php endif; ?>
            </div>

            <div class="section-title">A. GENERAL OBSERVATIONS / CONDITION of the client:</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($session['general_observations'] ?? '')); ?>
            </div>

            <div class="section-title">B. SUMMARY (What transpired in the session) + Action taken</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($session['session_summary'])); ?>
            </div>

            <?php if (!empty($session['action_taken'])): ?>
            <div class="content-box" style="margin-top: 10px;">
                <strong>Action Taken:</strong><br>
                <?php echo nl2br(htmlspecialchars($session['action_taken'])); ?>
            </div>
            <?php endif; ?>

            <div class="section-title">C. FOR FOLLOW-UP / RECOMMENDATIONS / PLANS</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($session['follow_up_recommendations'] ?? '')); ?>
            </div>

            <div class="section-title">D. NEXT APPOINTMENT:</div>
            <div class="content-box">
                <?php 
                if (!empty($session['next_appointment_date'])) {
                    echo date('F j, Y', strtotime($session['next_appointment_date']));
                    if (!empty($session['next_appointment_time'])) {
                        echo ' at ' . date('g:i A', strtotime($session['next_appointment_time']));
                    }
                }
                ?>
            </div>
        </div>

        <div class="signature-section">
            <div>Counselor: <?php echo htmlspecialchars($session['counselor_name']); ?></div>
            <div class="signature-line"></div>
            <div>Signature over Printed Name</div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($sessions)): ?>
        <div class="header">
            <h1>Republic of the Philippines</h1>
            <h1>Department of Education</h1>
            <h1>Region VI – Western Visayas</h1>
            <h1>Division of Negros Occidental</h1>
            <h1>HINIGARAN NATIONAL HIGH SCHOOL</h1>
            <h1>Hinigaran, Negros Occidental</h1>
            <h2>INDIVIDUAL PROGRESS / SESSION NOTES</h2>
        </div>
        <div style="text-align: center; padding: 50px;">
            <p>No session notes available for this complaint.</p>
        </div>
    <?php endif; ?>

</body>
</html>
