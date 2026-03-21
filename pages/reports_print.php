<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - EMEMHS Guidance Office</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 15mm;
            background: white;
            color: #000;
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            opacity: 0.05;
            z-index: -1;
            pointer-events: none;
        }

        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @media print {
            .watermark {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h2 {
            font-size: 14pt;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .info-section {
            margin-bottom: 4px;
            font-size: 9pt;
        }

        .info-label {
            font-weight: bold;
            display: inline;
        }

        .info-value {
            display: inline;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-box {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background: #f9f9f9;
        }

        .stat-box .label {
            font-size: 8pt;
            color: #666;
            margin-bottom: 3px;
        }

        .stat-box .value {
            font-size: 16pt;
            font-weight: bold;
            color: #800000;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table th {
            background: #f0f0f0;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #000;
        }

        .summary-table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }

        .summary-table tr:nth-child(even) {
            background: #fafafa;
        }

        .total-row {
            font-weight: bold;
            background: #e0e0e0 !important;
            font-size: 9pt;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 7pt;
            color: #666;
        }

        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: left;
        }

        .signature-label {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            min-height: 40px;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 8pt;
            color: #333;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #800000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 10pt;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background: #600000;
        }

        @media print {
            body {
                padding: 10mm;
            }
            
            .print-button {
                display: none;
            }

            .watermark {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">
        <img src="../image/ememhs-logo.png" alt="EMEMHS Logo">
    </div>

    <button class="print-button" onclick="window.print()">
        🖨️ Print Report
    </button>

    <!-- Header -->
    <div class="header">
        <h2>Guidance Management System Report</h2>
        
        <div class="info-section">
            <span class="info-label">Department:</span>
            <span class="info-value">Guidance Office</span>
        </div>
        
        <div class="info-section">
            <span class="info-label">Report Type:</span>
            <span class="info-value">Student Concern and Complaint Report</span>
        </div>
        
        <div class="info-section">
            <span class="info-label">Date Generated:</span>
            <span class="info-value"><?= date('F d, Y h:i A') ?></span>
        </div>
        
        <div class="info-section">
            <span class="info-label">Period:</span>
            <span class="info-value"><?= date('F d, Y', strtotime($start_date)) ?> - <?= date('F d, Y', strtotime($end_date)) ?></span>
        </div>
        
        <?php if ($complaint_type !== 'all'): ?>
        <div class="info-section">
            <span class="info-label">Type Filter:</span>
            <span class="info-value"><?= htmlspecialchars($complaint_type) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($severity !== 'all'): ?>
        <div class="info-section">
            <span class="info-label">Severity Filter:</span>
            <span class="info-value"><?= htmlspecialchars(ucfirst($severity)) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($grade_level !== 'all'): ?>
        <div class="info-section">
            <span class="info-label">Grade Filter:</span>
            <span class="info-value">Grade <?= htmlspecialchars($grade_level) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistical Summary -->
    <?php
    // Calculate unique students who submitted reports
    $unique_students = [];
    foreach ($complaints as $complaint) {
        $unique_students[$complaint['student_id']] = true;
    }
    $total_students = count($unique_students);
    
    // Calculate resolved and unresolved
    $resolved_count = $by_status['resolved'] ?? 0;
    $unresolved_count = $total_complaints - $resolved_count;
    ?>
    
    <h3 class="section-title">Statistical Summary</h3>
    <div class="stats-grid">
        <div class="stat-box">
            <div class="label">Students Who Submitted Reports</div>
            <div class="value"><?= $total_students ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Cases Resolved</div>
            <div class="value"><?= $resolved_count ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Cases Unresolved</div>
            <div class="value"><?= $unresolved_count ?></div>
        </div>
    </div>

    <!-- Summary Section -->
    <h3 class="section-title">I. Summary of Student Concerns</h3>
    
    <table class="summary-table">
        <thead>
            <tr>
                <th>Category</th>
                <th style="text-align: center; width: 150px;">Number of Cases</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Sort by type name
            ksort($by_type);
            foreach ($by_type as $type => $count): 
            ?>
                <tr>
                    <td><?= htmlspecialchars($type) ?></td>
                    <td style="text-align: center;"><?= $count ?></td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($by_type)): ?>
                <tr>
                    <td colspan="2" style="text-align: center; padding: 15px; color: #666;">
                        No complaints found for the selected period.
                    </td>
                </tr>
            <?php endif; ?>
            
            <tr class="total-row">
                <td>Total Cases</td>
                <td style="text-align: center;"><?= $total_complaints ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Prepared by:</div>
            <div class="signature-line"></div>
            <div class="signature-title">Guidance Counselor</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Approved by:</div>
            <div class="signature-line"></div>
            <div class="signature-title">Principal</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>EMEMHS Guidance Office - Confidential Report</strong></p>
        <p>This document contains sensitive student information and should be handled accordingly.</p>
    </div>

    <script>
        // Auto-trigger print dialog after page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
