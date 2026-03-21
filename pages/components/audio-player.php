<!-- Audio Recording Section for Modal -->
<div class="bg-gray-50 p-4 rounded-lg" id="audioRecordingSection" style="display: none;">
    <h4 class="font-semibold text-[#800000] mb-4 flex items-center">
        <i class="fas fa-microphone mr-2"></i>
        Audio Recording
    </h4>
    <div class="bg-white p-3 rounded-md shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-600">Duration: <span id="audioRecordingDuration" class="font-medium text-gray-900">0:00</span></span>
            <a href="#" id="downloadAudioBtn" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1" download="complaint-audio">
                <i class="fas fa-download"></i>
                Download
            </a>
        </div>
        <audio id="audioRecordingPlayer" controls class="w-full">
            Your browser does not support the audio element.
        </audio>
    </div>
</div>

<script>
// Helper function to format audio duration
function formatAudioDuration(seconds) {
    if (!seconds || seconds === 0) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

// Helper function to display audio recording in modal
function displayAudioRecording(audioData, audioMimeType, audioDuration) {
    const audioSection = document.getElementById('audioRecordingSection');
    const audioPlayer = document.getElementById('audioRecordingPlayer');
    const audioDurationDisplay = document.getElementById('audioRecordingDuration');
    const downloadBtn = document.getElementById('downloadAudioBtn');
    
    if (audioData && audioMimeType) {
        // Show audio section
        audioSection.style.display = 'block';
        
        // Set audio source
        const audioSrc = `data:${audioMimeType};base64,${audioData}`;
        audioPlayer.src = audioSrc;
        
        // Set duration
        if (audioDuration) {
            audioDurationDisplay.textContent = formatAudioDuration(parseInt(audioDuration));
        }
        
        // Set download link
        downloadBtn.href = audioSrc;
        downloadBtn.download = `complaint-audio-${Date.now()}.${audioMimeType.split('/')[1]}`;
    } else {
        // Hide audio section if no audio
        audioSection.style.display = 'none';
    }
}
</script>
