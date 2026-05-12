let recognition;
let isVoiceActive = false;
let speechEnabled = false;

function getSpeechRecognition() {
    return window.SpeechRecognition || window.webkitSpeechRecognition || null;
}

function toggleVoice() {
    if (isVoiceActive) {
        stopVoice();
    } else {
        startVoice();
    }
}

function startVoice() {
    const SpeechRecognition = getSpeechRecognition();
    const button = document.getElementById("voiceBtn");

    if (!SpeechRecognition) {
        alert("Voice input is not supported in this browser. Try Google Chrome.");
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onstart = function () {
        isVoiceActive = true;
        if (button) {
            button.classList.add("active");
            button.textContent = "🛑 Stop";
        }
    };

    recognition.onresult = function (event) {
        const text = event.results[0][0].transcript;
        const input = document.getElementById("userInput");
        if (input) {
            input.value = text;
            sendMessage();
        }
    };

    recognition.onerror = function (event) {
        console.error("Voice error:", event.error);
    };

    recognition.onend = function () {
        isVoiceActive = false;
        if (button) {
            button.classList.remove("active");
            button.textContent = "🎤 Voice";
        }
    };

    recognition.start();
}

function stopVoice() {
    if (recognition) recognition.stop();
    isVoiceActive = false;
}

function toggleSpeech() {
    speechEnabled = !speechEnabled;
    const button = document.getElementById("speechBtn");
    if (!button) return;

    if (speechEnabled) {
        button.textContent = "🔊 On";
        button.classList.add("active");
    } else {
        button.textContent = "🔊 Off";
        button.classList.remove("active");
        if (window.speechSynthesis) window.speechSynthesis.cancel();
    }
}

function speak(text) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();

    const speech = new SpeechSynthesisUtterance(text);
    speech.lang = "en-US";
    speech.rate = 1;
    speech.pitch = 1;
    window.speechSynthesis.speak(speech);
}
