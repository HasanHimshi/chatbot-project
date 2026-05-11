function sendMessage(){

    let input =
    document.getElementById("userInput").value;

    if(input==""){
        return;
    }

    let chatbox =
    document.getElementById("chatbox");

    let time =
    new Date().toLocaleTimeString();

    /* USER MESSAGE */

    chatbox.innerHTML += `

    <div class="user-row">

        <div class="user-message">

            ${input}

            <div class="time">${time}</div>

        </div>

        <div class="avatar user-avatar">
            👨
        </div>

    </div>

    `;

    /* TYPING ANIMATION */

    chatbox.innerHTML += `

    <div class="bot-row" id="typing">

        <div class="avatar bot-avatar">
            🤖
        </div>

        <div class="bot-message">

            <div class="typing">

                <span></span>
                <span></span>
                <span></span>

            </div>

        </div>

    </div>

    `;

    chatbox.scrollTop = chatbox.scrollHeight;

    fetch("chatbot.php",{

        method:"POST",

        headers:{
            "Content-Type":
            "application/x-www-form-urlencoded"
        },

        body:"message="+input

    })

    .then(response => response.text())

    .then(data => {

        /* REMOVE TYPING */

        document
        .getElementById("typing")
        .remove();

        /* BOT MESSAGE */

        chatbox.innerHTML += `

        <div class="bot-row">

            <div class="avatar bot-avatar">
                🤖
            </div>

            <div class="bot-message">

                ${data}

                <div class="time">${time}</div>

            </div>

        </div>

        `;

        /* VOICE OUTPUT */

        if(typeof speechEnabled !== "undefined" && speechEnabled){
    speak(data);
}

        /* CLEAR INPUT */

        document
        .getElementById("userInput")
        .value = "";

        /* AUTO SCROLL */

        chatbox.scrollTop =
        chatbox.scrollHeight;

    });
}

/* ENTER KEY */

document
.getElementById("userInput")

.addEventListener("keypress",

function(event){

    if(event.key==="Enter"){

        sendMessage();
    }
});

/* VOICE OUTPUT */
function speak(text){

    let speech = new SpeechSynthesisUtterance();

    speech.text = text;
    speech.lang = "en-US";
    speech.rate = 1;
    speech.pitch = 1;


    window.speechSynthesis.speak(speech);
}