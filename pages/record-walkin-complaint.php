<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if staff is logged in
if (!$_SESSION['isLoggedIn']) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Walk-in Complaint - Guidance Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #800000;
            --primary-hover: #600000;
            --secondary-color: #64748b;
            --background-color: #f8fafc;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
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

        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background-color: white;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: var(--secondary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .required {
            color: #ef4444;
        }

        .info-box {
            background-color: rgba(128, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .severity-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .severity-low {
            background-color: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .severity-medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .severity-high {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .severity-urgent {
            background-color: rgba(139, 69, 19, 0.1);
            color: #a16207;
            animation: pulse 2s infinite;
        }

        .severity-option {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .severity-option:hover {
            border-color: var(--primary-color);
            background-color: rgba(128, 0, 0, 0.02);
        }

        .severity-option input[type="radio"]:checked + label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .severity-option input[type="radio"]:checked ~ .severity-icon {
            color: var(--primary-color);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</head>
<body class="min-h-screen">
<?php include 'navigation-admin.php'?>
<div class="main-content">
    <main class="min-h-screen">
        <div class="p-8">
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg flex items-start animate-fade-in">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-green-800 font-medium"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg flex items-start animate-fade-in">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-red-800 font-medium"><?= htmlspecialchars($_SESSION['error_message']) ?></p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Record Walk-in Complaint</h1>
                            <p class="text-sm text-gray-600 mt-1">Document complaints from students who walked in without an online appointment</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-[#800000] mt-1 mr-3"></i>
                    <div>
                        <p class="text-sm text-gray-700">
                            <strong>Walk-in Recording:</strong> Use this form to record complaints or concerns from students who came for counseling without scheduling an online appointment. This helps maintain complete history tracking.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="../logic/record_walkin_complaint_logic.php" method="POST" class="form-card p-8">
                <!-- Student Information Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        <i class="fas fa-user mr-2 text-[#800000]"></i>
                        Student Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="form-label">
                                First Name <span class="required">*</span>
                            </label>
                            <input type="text" name="first_name" id="first_name" class="form-input" required placeholder="Enter first name">
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="form-label">
                                Last Name <span class="required">*</span>
                            </label>
                            <input type="text" name="last_name" id="last_name" class="form-input" required placeholder="Enter last name">
                        </div>

                        <!-- Grade Level -->
                        <div>
                            <label for="grade_level" class="form-label">
                                Grade Level <span class="required">*</span>
                            </label>
                            <select name="grade_level" id="grade_level" class="form-select" required>
                                <option value="">-- Select grade --</option>
                                <option value="7">Grade 7</option>
                                <option value="8">Grade 8</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>

                        <!-- Section -->
                        <div>
                            <label for="section" class="form-label">
                                Section <span class="required">*</span>
                            </label>
                            <input type="text" name="section" id="section" class="form-input" required placeholder="e.g., A, B, Einstein">
                        </div>

                        <!-- Age (Optional) -->
                        <div>
                            <label for="age" class="form-label">
                                Age
                            </label>
                            <input type="number" name="age" id="age" class="form-input" min="10" max="99" placeholder="Enter age" oninput="if(this.value.length > 2) this.value = this.value.slice(0,2);">
                        </div>

                        <!-- Contact Number (Optional) -->
                        <div>
                            <label for="contact_number" class="form-label">
                                Contact Number
                            </label>
                            <input type="tel" name="contact_number" id="contact_number" class="form-input" pattern="09[0-9]{9}" maxlength="11" placeholder="09XXXXXXXXX" oninput="validatePhoneNumber()">
                            <span id="phone_status" class="text-xs text-red-600 mt-1 block"></span>
                        </div>
                    </div>
                </div>

                <!-- Complaint Information Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">
                        <i class="fas fa-clipboard-list mr-2 text-[#800000]"></i>
                        Complaint/Concern Details
                    </h2>

                    <!-- Complaint Type -->
                    <div class="mb-6">
                        <label for="complaint_type" class="form-label">
                            Complaint/Concern Type <span class="required">*</span>
                        </label>
                        <select name="complaint_type" id="complaint_type" class="form-select" required>
                            <option value="">-- Select type --</option>
                            <option value="academic_stress">Academic Stress</option>
                            <option value="family_problems">Family Problems</option>
                            <option value="peer_relationship">Peer Pressure/Relationship Issues</option>
                            <option value="bullying">Bullying</option>
                            <option value="mental_health">Mental Health Concerns</option>
                            <option value="behavioral_issues">Behavioral Issues</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <!-- Other Specify (hidden by default) -->
                    <div id="other_specify_container" class="mb-6 hidden">
                        <label for="other_specify" class="form-label">
                            Please Specify <span class="required">*</span>
                        </label>
                        <input type="text" name="other_specify" id="other_specify" class="form-input" placeholder="Specify the type of complaint">
                    </div>

                    <!-- Severity -->
                    <div class="mb-6">
                        <label for="severity" class="form-label">
                            Severity Level <span class="required">*</span>
                        </label>
                        <select name="severity" id="severity" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="form-label">
                            Complaint/Concern Details <span class="required">*</span>
                        </label>
                        <textarea name="description" id="description" class="form-textarea" required placeholder="Describe the complaint or concern discussed during the counseling session..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Provide detailed notes about the counseling session and the student's concerns.</p>
                    </div>

                    <!-- Counseling Date -->
                    <div class="mb-6">
                        <label for="counseling_date" class="form-label">
                            Counseling Date <span class="required">*</span>
                        </label>
                        <input type="date" name="counseling_date" id="counseling_date" class="form-input" required value="<?= date('Y-m-d') ?>">
                        <p class="text-xs text-gray-500 mt-1">Date when the counseling session took place.</p>
                    </div>

                    <!-- Action Taken -->
                    <div class="mb-6">
                        <label for="action_taken" class="form-label">
                            Action Taken / Recommendations
                        </label>
                        <textarea name="action_taken" id="action_taken" class="form-textarea" placeholder="Describe any actions taken or recommendations given during the session..."></textarea>
                    </div>

                    <!-- Admin Remarks -->
                    <div class="mb-6">
                        <label for="admin_remark" class="form-label">
                            Admin Remarks / Resolution Notes <span class="required">*</span>
                        </label>
                        <textarea name="admin_remark" id="admin_remark" class="form-textarea" required placeholder="Document the outcome of the counseling session, resolution status, and any important notes..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Required: Explain how the issue was addressed and the current status.</p>
                    </div>

                    <!-- Follow-up Required -->
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="follow_up_required" value="1" class="mr-3 w-4 h-4 text-[#800000] border-gray-300 rounded focus:ring-[#800000]">
                            <span class="form-label mb-0">Follow-up session required</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="complaint-concern-admin.php" class="btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i>
                        Record Complaint
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Show/hide "Other Specify" field
    document.getElementById('complaint_type').addEventListener('change', function() {
        const otherContainer = document.getElementById('other_specify_container');
        const otherInput = document.getElementById('other_specify');
        
        if (this.value === 'others') {
            otherContainer.classList.remove('hidden');
            otherInput.required = true;
        } else {
            otherContainer.classList.add('hidden');
            otherInput.required = false;
            otherInput.value = '';
        }
    });

    // Set max date to today
    document.getElementById('counseling_date').max = new Date().toISOString().split('T')[0];

    // Phone number validation
    function validatePhoneNumber() {
        const phoneInput = document.getElementById('contact_number');
        const phoneStatus = document.getElementById('phone_status');
        const phoneValue = phoneInput.value;

        // Remove non-numeric characters
        phoneInput.value = phoneValue.replace(/\D/g, '');

        if (phoneInput.value.length > 0) {
            if (phoneInput.value.length !== 11) {
                phoneStatus.textContent = 'Phone number must be exactly 11 digits';
                phoneInput.setCustomValidity('Phone number must be exactly 11 digits');
            } else if (!phoneInput.value.startsWith('09')) {
                phoneStatus.textContent = 'Phone number must start with 09';
                phoneInput.setCustomValidity('Phone number must start with 09');
            } else {
                phoneStatus.textContent = '';
                phoneInput.setCustomValidity('');
            }
        } else {
            phoneStatus.textContent = '';
            phoneInput.setCustomValidity('');
        }
    }
</script>
</body>
</html>
