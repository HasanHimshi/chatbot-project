<?php include "auth.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Himshi AI EduBot | Home</title><link rel="stylesheet" href="style.css"></head>
<body class="app-body page-home">
<div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
<div class="site-shell">
<?php include "nav.php"; ?>
<header class="hero-section">
    <div class="hero-copy">
        <span class="mini-label">Education Counseling Assistant</span>
        <h1>Your smart <span class="gradient-text">AI study guide</span> for courses and careers.</h1>
        <p>Ask about O/L paths, A/L paths, IT careers, software engineering, diplomas, degrees, study skills, and future career decisions. The bot uses trained answers first and AI support when needed.</p>
        <div class="hero-actions"><a class="btn primary" href="chat.php">Start Chat</a><a class="btn secondary" href="training.php">Train Bot</a><a class="btn ghost" href="dashboard.php">Admin Dashboard</a></div>
    </div>
    <div class="hero-card glass-panel">
        <span class="mini-label">Live Preview</span>
        <div class="preview-chat">
            <div class="preview-message user">What course should I do after O/L?</div>
            <div class="preview-message bot">Tell me your interests first. If you like computers, IT, software engineering, or networking can be good paths.</div>
            <div class="preview-message user">Who made you?</div>
            <div class="preview-message bot">I was made by Himshi.</div>
        </div>
    </div>
</header>

<section class="quick-grid">
    <a class="quick-card" href="chat.php"><h3>💬 Chat</h3><p>Ask questions and get friendly education guidance.</p></a>
    <a class="quick-card" href="training.php"><h3>🧠 Training</h3><p>Add trusted answers that the bot checks before AI.</p></a>
    <a class="quick-card" href="history.php"><h3>📚 History</h3><p>Review saved questions, answers, and sources.</p></a>
    <a class="quick-card" href="settings.php"><h3>⚙️ Settings</h3><p>Change bot identity, prompt, and admin password.</p></a>
</section>

<section class="section-block" id="features">
    <div class="section-head"><div><span class="mini-label">Features</span><h2>Built like a real project</h2></div></div>
    <div class="features-grid">
        <div class="feature-card"><h3>AI fallback</h3><p>If no trained answer is found, AI helps with a clean reply.</p></div>
        <div class="feature-card"><h3>Chat memory</h3><p>Recent messages are saved and used for follow-up answers.</p></div>
        <div class="feature-card"><h3>Admin dashboard</h3><p>See stats, latest questions, popular topics, and feedback.</p></div>
        <div class="feature-card"><h3>Feedback</h3><p>Users can mark answers helpful or not helpful.</p></div>
    </div>
</section>

<section class="section-block">
    <div class="section-head"><div><span class="mini-label">How it works</span><h2>Simple answer flow</h2></div></div>
    <div class="steps-grid">
        <div class="step-card"><h3>1. Student asks</h3><p>The user sends a question from the chat page.</p></div>
        <div class="step-card"><h3>2. Database checks</h3><p>The bot checks default and trained answers first.</p></div>
        <div class="step-card"><h3>3. AI replies</h3><p>If needed, AI gives a friendly education answer and saves it.</p></div>
    </div>
</section>

<section class="stats-row">
    <div class="stat-card"><h3>24/7</h3><p>Local project access</p></div>
    <div class="stat-card"><h3>Admin</h3><p>Protected controls</p></div>
    <div class="stat-card"><h3>Memory</h3><p>Session chat saving</p></div>
    <div class="stat-card"><h3>Clean</h3><p>No Markdown output</p></div>
</section>

<section class="section-block" id="about">
    <div class="glass-panel" style="padding:30px">
        <span class="mini-label">About</span>
        <h2>Made for student guidance</h2>
        <p style="line-height:1.8;color:#c6d9ee">Himshi AI EduBot is a PHP and MySQL education chatbot project. It can be trained by an admin, remember recent conversation context, save history, and answer student-friendly questions. The chatbot identity is controlled so creator questions always answer: I was made by Himshi.</p>
    </div>
</section>

<section class="section-block" id="faq">
    <div class="section-head"><div><span class="mini-label">FAQ</span><h2>Common questions</h2></div></div>
    <div class="faq-grid">
        <div class="faq-card"><h3>Can I train it?</h3><p>Yes. Admin can add, edit, disable, or delete training answers.</p></div>
        <div class="faq-card"><h3>Does it save chats?</h3><p>Yes. Messages are saved in MySQL history and session tables.</p></div>
        <div class="faq-card"><h3>Who made it?</h3><p>This bot was made by Himshi.</p></div>
    </div>
</section>

<footer class="footer" id="contact"><span>© 2026 Himshi AI EduBot</span><span>Made by Himshi • PHP + MySQL + AI</span></footer>
</div>
</body>
</html>
