<?php
include "db.php";
include "auth.php";
requireAdmin();

$msg = "";
$editItem = null;

if (isset($_POST['delete_training_id'])) {
    $deleteId = intval($_POST['delete_training_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM chatbot_training WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $deleteId);
        $msg = mysqli_stmt_execute($stmt) ? "Training deleted successfully." : "Delete failed: " . mysqli_error($conn);
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['toggle_training_id'])) {
    $toggleId = intval($_POST['toggle_training_id']);
    $stmt = mysqli_prepare($conn, "UPDATE chatbot_training SET status = IF(status='active','inactive','active') WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $toggleId);
        $msg = mysqli_stmt_execute($stmt) ? "Training status changed." : "Status update failed.";
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['save_training'])) {
    $keyword = trim(mb_strtolower($_POST['keyword'] ?? '', 'UTF-8'));
    $response = trim($_POST['response'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
    $editId = intval($_POST['edit_id'] ?? 0);

    if ($keyword === '' || $response === '') {
        $msg = "Keyword and response are required.";
    } else {
        $dupSql = $editId > 0 ? "SELECT id FROM chatbot_training WHERE keyword = ? AND id != ? LIMIT 1" : "SELECT id FROM chatbot_training WHERE keyword = ? LIMIT 1";
        $dupStmt = mysqli_prepare($conn, $dupSql);
        if ($dupStmt) {
            if ($editId > 0) mysqli_stmt_bind_param($dupStmt, "si", $keyword, $editId); else mysqli_stmt_bind_param($dupStmt, "s", $keyword);
            mysqli_stmt_execute($dupStmt);
            $dup = mysqli_stmt_get_result($dupStmt);
            if ($dup && mysqli_num_rows($dup) > 0) $msg = "Warning: this keyword already exists. Please edit the existing record or use a different keyword.";
            mysqli_stmt_close($dupStmt);
        }

        if ($msg === '') {
            if ($editId > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE chatbot_training SET keyword = ?, response = ?, category = ?, status = ? WHERE id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssi", $keyword, $response, $category, $status, $editId);
                    $msg = mysqli_stmt_execute($stmt) ? "Training updated successfully." : "Update failed: " . mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                }
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO chatbot_training (keyword, response, category, status) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $keyword, $response, $category, $status);
                    $msg = mysqli_stmt_execute($stmt) ? "Bot trained successfully." : "Insert failed: " . mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM chatbot_training WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $editItem = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);
    }
}

$trained = mysqli_query($conn, "SELECT * FROM chatbot_training ORDER BY id DESC LIMIT 100");
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM chatbot_training");
$countRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
$totalTraining = (int)($countRow['total'] ?? 0);
$activeResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM chatbot_training WHERE status='active'");
$activeTraining = $activeResult ? (int)(mysqli_fetch_assoc($activeResult)['total'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Training | Himshi AI EduBot</title><link rel="stylesheet" href="style.css"></head>
<body class="app-body page-training">
<div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
<div class="site-shell">
<?php include "nav.php"; ?>
<main class="admin-layout">
<section class="training-editor glass-panel">
    <span class="mini-label">Training Control</span>
    <h1><?php echo $editItem ? 'Edit trained answer' : 'Add trained answer'; ?></h1>
    <p>Training answers are checked before AI fallback. Use short clean keywords.</p>
    <?php if ($msg !== '') { ?><div class="alert-box"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
    <form method="POST" class="training-form">
        <input type="hidden" name="edit_id" value="<?php echo $editItem ? (int)$editItem['id'] : 0; ?>">
        <div class="two-col">
            <div><label>Keyword</label><input type="text" name="keyword" placeholder="software engineering" value="<?php echo htmlspecialchars($editItem['keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
            <div><label>Category</label><input type="text" name="category" placeholder="course / career / fees" value="<?php echo htmlspecialchars($editItem['category'] ?? 'general', ENT_QUOTES, 'UTF-8'); ?>"></div>
        </div>
        <label>Bot Response</label>
        <textarea name="response" rows="8" placeholder="Write the exact answer..." required><?php echo htmlspecialchars($editItem['response'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        <label>Status</label>
        <select name="status"><option value="active" <?php echo (($editItem['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo (($editItem['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option></select>
        <div class="form-actions"><button class="btn primary" type="submit" name="save_training"><?php echo $editItem ? 'Update Training' : 'Save Training'; ?></button><?php if ($editItem) { ?><a class="btn ghost" href="training.php">Cancel Edit</a><?php } ?><a class="btn secondary" href="chat.php">Test Chat</a></div>
    </form>
</section>
<aside class="admin-side glass-panel">
    <span class="mini-label">Overview</span>
    <h2><?php echo $activeTraining; ?> active / <?php echo $totalTraining; ?> total</h2>
    <p>Inactive records stay saved but will not be used by the chatbot.</p>
    <input type="text" id="trainingSearch" onkeyup="filterTraining()" placeholder="Search trained answers...">
    <button class="btn secondary full" onclick="exportTrainingText()">Export Training</button>
</aside>
<section class="training-list glass-panel">
    <div class="list-head"><div><span class="mini-label">Saved Training</span><h2>Training records</h2></div></div>
    <div id="trainingList" class="training-table">
        <?php if ($trained && mysqli_num_rows($trained) > 0) { while ($row = mysqli_fetch_assoc($trained)) { ?>
        <article class="training-item <?php echo ($row['status'] ?? 'active') === 'inactive' ? 'is-inactive' : ''; ?>">
            <div>
                <h3><?php echo htmlspecialchars($row['keyword'], ENT_QUOTES, 'UTF-8'); ?> <span><?php echo htmlspecialchars($row['category'] ?? 'general', ENT_QUOTES, 'UTF-8'); ?></span></h3>
                <p><?php echo nl2br(htmlspecialchars($row['response'], ENT_QUOTES, 'UTF-8')); ?></p>
                <small><?php echo htmlspecialchars(($row['status'] ?? 'active') . ' • ' . ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
            <div class="item-actions">
                <a href="training.php?edit=<?php echo (int)$row['id']; ?>">Edit</a>
                <form method="POST"><input type="hidden" name="toggle_training_id" value="<?php echo (int)$row['id']; ?>"><button type="submit"><?php echo ($row['status'] ?? 'active') === 'active' ? 'Disable' : 'Enable'; ?></button></form>
                <form method="POST" onsubmit="return confirm('Delete this trained answer?');"><input type="hidden" name="delete_training_id" value="<?php echo (int)$row['id']; ?>"><button class="danger" type="submit">Delete</button></form>
            </div>
        </article>
        <?php }} else { ?><div class="empty-state">No trained answers yet. Add your first training record.</div><?php } ?>
    </div>
</section>
</main>
</div>
<script src="script.js"></script>
</body>
</html>
