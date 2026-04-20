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
    SELECT cc.*, s.first_name, s.last_name, s.grade_level, s.section, s.address, s.id as student_id
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
            @page {
                margin: 0.5in;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 11pt;
        }
        
        .header h1 {
            margin: 5px 0;
            font-size: 14pt;
            font-weight: bold;
        }
        
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .info-line {
            margin: 8px 0;
            display: flex;
            align-items: baseline;
        }
        
        .info-label {
            font-size: 11pt;
            margin-right: 5px;
        }
        
        .info-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 0 5px;
        }
        
        .inline-group {
            display: flex;
            gap: 15px;
            margin: 8px 0;
        }
        
        .inline-item {
            display: flex;
            align-items: baseline;
        }
        
        .inline-item .info-value {
            min-width: 80px;
        }
        
        .section-box {
            border: 2px solid #000;
            padding: 10px;
            margin: 15px 0;
            min-height: 120px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
        }
        
        .section-content {
            font-size: 11pt;
            line-height: 1.6;
        }
        
        .underline-section {
            margin: 15px 0;
        }
        
        .underline-section .section-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .underline-lines {
            border-bottom: 1px solid #000;
            min-height: 20px;
            margin: 3px 0;
            padding: 2px 0;
            word-wrap: break-word;
        }
        
        .footer {
            margin-top: 50px;
        }
        
        .footer-line {
            font-size: 11pt;
            margin: 3px 0;
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
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #600000;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print</button>

    <?php if (!empty($sessions)): ?>
        <?php foreach ($sessions as $index => $session): ?>
            <?php if ($index > 0): ?>
                <div class="page-break"></div>
            <?php endif; ?>
            
            <div class="header">
                <p>Republic of the Philippines</p>
                <p>Department of Education</p>
                <p>Region VI – Western Visayas</p>
                <p>Division of Negros Occidental</p>
                <h1>HINIGARAN NATIONAL HIGH SCHOOL</h1>
                <p>Hinigaran, Negros Occidental</p>
            </div>
            
            <div class="title">INDIVIDUAL PROGRESS/ SESSION NOTES</div>
            
            <div class="inline-group">
                <div class="inline-item" style="flex: 3;">
                    <span class="info-label">NAME:</span>
                    <span class="info-value"><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></span>
                </div>
                <div class="inline-item">
                    <span class="info-label">AGE:</span>
                    <span class="info-value" style="min-width: 40px;"></span>
                </div>
                <div class="inline-item">
                    <span class="info-label">SEX:</span>
                    <span class="info-value" style="min-width: 60px;"></span>
                </div>
                <div class="inline-item" style="flex: 1;">
                    <span class="info-label">DATE:</span>
                    <span class="info-value"><?php echo date('F d, Y', strtotime($session['session_date'])); ?></span>
                </div>
            </div>
            
            <div class="inline-group">
                <div class="inline-item" style="flex: 2;">
                    <span class="info-label">Grade & Section:</span>
                    <span class="info-value"><?php echo htmlspecialchars($complaint['grade_level'] . ' ' . $complaint['section']); ?></span>
                </div>
                <div class="inline-item" style="flex: 1;">
                    <span class="info-label">Adviser:</span>
                    <span class="info-value"></span>
                </div>
            </div>
            
            <div class="info-line">
                <span class="info-label">Session#:</span>
                <span class="info-value" style="max-width: 60px;"><?php echo $session['session_number']; ?></span>
            </div>
            
            <div class="info-line">
                <span class="info-label">ADDRESS:</span>
                <span class="info-value"><?php echo htmlspecialchars($complaint['address'] ?? ''); ?></span>
            </div>
            
            <div class="section-box">
                <div class="section-title">PRESENTING PROBLEM/ ISSUE/ CONCERNS</div>
                <div class="section-content">
                    <?php if (!empty($session['presenting_problem_1'])): ?>
                        1. <?php echo htmlspecialchars($session['presenting_problem_1']); ?><br>
                    <?php else: ?>
                        1.<br>
                    <?php endif; ?>
                    <br>
                    <?php if (!empty($session['presenting_problem_2'])): ?>
                        2. <?php echo htmlspecialchars($session['presenting_problem_2']); ?><br>
                    <?php else: ?>
                        2.<br>
                    <?php endif; ?>
                    <br>
                    <?php if (!empty($session['presenting_problem_3'])): ?>
                        3. <?php echo htmlspecialchars($session['presenting_problem_3']); ?>
                    <?php else: ?>
                        3.
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="underline-section">
                <div class="section-title">A. GENERAL OBSERVATIONS/CONDITION of the client:</div>
                <div class="underline-lines"><?php echo htmlspecialchars($session['general_observations'] ?? ''); ?></div>
                <div class="underline-lines"></div>
                <div class="underline-lines"></div>
                <div class="underline-lines"></div>
            </div>
            
            <div class="underline-section">
                <div class="section-title">B. SUMMARY (What transpired in the session) + Action taken</div>
                <?php 
                // Combine summary and action taken
                $combined_text = $session['session_summary'] ?? '';
                if (!empty($session['action_taken'])) {
                    $combined_text .= "\n" . $session['action_taken'];
                }
                
                // If there's content, display it with underlines, otherwise show empty lines
                if (!empty(trim($combined_text))):
                    $lines = explode("\n", $combined_text);
                    foreach ($lines as $line):
                ?>
                    <div class="underline-lines"><?php echo htmlspecialchars($line); ?></div>
                <?php 
                    endforeach;
                    // Add at least 2 more empty lines after content
                    for ($i = 0; $i < 2; $i++):
                ?>
                    <div class="underline-lines"></div>
                <?php 
                    endfor;
                else:
                    // Show 6 empty lines if no content
                    for ($i = 0; $i < 6; $i++):
                ?>
                    <div class="underline-lines"></div>
                <?php 
                    endfor;
                endif;
                ?>
            </div>
            
            <div class="underline-section">
                <div class="section-title">C. FOR FOLLOW-UP/RECOMMENDATIONS/PLANS</div>
                <?php 
                $followup_text = $session['follow_up_recommendations'] ?? '';
                
                // If there's content, display it with underlines, otherwise show empty lines
                if (!empty(trim($followup_text))):
                    $lines = explode("\n", $followup_text);
                    foreach ($lines as $line):
                ?>
                    <div class="underline-lines"><?php echo htmlspecialchars($line); ?></div>
                <?php 
                    endforeach;
                    // Add at least 2 more empty lines after content
                    for ($i = 0; $i < 2; $i++):
                ?>
                    <div class="underline-lines"></div>
                <?php 
                    endfor;
                else:
                    // Show 6 empty lines if no content
                    for ($i = 0; $i < 6; $i++):
                ?>
                    <div class="underline-lines"></div>
                <?php 
                    endfor;
                endif;
                ?>
            </div>
            
            <div class="info-line" style="margin-top: 20px;">
                <span class="info-label">D. NEXT APPOINTMENT:</span>
                <span class="info-value" style="max-width: 300px;">
                    <?php 
                    if (!empty($session['next_appointment_date'])) {
                        echo date('F d, Y', strtotime($session['next_appointment_date']));
                        if (!empty($session['next_appointment_time'])) {
                            echo ' at ' . date('g:i A', strtotime($session['next_appointment_time']));
                        }
                    }
                    ?>
                </span>
            </div>
            
            <div class="footer">
                <div class="footer-line">Counselor: <?php echo htmlspecialchars($session['counselor_name'] ?? 'GUIDANCE OFFICE'); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="header">
            <p>Republic of the Philippines</p>
            <p>Department of Education</p>
            <p>Region VI – Western Visayas</p>
            <p>Division of Negros Occidental</p>
            <h1>HINIGARAN NATIONAL HIGH SCHOOL</h1>
            <p>Hinigaran, Negros Occidental</p>
        </div>
        
        <div class="title">INDIVIDUAL PROGRESS/ SESSION NOTES</div>
        
        <div style="text-align: center; padding: 50px;">
            <p>No session notes available for this complaint.</p>
        </div>
    <?php endif; ?>

</body>
</html>
