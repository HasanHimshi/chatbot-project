<?php
include "db.php";
include "auth.php";
$welcome = getSetting('welcome_message', 'Hello 👋 I am your education counseling assistant. Ask me anything about education.');
$botName = getSetting('bot_name', 'Himshi AI EduBot');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title><?php echo htmlspecialchars($botName, ENT_QUOTES, 'UTF-8'); ?> | Chat</title><link rel="stylesheet" href="style.css"></head>
<body class="app-body page-chat">
<div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
<div class="site-shell">
<?php include "nav.php"; ?>
<main class="chat-v8-shell">
    <section class="chat-main glass-panel">
        <header class="chat-topbar">
            <div>
                <span class="mini-label">Smart Education Chat</span>
                <h1><?php echo htmlspecialchars($botName, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p>Database training first, AI fallback second, memory saved in MySQL.</p>
            </div>
            <div class="top-actions">
                <button onclick="newChatScreen()">New Chat</button>
                <button onclick="exportChatText()">Export</button>
                <button id="themeBtn" onclick="toggleTheme()">🌙 Theme</button>
                <a href="history.php">History</a>
            </div>
        </header>

        <div class="topic-strip">
            <button onclick="quickAsk('What course should I do after O/L?')">After O/L</button>
            <button onclick="quickAsk('What course should I do after A/L?')">After A/L</button>
            <button onclick="quickAsk('What is software engineering?')">Software Engineering</button>
            <button onclick="quickAsk('How can I become a programmer?')">Programming</button>
            <button onclick="quickAsk('How to choose my career?')">Career Choice</button>
            <button onclick="quickAsk('What skills should I learn first?')">Skills</button>
        </div>

        <div id="chatbox" class="chat-window v8-chat-window">
            <div class="bot-row message-row">
                <div class="avatar bot-avatar">🤖</div>
                <div class="bot-message message-bubble">
                    <div class="message-text"><?php echo htmlspecialchars($welcome, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="message-actions">
                        <button class="copy-btn" onclick="copyText(this, <?php echo json_encode($welcome); ?>)">Copy</button>
                        <button class="copy-btn" onclick="insertPrompt('Can you help me choose a course?')">Try Prompt</button>
                    </div>
                    <div class="time">Ready now</div>
                </div>
            </div>
        </div>

        <div class="composer-panel v8-composer">
            <div class="composer-meta"><span id="connectionStatus">Ready</span><span><b id="charCount">0</b>/500</span></div>
            <div class="composer-box">
                <textarea id="userInput" maxlength="500" rows="1" placeholder="Type your question here..."></textarea>
                <button type="button" id="speechBtn" onclick="toggleSpeech()">🔊 Off</button>
                <button type="button" id="voiceBtn" onclick="toggleVoice()">🎤 Voice</button>
                <button type="button" id="sendBtn" class="send-btn" onclick="sendMessage()">Send</button>
            </div>
        </div>
    </section>

    <aside class="chat-command-panel glass-panel">
        <div class="assistant-card">
            <div class="assistant-avatar">🎓</div>
            <h2>Session Tools</h2>
            <p>Your session is saved locally and messages are saved in MySQL.</p>
        </div>
        <div class="side-section compact-actions">
            <h3>Actions</h3>
            <button onclick="regenerateLastAnswer()">↻ Regenerate Last</button>
            <button onclick="clearChat()">🧹 Clear Screen</button>
            <button onclick="quickAsk('Who made you?')">Identity Test</button>
            <button onclick="quickAsk('What did I ask before?')">Memory Test</button>
        </div>
        <div class="side-section">
            <h3>Categories</h3>
            <button onclick="quickAsk('Suggest IT career paths')">IT Careers</button>
            <button onclick="quickAsk('Explain diploma vs degree simply')">Diploma vs Degree</button>
            <button onclick="quickAsk('How to prepare for an interview?')">Interview Tips</button>
            <button onclick="quickAsk('Give me study tips')">Study Tips</button>
        </div>
    </aside>
</main>
</div>
<script src="script.js"></script>
<script src="voice.js"></script>
</body>
</html>
