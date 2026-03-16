<?php
session_start();
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn'] || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get unverified students
$stmt = $pdo->prepare(SQL_GET_UNVERIFIED_STUDENTS);
$stmt->execute();
$unverified_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Verification - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        .main-content {
            padding-top: 80px;
        }
        
        .minimal-card {
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .minimal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-color: #800000;
        }

        .minimal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #800000 0%, #a00000 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .minimal-card:hover::before {
            opacity: 1;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 95%;
            max-height: 95%;
            animation: zoomIn 0.3s ease;
        }

        @keyframes zoomIn {
            from { transform: translate(-50%, -50%) scale(0.8); }
            to { transform: translate(-50%, -50%) scale(1); }
        }

        .modal img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 32px;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(128, 0, 0, 0.8);
            transform: rotate(90deg);
        }

        .detail-modal {
            display: none;
            position: fixed;
            z-index: 1500;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease;
        }

        .detail-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 1.5rem;
            max-width: 95%;
            width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { 
                transform: translate(-50%, -40%);
                opacity: 0;
            }
            to { 
                transform: translate(-50%, -50%);
                opacity: 1;
            }
        }

        .school-id-preview {
            max-height: 220px;
            object-fit: contain;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .school-id-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        .status-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #fbbf24;
        }

        .btn-verify {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.3);
        }

        .btn-verify:hover:not(:disabled) {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -2px rgba(34, 197, 94, 0.4);
        }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-reject {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
        }

        .btn-reject:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -2px rgba(239, 68, 68, 0.4);
        }

        .btn-reject:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.875rem;
            color: #1f2937;
            font-weight: 500;
        }

        /* Scrollbar styling */
        .detail-modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .detail-modal-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .detail-modal-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .detail-modal-content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media (max-width: 768px) {
            .detail-modal-content {
                width: 95%;
                max-height: 95vh;
            }
            
            .modal-close {
                width: 40px;
                height: 40px;
                font-size: 24px;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <?php include 'navigation-admin.php'; ?>
    
    <div class="main-content">
        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[#800000] mb-2">Student Verification</h1>
                <p class="text-gray-600">Review and verify student school ID submissions</p>
            </div>

            <?php if (empty($unverified_students)): ?>
                <div class="minimal-card rounded-2xl p-12 text-center">
                    <div class="text-green-500 mb-6">
                        <i class="fas fa-shield-check text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">All Students Verified</h3>
                    <p class="text-gray-500 max-w-md mx-auto">There are no pending student verifications at this time. All registered students have been reviewed and verified.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($unverified_students as $index => $student): ?>
                        <div class="minimal-card rounded-2xl p-6 cursor-pointer" onclick="openDetailModal(<?= $index ?>)">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate">
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-2">
                                        Grade <?= htmlspecialchars($student['grade_level']) ?> - <?= htmlspecialchars($student['section']) ?>
                                    </p>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock mr-1"></i>Pending Review
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <?php if ($student['school_id_image']): ?>
                                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                                        <img 
                                            src="data:<?= $student['school_id_mime_type'] ?>;base64,<?= base64_encode($student['school_id_image']) ?>" 
                                            alt="School ID Preview" 
                                            class="school-id-preview w-full rounded-lg"
                                        >
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-50 rounded-xl p-6 text-center">
                                        <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
                                        <p class="text-sm text-gray-500">No ID uploaded</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span><i class="fas fa-envelope mr-1"></i>Email provided</span>
                                <span class="text-[#800000] font-medium">Click to review</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <span class="modal-close" onclick="closeImageModal(); event.stopPropagation();">
            <i class="fas fa-times"></i>
        </span>
        <div class="modal-content">
            <img id="modalImage" src="" alt="School ID Full Size">
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="detail-modal" onclick="closeDetailModal(event)">
        <div class="detail-modal-content w-full max-w-2xl" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-[#800000]">Student Verification Details</h2>
                    <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-8 space-y-6" id="detailContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            
            <div class="p-6 border-t border-gray-200 bg-gradient-to-br from-gray-50 to-gray-100 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button 
                        id="verifyBtn"
                        class="flex-1 btn-verify text-white px-6 py-3.5 rounded-xl font-semibold flex items-center justify-center"
                    >
                        <i class="fas fa-shield-check mr-2"></i>Verify Student
                    </button>
                    <button 
                        id="rejectBtn"
                        class="flex-1 btn-reject text-white px-6 py-3.5 rounded-xl font-semibold flex items-center justify-center"
                    >
                        <i class="fas fa-times-circle mr-2"></i>Reject Application
                    </button>
                </div>
                <p class="text-xs text-gray-600 text-center mt-4 font-medium">
                    <i class="fas fa-info-circle mr-1"></i>Verified students can submit complaints and report lost items
                </p>
            </div>
        </div>
    </div>

    <script>
        // Store student data in JavaScript
        const studentsData = <?= json_encode(array_map(function($student) {
            // Convert binary image data to base64 for JavaScript
            if ($student['school_id_image']) {
                $student['school_id_image_base64'] = base64_encode($student['school_id_image']);
                unset($student['school_id_image']); // Remove binary data
            }
            return $student;
        }, $unverified_students)) ?>;
        
        let currentStudent = null;

        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function openDetailModal(studentIndex) {
            const student = studentsData[studentIndex];
            if (!student) {
                console.error('Student not found at index:', studentIndex);
                return;
            }
            
            currentStudent = student;
            
            const detailContent = document.getElementById('detailContent');
            const verifyBtn = document.getElementById('verifyBtn');
            const rejectBtn = document.getElementById('rejectBtn');
            
            // Populate modal content
            detailContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Personal Information</h3>
                            <div class="bg-white rounded-xl p-4 border border-gray-200 space-y-2">
                                <p class="text-sm"><span class="font-medium">Full Name:</span> ${student.first_name} ${student.middle_name || ''} ${student.last_name}</p>
                                <p class="text-sm"><span class="font-medium">Grade & Section:</span> Grade ${student.grade_level} - Section ${student.section}</p>
                                <p class="text-sm"><span class="font-medium">Address:</span> ${student.address || 'Not provided'}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h3>
                            <div class="bg-white rounded-xl p-4 border border-gray-200 space-y-2">
                                <p class="text-sm"><span class="font-medium">Email:</span> ${student.email}</p>
                                <p class="text-sm"><span class="font-medium">Phone:</span> ${student.phone_number}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Parent/Guardian</h3>
                            <div class="bg-white rounded-xl p-4 border border-gray-200 space-y-2">
                                <p class="text-sm"><span class="font-medium">Name:</span> ${student.parent_name}</p>
                                <p class="text-sm"><span class="font-medium">Contact:</span> ${student.contact_number}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">School ID Verification</h3>
                        <div class="bg-white rounded-xl p-4 border border-gray-200">
                            ${student.school_id_image_base64 ? `
                                <img 
                                    src="data:${student.school_id_mime_type};base64,${student.school_id_image_base64}" 
                                    alt="School ID" 
                                    class="w-full rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                    onclick="openImageModal(this.src)"
                                >
                                <p class="text-xs text-gray-500 mt-2 text-center">Click image to view full size</p>
                            ` : `
                                <div class="text-center py-8">
                                    <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                    <p class="text-sm text-gray-500">No school ID image uploaded</p>
                                </div>
                            `}
                        </div>
                    </div>
                </div>
            `;
            
            // Reset button state
            verifyBtn.disabled = false;
            rejectBtn.disabled = false;
            verifyBtn.innerHTML = '<i class="fas fa-shield-check mr-2"></i>Verify Student';
            rejectBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Reject Application';
            
            // Set up button event listeners
            verifyBtn.onclick = () => verifyStudent(student.id, true);
            rejectBtn.onclick = () => verifyStudent(student.id, false);
            
            document.getElementById('detailModal').style.display = 'block';
        }

        function closeDetailModal(event) {
            if (!event || event.target === document.getElementById('detailModal')) {
                document.getElementById('detailModal').style.display = 'none';
                currentStudent = null;
            }
        }

        function verifyStudent(studentId, isVerified) {
            const action = isVerified ? 'verify' : 'reject';
            const message = isVerified ? 
                'Are you sure you want to verify this student? They will be able to submit complaints and report lost items.' :
                'Are you sure you want to reject this student? They will not be able to submit complaints or report lost items.';
            
            if (confirm(message)) {
                // Show loading state
                const verifyBtn = document.getElementById('verifyBtn');
                const rejectBtn = document.getElementById('rejectBtn');
                
                verifyBtn.disabled = true;
                rejectBtn.disabled = true;
                verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

                // Send AJAX request
                fetch('../logic/verify_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        student_id: studentId,
                        is_verified: isVerified
                    })
                })
                .then(response => {
                    // Check if response is actually JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server returned non-JSON response. Status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Close modal
                        closeDetailModal();
                        
                        // Remove the card from the page
                        const cards = document.querySelectorAll('.minimal-card');
                        cards.forEach((card, index) => {
                            if (index === studentsData.findIndex(s => s.id === studentId)) {
                                card.style.transition = 'all 0.5s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'translateY(-20px)';
                                
                                setTimeout(() => {
                                    card.remove();
                                    
                                    // Remove from studentsData array
                                    studentsData.splice(studentsData.findIndex(s => s.id === studentId), 1);
                                    
                                    // Check if no more cards exist
                                    if (studentsData.length === 0) {
                                        location.reload(); // Reload to show "all verified" message
                                    }
                                }, 500);
                            }
                        });
                        
                        // Show success message
                        showNotification(
                            isVerified ? 'Student verified successfully!' : 'Student rejected successfully!',
                            isVerified ? 'success' : 'info'
                        );
                    } else {
                        // Re-enable buttons on error
                        verifyBtn.disabled = false;
                        rejectBtn.disabled = false;
                        verifyBtn.innerHTML = '<i class="fas fa-shield-check mr-2"></i>Verify Student';
                        rejectBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Reject Application';
                        
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Re-enable buttons on error
                    verifyBtn.disabled = false;
                    rejectBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="fas fa-shield-check mr-2"></i>Verify Student';
                    rejectBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Reject Application';
                    
                    showNotification('Error: ' + error.message, 'error');
                });
            }
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
            
            const bgColor = type === 'success' ? 'bg-green-500' : 
                           type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            notification.className += ` ${bgColor} text-white`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
                closeDetailModal();
            }
        });
    </script>
</body>
</html>