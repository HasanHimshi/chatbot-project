let recognition;
let isVoiceActive = false;

// TOGGLE VOICE BUTTON
function toggleVoice(){

    let btn = document.getElementById("voiceBtn");

    if(isVoiceActive){
        stopVoice();
        btn.classList.remove("active");
        btn.innerText = "🎤 Voice";
    }else{
        startVoice();
        btn.classList.add("active");
        btn.innerText = "🛑 Stop";
    }
}

// START VOICE
function startVoice(){

    recognition = new webkitSpeechRecognition();

    recognition.lang = "en-US";
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onstart = function(){
        isVoiceActive = true;
        console.log("Voice ON");
    };

    recognition.onresult = function(event){

        let text = event.results[0][0].transcript;

        document.getElementById("userInput").value = text;

        sendMessage(); // send to chatbot
    };

    recognition.onend = function(){
        isVoiceActive = false;
        document.getElementById("voiceBtn").classList.remove("active");
        document.getElementById("voiceBtn").innerText = "🎤 Voice";
    };

    recognition.start();
}
//speech 

let speechEnabled = false;

function toggleSpeech(){

    speechEnabled = !speechEnabled;

    let btn = document.getElementById("speechBtn");

    if(speechEnabled){
        btn.innerText = "🔊 Voice ON";
        btn.style.background = "green";
    }else{
        btn.innerText = "🔊 Voice OFF";
        btn.style.background = "red";
    }
}

// speek 
function speak(text){

    let speech = new SpeechSynthesisUtterance();

    speech.text = text;
    speech.lang = "en-US";
    speech.rate = 1;
    speech.pitch = 1;

    window.speechSynthesis.speak(speech);
}

// STOP VOICE
function stopVoice(){
    if(recognition){
        recognition.stop();
    }
    isVoiceActive = false;
}