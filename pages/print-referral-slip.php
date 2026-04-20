<?php
session_start();
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

$referral_id = $_GET['id'] ?? null;

if (!$referral_id) {
    die('Referral ID is required');
}

// Get referral details
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        s.first_name,
        s.last_name,
        s.grade_level,
        s.section,
        s.address,
        cc.type as complaint_type,
        cc.description as complaint_description,
        cc.severity
    FROM referrals r
    JOIN students s ON r.student_id = s.id
    JOIN complaints_concerns cc ON r.complaint_id = cc.id
    WHERE r.id = ?
");
$stmt->execute([$referral_id]);
$referral = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$referral) {
    die('Referral not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Intake Sheet - <?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                margin: 0.5in;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            max-width: 8.5in;
            margin: 0 auto;
            padding: 20px;
            background: white;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 12px;
        }
        
        .header h1 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 30px 0 20px 0;
        }
        
        .form-row {
            margin: 15px 0;
            display: flex;
            align-items: baseline;
        }
        
        .form-label {
            font-size: 14px;
            margin-right: 10px;
            white-space: nowrap;
        }
        
        .form-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 20px;
            padding: 0 5px;
        }
        
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 25px 0 15px 0;
        }
        
        .inline-fields {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }
        
        .inline-field {
            display: flex;
            align-items: baseline;
        }
        
        .inline-field .form-label {
            margin-right: 5px;
        }
        
        .inline-field .form-value {
            min-width: 100px;
        }
        
        .reason-area {
            margin: 15px 0;
        }
        
        .reason-lines {
            border-bottom: 1px solid #000;
            min-height: 20px;
            margin: 5px 0;
            padding: 0 5px;
        }
        
        .footer {
            margin-top: 80px;
            text-align: right;
        }
        
        .footer-text {
            font-size: 14px;
            margin: 2px 0;
        }
        
        .print-button {
            background: #800000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 0;
        }
        
        .print-button:hover {
            background: #600000;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print Intake Sheet</button>
    
    <div class="header">
        <p>Republic of the Philippines</p>
        <p>Department of Education</p>
        <p>Region VI – Western Visayas</p>
        <p>Division of Negros Occidental</p>
        <h1>HINIGARAN NATIONAL HIGH SCHOOL</h1>
        <p>Hinigaran, Negros Occidental</p>
    </div>
    
    <div class="title">GUIDANCE INTAKE SHEET</div>
    
    <div class="form-row">
        <span class="form-label">Date</span>
        <span class="form-label">:</span>
        <span class="form-value"><?= date('F d, Y', strtotime($referral['referral_date'])) ?></span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Referred by</span>
        <span class="form-label">:</span>
        <span class="form-value"><?= htmlspecialchars($referral['referred_to']) ?></span>
    </div>
    
    <div class="reason-area">
        <div class="form-label">Reason's for Referral:</div>
        <div class="reason-lines"><?= htmlspecialchars($referral['reason']) ?></div>
        <?php if (!empty($referral['notes'])): ?>
        <div class="reason-lines"><?= htmlspecialchars($referral['notes']) ?></div>
        <?php else: ?>
        <div class="reason-lines"></div>
        <?php endif; ?>
    </div>
    
    <div class="section-title">Student's Personal Information</div>
    
    <div class="form-row">
        <span class="form-label">Name</span>
        <span class="form-label">:</span>
        <span class="form-value"><?= htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']) ?></span>
    </div>
    
    <div class="inline-fields">
        <div class="inline-field" style="flex: 2;">
            <span class="form-label">Grade Level & Section:</span>
            <span class="form-value"><?= htmlspecialchars($referral['grade_level'] . ' ' . $referral['section']) ?></span>
        </div>
        <div class="inline-field">
            <span class="form-label">Age:</span>
            <span class="form-value" style="min-width: 60px;"></span>
        </div>
        <div class="inline-field">
            <span class="form-label">Birth Order:</span>
            <span class="form-value" style="min-width: 40px;"></span>
        </div>
        <div class="inline-field">
            <span class="form-label">Gender:</span>
            <span class="form-value" style="min-width: 80px;"></span>
        </div>
    </div>
    
    <div class="form-row">
        <span class="form-label">Address:</span>
        <span class="form-value"><?= htmlspecialchars($referral['address']) ?></span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Religion:</span>
        <span class="form-value"></span>
    </div>
    
    <div class="form-row">
        <span class="form-label">Adviser's Name:</span>
        <span class="form-value"></span>
    </div>
    
    <div style="margin-top: 25px;">
        <div class="form-label" style="margin-bottom: 10px;">In case of Emergency:</div>
        
        <div class="form-row">
            <span class="form-label">Person to Contact:</span>
            <span class="form-value"></span>
        </div>
        
        <div class="form-row">
            <span class="form-label">Relationship:</span>
            <span class="form-value"></span>
        </div>
        
        <div class="form-row">
            <span class="form-label">Contact number:</span>
            <span class="form-value"></span>
        </div>
    </div>
    
    <div class="footer">
        <div class="footer-text">Intake by: <?= strtoupper(htmlspecialchars($referral['referred_by_name'] ?? 'GUIDANCE OFFICE')) ?></div>
        <div class="footer-text">Guidance Designate-SHS</div>
    </div>
</body>
</html>
