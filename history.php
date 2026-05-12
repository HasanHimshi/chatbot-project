<?php
include "db.php";
include "auth.php";
requireAdmin();

$msg = '';
if (isset($_POST['delete_history_id'])) {
    $id = intval($_POST['delete_history_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM chat_history WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        $msg = mysqli_stmt_execute($stmt) ? 'History item deleted.' : 'Delete failed.';
        mysqli_stmt_close($stmt);
    }
}
if (isset($_POST['clear_history'])) {
    mysqli_query($conn, "TRUNCATE TABLE chat_history");
    mysqli_query($conn, "TRUNCATE TABLE chat_messages");
    mysqli_query($conn, "TRUNCATE TABLE chat_sessions");
    $msg = 'All chat history cleared.';
}

$sourceFilter = $_GET['source'] ?? 'all';
$where = '';
if (in_array($sourceFilter, ['trained','default','ai','identity'], true)) {
    $safe = mysqli_real_escape_string($conn, $sourceFilter);
    $where = "WHERE source = '$safe'";
}
$history = mysqli_query($conn, "SELECT * FROM chat_history $where ORDER BY id DESC LIMIT 150");
$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM chat_history");
$total = $totalResult ? (int)(mysqli_fetch_assoc($totalResult)['total'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>History | Himshi AI EduBot</title><link rel="stylesheet" href="style.css"></head>
<body class="app-body page-history">
<div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
<div class="site-shell">
<?php include "nav.php"; ?>
<main class="history-layout">
    <section class="history-head glass-panel">
        <div>
            <span class="mini-label">Conversation Archive</span>
            <h1>Saved chat history</h1>
            <p>Search, filter, export, or delete saved conversations from the database.</p>
        </div>
        <div class="history-controls">
            <strong><?php echo $total; ?> saved chats</strong>
            <input type="text" id="historySearch" onkeyup="filterHistory()" placeholder="Search history...">
            <select onchange="window.location.href='history.php?source=' + this.value">
                <option value="all" <?php echo $sourceFilter==='all'?'selected':''; ?>>All sources</option>
                <option value="ai" <?php echo $sourceFilter==='ai'?'selected':''; ?>>AI</option>
                <option value="trained" <?php echo $sourceFilter==='trained'?'selected':''; ?>>Trained</option>
                <option value="default" <?php echo $sourceFilter==='default'?'selected':''; ?>>Default</option>
                <option value="identity" <?php echo $sourceFilter==='identity'?'selected':''; ?>>Identity</option>
            </select>
            <button class="btn secondary" onclick="exportHistoryText()">Export TXT</button>
            <form method="POST" onsubmit="return confirm('Clear all history?');"><button class="btn danger" name="clear_history" type="submit">Clear All</button></form>
        </div>
    </section>
    <?php if ($msg !== '') { ?><div class="alert-box"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
    <section id="historyList" class="history-list">
        <?php if ($history && mysqli_num_rows($history) > 0) { while ($row = mysqli_fetch_assoc($history)) { ?>
        <article class="history-card glass-panel">
            <div class="history-content">
                <div class="history-user"><b>User:</b> <?php echo nl2br(htmlspecialchars($row['user_message'], ENT_QUOTES, 'UTF-8')); ?></div>
                <div class="history-bot"><b>Bot:</b> <?php echo nl2br(htmlspecialchars($row['bot_reply'], ENT_QUOTES, 'UTF-8')); ?></div>
                <div class="history-time"><?php echo htmlspecialchars(($row['source'] ?? 'ai') . ' • ' . ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="item-actions">
                <button onclick='copyText(this, <?php echo json_encode($row["bot_reply"]); ?>)'>Copy Answer</button>
                <form method="POST" onsubmit="return confirm('Delete this history item?');"><input type="hidden" name="delete_history_id" value="<?php echo (int)$row['id']; ?>"><button class="danger" type="submit">Delete</button></form>
            </div>
        </article>
        <?php }} else { ?><div class="empty-state glass-panel">No history found.</div><?php } ?>
    </section>
</main>
</div>
<script src="script.js"></script>
</body>
</html>
