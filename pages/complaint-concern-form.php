<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])) {
    echo "<script>alert('You are not logged in!!'); window.location.href = 'login.php';</script>";
}

// Check if student is verified
$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT is_verified FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$is_verified = $student && $student['is_verified'] == 1;

$selected_row = null;
$id = "0";
if (isset($_POST['user'])) {
    $id = $_POST['user'];
    $stmt = $pdo->prepare(SQL_LIST_COMPLAINTS_CONCERNS_BY_ID);
    $stmt->execute([$id]);
    $selected_row = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit A Complaint/Concern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .form-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
        }

        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .form-input:focus {
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }

        .preview-image {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .preview-image:hover {
            transform: scale(1.05);
        }

        /* Ensure buttons are clickable */
        button:not(:disabled) {
            cursor: pointer;
        }

        #stopBtn:not(:disabled):hover {
            transform: none; /* Remove transform on hover to prevent UI shifts */
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="form-container p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-[#800000] mb-8">Submit A Complaint/Concern</h1>

            <?php if (!$is_verified): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-2xl mt-1"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-red-800">Account Not Verified</h3>
                            <p class="text-red-700 mt-2">Your account is currently pending verification. You cannot submit complaints or concerns until your school ID has been verified by the guidance office.</p>
                            <p class="text-red-700 mt-2 font-medium">Please contact the guidance office for assistance with your verification.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="../logic/submit_complaint_concern_logic.php" method="POST" enctype="multipart/form-data" class="space-y-6" <?php echo !$is_verified ? 'style="opacity: 0.5; pointer-events: none;"' : ''; ?>>
                <div class="space-y-2">
                    <label for="complaint_type" class="block text-sm font-medium text-gray-700">Type of Complaint/Concern <span class="text-red-500">*</span></label>
                    <select name="complaint_type" id="complaint_type" required
                        class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        <?php
                        if (!isset($selected_row['type']) || $selected_row['type'] == null) {
                            echo "<option value=''>Select a type</option>";
                        } else {
                        ?>
                            <option value="<?= $selected_row['type'] ?>"><?= $selected_row['type'] ?></option>
                        <?php
                        }
                        ?>
                        <option value="bullying">Bullying</option>
                        <option value="family_problems">Family Problems</option>
                        <option value="academic_stress">Academic Stress</option>
                        <option value="mental_health">Mental Health Concerns</option>
                        <option value="peer_relationship">Peer Relationship Problems</option>
                        <option value="financial">Financial Problems</option>
                        <option value="physical_health">Physical Health Concerns</option>
                        <option value="romantic">Romantic Relationship Problems</option>
                        <option value="career">Career Guidance</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <div class="space-y-2" id="other_specify_group" style="display: none;">
                    <label for="other_specify" class="block text-sm font-medium text-gray-700">Specify Other Concern</label>
                    <input type="text" name="other_specify" id="other_specify"
                        class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label for="severity" class="block text-sm font-medium text-gray-700">Severity Level <span class="text-red-500">*</span></label>
                    <select name="severity" id="severity" required
                        class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                        <?php
                        $current_severity = $selected_row['severity'] ?? 'medium';
                        $severities = [
                            'low' => 'Low - General guidance needed',
                            'medium' => 'Medium - Moderate concern',
                            'high' => 'High - Urgent attention required',
                            'urgent' => 'Urgent - Immediate intervention needed'
                        ];

                        foreach ($severities as $value => $label) {
                            $selected = ($current_severity == $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select the severity level that best describes your situation</p>
                </div>

                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description</label>
                    <p class="text-xs text-gray-500 mb-2">You can provide a written description, an audio recording, or both.</p>
                    <?php
                    if (!isset($selected_row['description']) || $selected_row['description'] == null) {
                        echo '<textarea name="description" id="description" rows="6"
                               class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"
                               placeholder="Please provide a detailed description of your complaint/concern (optional if audio recording is provided)"></textarea>';
                    } else {
                    ?>
                        <textarea name="description" id="description" rows="6"
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"
                            placeholder="Please provide a detailed description of your complaint/concern (optional if audio recording is provided)"><?= $selected_row['description'] ?></textarea>
                    <?php
                    }
                    ?>
                </div>

                <!-- Audio Recording Section -->
                <div class="space-y-4">
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Audio Recording (Optional)</h3>
                        <p class="text-sm text-gray-600 mb-4">You can record an audio message (max 2 minutes) to provide additional context to your complaint/concern.</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-blue-800">
                                <strong>Note:</strong> Audio recording requires microphone access. Maximum recording time is 2 minutes. 
                                The recording will be stored securely and only accessible to guidance counselors.
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center space-x-4 mb-4">
                                <button type="button" id="recordBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Start Recording</span>
                                </button>

                                <button type="button" id="stopBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 hidden">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Stop Recording</span>
                                </button>

                                <button type="button" id="deleteAudioBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg hidden">
                                    Delete Recording
                                </button>
                            </div>

                            <!-- Recording Timer -->
                            <div id="recordingTimer" class="text-sm font-semibold mb-3 hidden">
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                                    </div>
                                    <span class="text-red-600">Recording: <span id="timerDisplay">0:00</span> / 2:00</span>
                                </div>
                            </div>

                            <!-- Audio Playback -->
                            <div id="audioPlayback" class="hidden">
                                <p class="text-sm text-green-600 mb-2">✓ Recording saved (Duration: <span id="audioDuration">0:00</span>)</p>
                                <audio id="audioElement" controls class="w-full mb-3"></audio>
                            </div>

                            <!-- Hidden inputs for audio data -->
                            <input type="hidden" name="audio_data" id="audioData">
                            <input type="hidden" name="audio_mime_type" id="audioMimeType">
                            <input type="hidden" name="audio_duration" id="audioDurationInput">

                            <!-- Error/Info messages -->
                            <div id="recordingError" class="text-red-500 text-sm mt-2 hidden"></div>
                            <div id="recordingInfo" class="text-blue-600 text-sm mt-2 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="evidence" class="block text-sm font-medium text-gray-700">Upload Evidence (optional)</label>

                    <?php if (!empty($selected_row['evidence'])): ?>
                        <div class="mb-4">
                            <img src="data:<?php echo $selected_row['mime_type']; ?>;base64,<?php echo base64_encode($selected_row['evidence']); ?>"
                                alt="Evidence"
                                class="preview-image max-w-xs" />
                        </div>
                        <input type="hidden" name="existing_evidence"
                            value="<?php echo base64_encode($selected_row['evidence']); ?>" />
                        <input type="hidden" name="existing_mime_type"
                            value="<?php echo htmlspecialchars($selected_row['mime_type']); ?>" />
                    <?php endif; ?>

                    <div class="flex items-center space-x-4">
                        <input type="file" name="evidence" id="evidence"
                            accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Accepted formats: Images (jpg, jpeg, png), PDF, Word documents (doc, docx)</p>
                </div>

                <div class="space-y-2">
                    <label for="counseling_date" class="block text-sm font-medium text-gray-700">Preferred Counseling Date (optional)</label>
                    <?php
                    if (!isset($selected_row['preferred_counseling_date']) || $selected_row['preferred_counseling_date'] == null) {
                        echo '<input type="date" name="counseling_date" id="counseling_date" 
                                   class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">';
                    } else {
                    ?>
                        <input type="date" name="counseling_date" id="counseling_date"
                            value="<?= $selected_row['preferred_counseling_date'] ?>"
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none">
                    <?php
                    }
                    ?>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Counseling sessions are available Monday-Friday during school hours (8:00 AM - 5:00 PM). Weekends are not available.
                    </p>
                </div>

                <input type="hidden" name="isUpdate" value="<?= $id ?>">

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        Submit Complaint/Concern
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script>
        // Audio Recording Functionality
        document.addEventListener("DOMContentLoaded", function () {
            const recordBtn = document.getElementById("recordBtn");
            const stopBtn = document.getElementById("stopBtn");
            const deleteAudioBtn = document.getElementById("deleteAudioBtn");
            const recordingTimer = document.getElementById("recordingTimer");
            const timerDisplay = document.getElementById("timerDisplay");
            const audioPlayback = document.getElementById("audioPlayback");
            const audioElement = document.getElementById("audioElement");
            const audioDuration = document.getElementById("audioDuration");
            const recordingError = document.getElementById("recordingError");
            const recordingInfo = document.getElementById("recordingInfo");
            
            const audioDataInput = document.getElementById("audioData");
            const audioMimeTypeInput = document.getElementById("audioMimeType");
            const audioDurationInput = document.getElementById("audioDurationInput");

            let mediaRecorder;
            let audioChunks = [];
            let recordingStartTime;
            let timerInterval;
            let audioBlob;
            const MAX_RECORDING_TIME = 120; // 2 minutes in seconds

            // Check browser support
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                recordingError.classList.remove("hidden");
                recordingError.innerHTML = "Your browser does not support audio recording. Please use a modern browser like Chrome, Firefox, or Edge.";
                recordBtn.disabled = true;
                return;
            }

            // Format time as MM:SS
            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            }

            // Update timer display
            function updateTimer() {
                const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                timerDisplay.textContent = formatTime(elapsed);
                
                // Warning at 1:30 (30 seconds left)
                if (elapsed === 90) {
                    recordingInfo.classList.remove("hidden");
                    recordingInfo.textContent = "⚠️ 30 seconds remaining";
                }
                
                // Auto-stop at 2 minutes
                if (elapsed >= MAX_RECORDING_TIME) {
                    stopRecording();
                    recordingInfo.classList.remove("hidden");
                    recordingInfo.textContent = "⏱️ Maximum recording time (2 minutes) reached";
                }
            }

            // Start recording
            recordBtn.addEventListener("click", async function () {
                try {
                    recordingError.classList.add("hidden");
                    recordingInfo.classList.add("hidden");
                    
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    
                    // Determine supported MIME type
                    let mimeType = 'audio/webm';
                    if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
                        mimeType = 'audio/webm;codecs=opus';
                    } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                        mimeType = 'audio/webm';
                    } else if (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')) {
                        mimeType = 'audio/ogg;codecs=opus';
                    } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                        mimeType = 'audio/mp4';
                    }
                    
                    mediaRecorder = new MediaRecorder(stream, { mimeType: mimeType });
                    audioChunks = [];
                    
                    mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            audioChunks.push(event.data);
                        }
                    };
                    
                    mediaRecorder.onstop = () => {
                        audioBlob = new Blob(audioChunks, { type: mimeType });
                        
                        // Check file size (max 5MB to be safe)
                        const fileSizeMB = audioBlob.size / (1024 * 1024);
                        if (fileSizeMB > 5) {
                            recordingError.classList.remove("hidden");
                            recordingError.textContent = `Recording is too large (${fileSizeMB.toFixed(2)}MB). Please record a shorter message.`;
                            deleteRecording();
                            return;
                        }
                        
                        const audioUrl = URL.createObjectURL(audioBlob);
                        audioElement.src = audioUrl;
                        audioPlayback.classList.remove("hidden");
                        deleteAudioBtn.classList.remove("hidden");
                        
                        // Calculate duration
                        const durationSeconds = Math.floor((Date.now() - recordingStartTime) / 1000);
                        audioDuration.textContent = formatTime(durationSeconds);
                        audioDurationInput.value = durationSeconds;
                        
                        // Convert to base64 for form submission
                        const reader = new FileReader();
                        reader.onloadend = () => {
                            const base64Audio = reader.result.split(',')[1];
                            audioDataInput.value = base64Audio;
                            audioMimeTypeInput.value = mimeType;
                        };
                        reader.readAsDataURL(audioBlob);
                        
                        // Stop all tracks
                        stream.getTracks().forEach(track => track.stop());
                    };
                    
                    mediaRecorder.start();
                    recordingStartTime = Date.now();
                    
                    // Update UI
                    recordBtn.classList.add("hidden");
                    stopBtn.classList.remove("hidden");
                    recordingTimer.classList.remove("hidden");
                    audioPlayback.classList.add("hidden");
                    
                    // Start timer
                    timerInterval = setInterval(updateTimer, 1000);
                    
                } catch (error) {
                    console.error("Error accessing microphone:", error);
                    recordingError.classList.remove("hidden");
                    
                    if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                        recordingError.textContent = "Microphone access denied. Please allow microphone access in your browser settings and refresh the page.";
                    } else if (error.name === 'NotFoundError') {
                        recordingError.textContent = "No microphone found. Please connect a microphone and try again.";
                    } else {
                        recordingError.textContent = "Error accessing microphone: " + error.message;
                    }
                }
            });

            // Stop recording
            function stopRecording() {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                    clearInterval(timerInterval);
                    
                    // Update UI
                    stopBtn.classList.add("hidden");
                    recordBtn.classList.remove("hidden");
                    recordingTimer.classList.add("hidden");
                }
            }

            stopBtn.addEventListener("click", stopRecording);

            // Delete recording
            function deleteRecording() {
                audioChunks = [];
                audioBlob = null;
                audioElement.src = "";
                audioPlayback.classList.add("hidden");
                deleteAudioBtn.classList.add("hidden");
                audioDataInput.value = "";
                audioMimeTypeInput.value = "";
                audioDurationInput.value = "";
                recordingInfo.classList.add("hidden");
                recordingError.classList.add("hidden");
            }

            deleteAudioBtn.addEventListener("click", function() {
                if (confirm("Are you sure you want to delete this recording?")) {
                    deleteRecording();
                }
            });

            // Form validation
            const form = document.querySelector("form");
            if (form) {
                form.addEventListener("submit", function(e) {
                    const description = document.getElementById("description").value.trim();
                    const hasAudio = audioDataInput.value !== "";
                    
                    if (!description && !hasAudio) {
                        e.preventDefault();
                        alert("Please provide either a written description or an audio recording.");
                        return false;
                    }
                });
            }
        });
        
        // Show/hide other specify field based on complaint type selection
        document
            .getElementById("complaint_type")
            .addEventListener("change", function() {
                const otherSpecifyGroup = document.getElementById("other_specify_group");
                if (this.value === "others") {
                    otherSpecifyGroup.style.display = "block";
                } else {
                    otherSpecifyGroup.style.display = "none";
                }
            });

        // Set minimum date and validate weekends for counseling date
        document.addEventListener("DOMContentLoaded", function() {
            const counselingDateInput = document.getElementById("counseling_date");

            if (counselingDateInput) {
                // Function to get next Monday
                function getNextWeekday(date) {
                    const nextDate = new Date(date);
                    const dayOfWeek = nextDate.getDay();

                    // Calculate days to add to get to next Monday
                    let daysToAdd = 0;
                    if (dayOfWeek === 0) { // Sunday
                        daysToAdd = 1;
                    } else if (dayOfWeek === 6) { // Saturday  
                        daysToAdd = 2;
                    }

                    nextDate.setDate(nextDate.getDate() + daysToAdd);
                    return nextDate;
                }

                // Get today's date, but skip to next weekday if today is weekend
                const today = new Date();
                let minDate = today;

                // If today is Saturday (6) or Sunday (0), set min to next Monday
                if (today.getDay() === 0 || today.getDay() === 6) {
                    minDate = getNextWeekday(today);
                }

                const yyyy = minDate.getFullYear();
                const mm = String(minDate.getMonth() + 1).padStart(2, "0");
                const dd = String(minDate.getDate()).padStart(2, "0");
                const minDateString = `${yyyy}-${mm}-${dd}`;

                // Set min attribute so past dates and weekends are disabled
                counselingDateInput.setAttribute("min", minDateString);

                // Validate selected date for weekends and past dates
                function validateWeekday(selectedValue) {
                    if (!selectedValue) return true; // Allow empty values

                    const selectedDate = new Date(selectedValue);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // Reset time to compare dates only
                    selectedDate.setHours(0, 0, 0, 0);

                    // Check if selected date is in the past
                    if (selectedDate < today) {
                        alert(
                            "Past dates are not allowed. Please select a current or future date."
                        );
                        counselingDateInput.value = "";
                        return false;
                    }

                    const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 6 = Saturday

                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        // Calculate next Monday - avoid timezone issues
                        const nextMonday = new Date(selectedDate);
                        if (dayOfWeek === 0) { // Sunday
                            nextMonday.setDate(nextMonday.getDate() + 1);
                        } else { // Saturday
                            nextMonday.setDate(nextMonday.getDate() + 2);
                        }

                        // Format date as YYYY-MM-DD without timezone issues
                        const year = nextMonday.getFullYear();
                        const month = String(nextMonday.getMonth() + 1).padStart(2, '0');
                        const day = String(nextMonday.getDate()).padStart(2, '0');
                        const nextMondayString = `${year}-${month}-${day}`;

                        alert(
                            "Weekend selected. Counseling sessions are only available on weekdays. " +
                            "Automatically changed to next available weekday: " +
                            nextMonday.toLocaleDateString("en-US", {
                                weekday: "long",
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                            })
                        );

                        counselingDateInput.value = nextMondayString;
                        return false;
                    }

                    return true;
                }

                // Add event listener to validate on date selection
                counselingDateInput.addEventListener("change", function() {
                    validateWeekday(this.value);
                });

                // Prevent form submission with invalid dates
                const form = counselingDateInput.closest("form");
                if (form) {
                    form.addEventListener("submit", function(e) {
                        if (counselingDateInput.value) {
                            const selectedDate = new Date(counselingDateInput.value);
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            selectedDate.setHours(0, 0, 0, 0);

                            // Check for past dates
                            if (selectedDate < today) {
                                e.preventDefault();
                                alert(
                                    "Past dates are not allowed. Please select a current or future date."
                                );
                                return;
                            }

                            // Check for weekends
                            if (!validateWeekday(counselingDateInput.value)) {
                                e.preventDefault();
                                alert("Please select a valid counseling date (weekdays only).");
                                return;
                            }
                        }
                    });
                }
            }
        });
    </script>
</body>

</html>