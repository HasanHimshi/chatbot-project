<?php
include "db.php";
include "auth.php";
requireAdmin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['bot_name','creator_name','welcome_message','gemini_model','api_limit_message','system_prompt'];
    foreach ($keys as $key) {
        setSetting($key, trim($_POST[$key] ?? ''));
    }

    $newUser = trim($_POST['admin_username'] ?? '');
    $newPass = $_POST['admin_password'] ?? '';
    if ($newUser !== '') {
        $stmt = mysqli_prepare($conn, "UPDATE admins SET username = ? WHERE id = ?");
        if ($stmt) {
            $adminId = (int)$_SESSION['admin_id'];
            mysqli_stmt_bind_param($stmt, "si", $newUser, $adminId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['admin_username'] = $newUser;
        }
    }
    if ($newPass !== '') {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE admins SET password_hash = ? WHERE id = ?");
        if ($stmt) {
            $adminId = (int)$_SESSION['admin_id'];
            mysqli_stmt_bind_param($stmt, "si", $hash, $adminId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    $msg = 'Settings saved successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Settings | Himshi AI EduBot</title><link rel="stylesheet" href="style.css"></head>
<body class="app-body page-settings">
<div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
<div class="site-shell">
<?php include "nav.php"; ?>
<main class="settings-layout">
    <section class="glass-panel settings-panel">
        <span class="mini-label">Bot Settings</span>
        <h1>Control the chatbot without editing code</h1>
        <p>Change the public name, creator identity, welcome message, model, and prompt from here.</p>
        <?php if ($msg !== '') { ?><div class="alert-box"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
        <form method="POST" class="settings-form">
            <div class="two-col">
                <div><label>Bot Name</label><input type="text" name="bot_name" value="<?php echo htmlspecialchars(getSetting('bot_name'), ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div><label>Creator Name</label><input type="text" name="creator_name" value="<?php echo htmlspecialchars(getSetting('creator_name'), ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>
            <label>Welcome Message</label>
            <textarea name="welcome_message" rows="4"><?php echo htmlspecialchars(getSetting('welcome_message'), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="two-col">
                <div><label>Gemini Model</label><input type="text" name="gemini_model" value="<?php echo htmlspecialchars(getSetting('gemini_model'), ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div><label>API Limit Message</label><input type="text" name="api_limit_message" value="<?php echo htmlspecialchars(getSetting('api_limit_message'), ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>
            <label>System Prompt</label>
            <textarea name="system_prompt" rows="7"><?php echo htmlspecialchars(getSetting('system_prompt'), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="settings-divider"></div>
            <h2>Admin Account</h2>
            <div class="two-col">
                <div><label>Admin Username</label><input type="text" name="admin_username" value="<?php echo htmlspecialchars(adminName(), ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div><label>New Password</label><input type="password" name="admin_password" placeholder="Leave blank to keep old password"></div>
            </div>
            <button class="btn primary" type="submit">Save Settings</button>
        </form>
    </section>
    <aside class="glass-panel admin-side">
        <span class="mini-label">Tips</span>
        <h2>Safer setup</h2>
        <p>Keep API keys outside JavaScript. Your project reads Gemini from the Windows environment variable GEMINI_API_KEY.</p>
        <p>After changing admin password, store it safely.</p>
        <a class="btn secondary full" href="dashboard.php">Back to Dashboard</a>
    </aside>
</main>
</div>
</body>
</html>
