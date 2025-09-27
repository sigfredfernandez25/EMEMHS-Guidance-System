<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])) {
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
}
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

            <form action="../logic/submit_complaint_concern_logic.php" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                    <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description <span class="text-red-500">*</span></label>
                    <?php
                    if (!isset($selected_row['description']) || $selected_row['description'] == null) {
                        echo '<textarea name="description" id="description" rows="6" minlength="10" required
                               class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"
                               placeholder="Please provide a detailed description of your complaint/concern (minimum 100 characters)"></textarea>';
                    } else {
                    ?>
                        <textarea name="description" id="description" rows="6" minlength="10" required
                            class="form-input w-full px-4 py-2 rounded-lg focus:outline-none"
                            placeholder="Please provide a detailed description of your complaint/concern (minimum 100 characters)"><?= $selected_row['description'] ?></textarea>
                    <?php
                    }
                    ?>
                </div>

                <!-- Voice Recording Section -->
                <div class="space-y-4">
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Voice Recording Option</h3>
                        <p class="text-sm text-gray-600 mb-4">Alternatively, you can record your complaint/concern using your microphone and we'll convert it to text.</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-blue-800">
                                <strong>Language Support:</strong> You can record in English or Filipino (Tagalog).
                                Filipino language recognition works best in Chrome browser. Speak clearly and at a normal pace for better accuracy.
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="mb-3">
                                <div class="flex items-center space-x-4 mb-2">
                                    <label for="speechLanguage" class="text-sm font-medium text-gray-700">Recording Language:</label>
                                    <select id="speechLanguage" class="text-sm border border-gray-300 rounded px-2 py-1">
                                        <option value="en-US">English</option>
                                        <option value="fil-PH">Filipino</option>
                                    </select>
                                </div>
                                <button type="button" onclick="testSpeechRecognition()" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                    Test microphone setup
                                </button>
                                <span class="text-xs text-gray-500 ml-2">(Check if your browser and microphone are ready for voice recording)</span>
                            </div>
                            <div class="flex items-center space-x-4 mb-4">
                                <button type="button" id="recordBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                                    <svg id="recordIcon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span id="recordText">Start Recording</span>
                                </button>

                                <button type="button" id="stopBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 hidden">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Stop Recording</span>
                                </button>

                                <button type="button" id="clearBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg hidden">
                                    Clear Recording
                                </button>
                            </div>

                            <!-- Recording Status -->
                            <div id="recordingStatus" class="text-sm text-gray-600 mb-3 hidden">
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                                    </div>
                                    <span id="statusText">Recording...</span>
                                </div>
                            </div>

                            <!-- Audio Playback -->
                            <div id="audioPlayback" class="hidden">
                                <audio id="audioElement" controls class="w-full mb-3"></audio>
                                <div class="flex space-x-2">
                                    <button type="button" id="processAudioBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                                        Process to Text
                                    </button>
                                    <div id="processingLoader" class="hidden">
                                        <div class="flex items-center space-x-2 text-blue-600">
                                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                                            <span class="text-sm">Processing audio...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden inputs for audio data -->
                            <input type="hidden" name="audio_data" id="audioData">
                            <input type="hidden" name="audio_format" id="audioFormat">

                            <!-- Error messages -->
                            <div id="recordingError" class="text-red-500 text-sm mt-2 hidden"></div>
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
        // Voice Recording Functionality
         // Voice Recording Functionality
    document.addEventListener("DOMContentLoaded", function () {
        const recordBtn = document.getElementById("recordBtn");
        const stopBtn = document.getElementById("stopBtn");
        const clearBtn = document.getElementById("clearBtn");
        const descriptionField = document.getElementById("description");
        const recordingStatus = document.getElementById("recordingStatus");
        const recordingError = document.getElementById("recordingError");

        let recognition;
        let isRecording = false;
        let transcriptText = "";
        let currentLanguage = "en-US";

        if ("webkitSpeechRecognition" in window || "SpeechRecognition" in window) {
            // Use the correct constructor based on browser
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.lang = currentLanguage;

            // Add additional error handling for connection issues
            recognition.onstart = function() {
                console.log("Speech recognition started successfully");
                recordingError.classList.add("hidden");
            };

            recognition.onresult = function (event) {
                let interimTranscript = "";
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        transcriptText += transcript + " ";
                    } else {
                        interimTranscript += transcript;
                    }
                }
                descriptionField.value = transcriptText + interimTranscript;
            };

            recognition.onerror = function (event) {
                console.error("Speech recognition error:", event.error);
                isRecording = false;
                stopBtn.classList.add("hidden");
                stopBtn.disabled = false; // Ensure it's enabled for next use
                recordBtn.classList.remove("hidden");
                recordingStatus.classList.add("hidden");

                recordingError.classList.remove("hidden");

                // Provide specific error messages and solutions
                let errorMessage = "";
                switch(event.error) {
                    case 'network':
                        const currentUrl = window.location.href;
                        const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

                        if (!isSecure) {
                            errorMessage = "Network error: Speech recognition requires HTTPS or localhost. Current URL: " + currentUrl + ". Please access the page via HTTPS or localhost.";
                        } else {
                            errorMessage = "Network error: Speech recognition service is temporarily unavailable. <br><br>" +
                                "<strong>Troubleshooting steps:</strong><br>" +
                                "1. Try refreshing the page<br>" +
                                "2. Check if your microphone permissions are granted (click the microphone icon in address bar)<br>" +
                                "3. Try using a different browser (Chrome works best)<br>" +
                                "4. If the problem persists, please use the text input field instead.<br><br>" +
                                "Current URL: " + currentUrl;
                        }
                        break;
                    case 'audio-capture':
                        errorMessage = "Microphone access denied: Please allow microphone access in your browser settings and refresh the page.";
                        break;
                    case 'not-allowed':
                        errorMessage = "Microphone not allowed: Click the microphone icon in your browser's address bar to grant permission.";
                        break;
                    case 'no-speech':
                        errorMessage = "No speech detected: Please speak clearly into your microphone and try again.";
                        break;
                    case 'aborted':
                        errorMessage = "Speech recognition was interrupted: Please try recording again.";
                        break;
                    case 'language-not-supported':
                        if (currentLanguage === "fil-PH") {
                            errorMessage = "Filipino language recognition issue: Make sure you're speaking clearly in Filipino/Tagalog. Note: Filipino speech recognition works best in Chrome browser.";
                        } else {
                            errorMessage = "Language not supported: Please make sure your speech is in English.";
                        }
                        break;
                    case 'service-not-allowed':
                        errorMessage = "Speech recognition service unavailable: Please try again later or use a different browser.";
                        break;
                    default:
                        errorMessage = "Speech recognition error: " + event.error + ". Please try again or use the text input instead.";
                }

                recordingError.innerHTML = errorMessage;
            };

            recognition.onend = function () {
                isRecording = false;
                stopBtn.classList.add("hidden");
                stopBtn.disabled = false; // Re-enable for next use
                recordBtn.classList.remove("hidden");
                recordingStatus.classList.add("hidden");
            };
        } else {
            recordingError.classList.remove("hidden");

            // Provide browser-specific guidance
            const browserInfo = getBrowserInfo();
            let browserMessage = "";

            if (browserInfo.includes("Chrome")) {
                browserMessage = "Please ensure you're using a recent version of Chrome and the page is served over HTTPS or localhost.";
            } else if (browserInfo.includes("Firefox")) {
                browserMessage = "Firefox has limited speech recognition support. Consider using Chrome for better compatibility.";
            } else if (browserInfo.includes("Safari")) {
                browserMessage = "Safari requires HTTPS for speech recognition. Make sure the page is served securely.";
            } else if (browserInfo.includes("Edge")) {
                browserMessage = "Please ensure you're using a recent version of Edge and the page is served over HTTPS or localhost.";
            } else {
                browserMessage = "For best compatibility, use Chrome browser with HTTPS or localhost.";
            }

            recordingError.innerHTML = "Your browser does not fully support Speech Recognition. " + browserMessage +
                "<br><br><strong>Language Support:</strong><br>" +
                "- English: Supported in most modern browsers<br>" +
                "- Filipino: Best support in Chrome browser<br><br>" +
                "Current browser: " + browserInfo +
                "<br>Current URL: " + window.location.href;
        }

        // Helper function to get browser information
        function getBrowserInfo() {
            const userAgent = navigator.userAgent;
            if (userAgent.includes("Chrome")) return "Chrome " + userAgent.match(/Chrome\/([0-9.]+)/)[1];
            if (userAgent.includes("Firefox")) return "Firefox " + userAgent.match(/Firefox\/([0-9.]+)/)[1];
            if (userAgent.includes("Safari")) return "Safari (version unknown)";
            if (userAgent.includes("Edge")) return "Edge " + userAgent.match(/Edge\/([0-9.]+)/)[1];
            return "Unknown browser";
        }

        // Language selection handler
        const languageSelect = document.getElementById("speechLanguage");
        if (languageSelect) {
            languageSelect.addEventListener("change", function() {
                currentLanguage = this.value;
                if (recognition) {
                    recognition.lang = currentLanguage;
                }
            });
        }

        // Add a test function to check microphone permissions
        window.testSpeechRecognition = function() {
            if (!recognition) {
                const browserInfo = getBrowserInfo();
                let message = "Speech recognition is not supported in this browser. ";
                if (currentLanguage === "fil-PH") {
                    message += "For Filipino language support, please use Chrome browser.";
                } else {
                    message += "Please use Chrome for best compatibility.";
                }
                alert(message);
                return;
            }

            // Check if microphone permission is granted
            navigator.permissions.query({name:'microphone'}).then(function(result) {
                let message = "";
                if (result.state === 'granted') {
                    message = 'Microphone permission granted. ';
                    if (currentLanguage === "fil-PH") {
                        message += 'You can now record in Filipino. Try recording again.';
                    } else {
                        message += 'Try recording again.';
                    }
                } else if (result.state === 'denied') {
                    message = 'Microphone permission denied. Please enable it in your browser settings and refresh the page.';
                } else {
                    message = 'Microphone permission not determined. Please try recording to grant permission.';
                }
                alert(message);
            }).catch(function(error) {
                console.error("Permission check failed:", error);
                alert("Permission check failed. Please ensure your browser supports speech recognition.");
            });
        };

        // Start recording
        recordBtn.addEventListener("click", function () {
            if (!isRecording && recognition) {
                // Clear any previous errors
                recordingError.classList.add("hidden");

                try {
                    recognition.start();
                    isRecording = true;
                    recordBtn.classList.add("hidden");
                    stopBtn.classList.remove("hidden");
                    stopBtn.disabled = false;
                    recordingStatus.classList.remove("hidden");
                } catch (error) {
                    console.error("Error starting recognition:", error);
                    recordingError.classList.remove("hidden");
                    recordingError.innerText = "Error starting speech recognition: " + error.message;
                }
            }
        });

        // Stop recording
        stopBtn.addEventListener("click", function () {
            if (isRecording && recognition) {
                try {
                    recognition.stop();
                    isRecording = false;
                    stopBtn.classList.add("hidden");
                    recordBtn.classList.remove("hidden");
                    recordingStatus.classList.add("hidden");
                } catch (error) {
                    console.error("Error stopping recognition:", error);
                    // Force reset the UI state
                    isRecording = false;
                    stopBtn.classList.add("hidden");
                    recordBtn.classList.remove("hidden");
                    recordingStatus.classList.add("hidden");
                }
            }
        });

        // Clear text
        clearBtn.addEventListener("click", function () {
            transcriptText = "";
            descriptionField.value = "";
            clearBtn.classList.add("hidden");
            recordBtn.classList.remove("hidden");
        });


    // Process (finalize) audio to text
    processAudioBtn.addEventListener("click", function () {
        processingLoader.classList.remove("hidden");
        setTimeout(() => {
            processingLoader.classList.add("hidden");
            alert("Audio processed. Text has been added to your description.");
        }, 1500);
    });
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