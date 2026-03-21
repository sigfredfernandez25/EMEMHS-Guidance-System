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
            padding: 20mm;
            background: white;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24pt;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 18pt;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 11pt;
            color: #333;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }

        .stat-box .label {
            font-size: 10pt;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-box .value {
            font-size: 24pt;
            font-weight: bold;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-item {
            border: 1px solid #ddd;
            padding: 15px;
        }

        .chart-item h3 {
            font-size: 12pt;
            margin-bottom: 10px;
        }

        .bar-item {
            margin-bottom: 10px;
        }

        .bar-label {
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            margin-bottom: 3px;
        }

        .bar-container {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: #800000;
        }

        .recommendations {
            margin-bottom: 30px;
        }

        .recommendation-item {
            border-left: 4px solid #800000;
            padding: 10px 15px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }

        .recommendation-item h4 {
            font-size: 11pt;
            margin-bottom: 5px;
        }

        .recommendation-item p {
            font-size: 9pt;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        table thead {
            background: #f0f0f0;
        }

        table th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            body {
                padding: 10mm;
            }
            
            .no-print {
                display: none !important;
            }
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
            font-size: 11pt;
            z-index: 1000;
        }

        .print-button:hover {
            background: #600000;
        }

        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Report
    </button>

    <!-- Header -->
    <div class="header">
        <h1>EMEMHS Guidance Office</h1>
        <h2>Complaints & Concerns Report</h2>
        <p><strong>Period:</strong> <?= date('F d, Y', strtotime($start_date)) ?> - <?= date('F d, Y', strtotime($end_date)) ?></p>
        <p><strong>Generated:</strong> <?= date('F d, Y h:i A') ?></p>
        <?php if ($complaint_type !== 'all'): ?>
            <p><strong>Type Filter:</strong> <?= htmlspecialchars($complaint_type) ?></p>
        <?php endif; ?>
        <?php if ($severity !== 'all'): ?>
            <p><strong>Severity Filter:</strong> <?= htmlspecialchars(ucfirst($severity)) ?></p>
        <?php endif; ?>
        <?php if ($grade_level !== 'all'): ?>
            <p><strong>Grade Filter:</strong> <?= htmlspecialchars($grade_level) ?></p>
        <?php endif; ?>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-stats">
        <div class="stat-box">
            <div class="label">Total Complaints</div>
            <div class="value"><?= $total_complaints ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Pending</div>
            <div class="value"><?= $by_status['pending'] ?? 0 ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Scheduled</div>
            <div class="value"><?= $by_status['scheduled'] ?? 0 ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Resolved</div>
            <div class="value"><?= $by_status['resolved'] ?? 0 ?></div>
        </div>
    </div>

    <!-- Charts -->
    <div class="section">
        <h2 class="section-title">Statistical Analysis</h2>
        <div class="chart-grid">
            <!-- By Type -->
            <div class="chart-item">
                <h3>Complaints by Type</h3>
                <?php 
                arsort($by_type);
                foreach ($by_type as $type => $count): 
                    $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                ?>
                    <div class="bar-item">
                        <div class="bar-label">
                            <span><?= htmlspecialchars($type) ?></span>
                            <span><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                        </div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- By Severity -->
            <div class="chart-item">
                <h3>Complaints by Severity</h3>
                <?php 
                $severity_order = ['urgent', 'high', 'medium', 'low'];
                foreach ($severity_order as $sev): 
                    if (!isset($by_severity[$sev])) continue;
                    $count = $by_severity[$sev];
                    $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                ?>
                    <div class="bar-item">
                        <div class="bar-label">
                            <span><?= ucfirst($sev) ?></span>
                            <span><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                        </div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- By Grade Level -->
            <div class="chart-item">
                <h3>Complaints by Grade Level</h3>
                <?php 
                ksort($by_grade);
                foreach ($by_grade as $grade => $count): 
                    $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                ?>
                    <div class="bar-item">
                        <div class="bar-label">
                            <span>Grade <?= htmlspecialchars($grade) ?></span>
                            <span><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                        </div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- By Status -->
            <div class="chart-item">
                <h3>Complaints by Status</h3>
                <?php 
                foreach ($by_status as $status => $count): 
                    $percentage = $total_complaints > 0 ? ($count / $total_complaints) * 100 : 0;
                ?>
                    <div class="bar-item">
                        <div class="bar-label">
                            <span><?= ucfirst($status) ?></span>
                            <span><?= $count ?> (<?= number_format($percentage, 1) ?>%)</span>
                        </div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="section recommendations">
        <h2 class="section-title">Key Recommendations</h2>
        <?php
        // Generate recommendations based on data
        $recommendations = [];
        
        // Check for high-frequency complaint types
        arsort($by_type);
        $top_types = array_slice($by_type, 0, 3, true);
        foreach ($top_types as $type => $count) {
            if ($count >= 5) {
                $recommendations[] = [
                    'title' => "Seminar on " . htmlspecialchars($type),
                    'description' => "There are {$count} reported cases of {$type}. Consider organizing a school-wide seminar to address this issue."
                ];
            }
        }
        
        // Check for high severity cases
        $high_severity = ($by_severity['urgent'] ?? 0) + ($by_severity['high'] ?? 0);
        if ($high_severity > 0) {
            $recommendations[] = [
                'title' => "Immediate Attention Required",
                'description' => "There are {$high_severity} high-priority cases that require immediate intervention and follow-up."
            ];
        }
        
        // Check for grade-specific issues
        arsort($by_grade);
        $top_grade = array_key_first($by_grade);
        if ($by_grade[$top_grade] >= 5) {
            $recommendations[] = [
                'title' => "Grade {$top_grade} Intervention",
                'description' => "Grade {$top_grade} has the highest number of complaints ({$by_grade[$top_grade]} cases). Consider targeted interventions for this grade level."
            ];
        }
        
        // Check pending cases
        $pending = $by_status['pending'] ?? 0;
        if ($pending > 10) {
            $recommendations[] = [
                'title' => "Pending Cases Backlog",
                'description' => "There are {$pending} pending cases awaiting scheduling. Consider allocating additional counseling time slots."
            ];
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'title' => "Maintain Current Programs",
                'description' => "Current intervention programs appear to be effective. Continue monitoring and maintain existing support systems."
            ];
        }
        
        foreach ($recommendations as $rec):
        ?>
            <div class="recommendation-item">
                <h4><?= $rec['title'] ?></h4>
                <p><?= $rec['description'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="page-break"></div>

    <!-- Detailed Table -->
    <div class="section">
        <h2 class="section-title">Detailed Complaints List (<?= $total_complaints ?> records)</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Grade</th>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($complaints)): ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($complaint['date_created'])) ?></td>
                            <td><?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?></td>
                            <td><?= htmlspecialchars($complaint['grade_level'] . '-' . $complaint['section']) ?></td>
                            <td><?= htmlspecialchars($complaint['type']) ?></td>
                            <td><?= ucfirst($complaint['severity']) ?></td>
                            <td><?= ucfirst($complaint['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            No complaints found for the selected filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>EMEMHS Guidance Office - Confidential Report</strong></p>
        <p>This document contains sensitive student information and should be handled accordingly.</p>
        <p>Page printed on <?= date('F d, Y \a\t h:i A') ?></p>
    </div>

    <script>
        // Auto-trigger print dialog after page loads
        window.onload = function() {
            // Small delay to ensure content is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
