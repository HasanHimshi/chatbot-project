<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>AI Chatbot</title>

    <link rel="stylesheet"
          href="style.css">

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        🎓 SL Education AI
    </div>

    <ul>

        <li>
            <a href="index.php">Home</a>
        </li>

        <li>
            <a href="chat.php">Chat</a>
        </li>

        <li>
            <a href="history.php">History</a>
        </li>

        <li>
            <a href="traning.php">Training</a>
        </li>

    </ul>

</div>

<!-- CHAT CONTAINER -->

<div class="chat-container">

    <!-- HEADER -->

    <div class="chat-header">

        🤖 Education Counseling AI Assistant

    </div>

    <!-- CHAT BOX -->

    <div id="chatbox">

        <div class="bot-row">

            <div class="avatar bot-avatar">
                🤖
            </div>

            <div class="bot-message">

                Hello 👋 <br>
                Welcome to SL Education AI Chatbot.

            </div>

        </div>

    </div>

    <!-- INPUT AREA -->

    <div class="input-area">

        <input type="text"
               id="userInput"
               placeholder="Ask your education question...">

        <button onclick="sendMessage()">
            Send
        </button>
        <button onclick="toggleSpeech()" id="speechBtn">🔊 Voice OFF</button>

        <button id="voiceBtn" onclick="toggleVoice()">🎤 Voice</button>

    </div>

</div>

<script src="script.js"></script>

<script src="voice.js"></script>

</body>
</html>