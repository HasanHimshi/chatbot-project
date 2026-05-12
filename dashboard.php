<?php
include "db.php";
include "auth.php";
requireAdmin();

function countRows($table) {
    global $conn;
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$table`");
    $row = $result ? mysqli_fetch_assoc($result) : ['total' => 0];
    return (int)($row['total'] ?? 0);
}

$totalTraining = countRows('chatbot_training');
$totalHistory = countRows('chat_history');
$totalSessions = countRows('chat_sessions');
$totalFeedback = countRows('feedback');
$recentQuestions = mysqli_query($conn, "SELECT user_message, bot_reply, source, created_at FROM chat_history ORDER BY id DESC LIMIT 8");
$popular = mysqli_query($conn, "SELECT user_message, COUNT(*) AS total FROM chat_history GROUP BY user_message ORDER BY total DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Himshi AI EduBot</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="app-body page-dashboard">
    <div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
    <div class="site-shell">
        <?php include "nav.php"; ?>
        <main class="admin-dashboard">
            <section class="dashboard-hero glass-panel">
                <span class="mini-label">Admin Dashboard</span>
                <h1>Welcome, <?php echo htmlspecialchars(adminName(), ENT_QUOTES, 'UTF-8'); ?>.</h1>
                <p>Monitor chats, training, feedback, and bot settings from one clean desktop panel.</p>
                <div class="hero-actions"><a class="btn primary" href="training.php">Manage Training</a><a class="btn secondary" href="settings.php">Bot Settings</a><a class="btn ghost" href="history.php">View History</a></div>
            </section>
            <section class="stats-row">
                <div class="stat-card"><h3><?php echo $totalTraining; ?></h3><p>Training Records</p></div>
                <div class="stat-card"><h3><?php echo $totalHistory; ?></h3><p>Saved Chats</p></div>
                <div class="stat-card"><h3><?php echo $totalSessions; ?></h3><p>Chat Sessions</p></div>
                <div class="stat-card"><h3><?php echo $totalFeedback; ?></h3><p>Feedback Items</p></div>
            </section>
            <section class="dashboard-grid">
                <div class="glass-panel dashboard-list">
                    <div class="list-head"><div><span class="mini-label">Latest</span><h2>Recent questions</h2></div></div>
                    <?php if ($recentQuestions && mysqli_num_rows($recentQuestions) > 0) { while ($row = mysqli_fetch_assoc($recentQuestions)) { ?>
                        <article class="dash-item">
                            <strong><?php echo htmlspecialchars($row['user_message'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <p><?php echo htmlspecialchars(mb_strimwidth($row['bot_reply'], 0, 140, '...'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <small><?php echo htmlspecialchars($row['source'] . ' • ' . $row['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </article>
                    <?php }} else { ?><div class="empty-state">No chat history yet.</div><?php } ?>
                </div>
                <div class="glass-panel dashboard-list">
                    <div class="list-head"><div><span class="mini-label">Popular</span><h2>Most asked</h2></div></div>
                    <?php if ($popular && mysqli_num_rows($popular) > 0) { while ($row = mysqli_fetch_assoc($popular)) { ?>
                        <article class="dash-item compact">
                            <strong><?php echo htmlspecialchars($row['user_message'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?php echo (int)$row['total']; ?> times</span>
                        </article>
                    <?php }} else { ?><div class="empty-state">No popular questions yet.</div><?php } ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
