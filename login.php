<?php
include "db.php";
include "auth.php";

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid admin username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Himshi AI EduBot</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="app-body page-login">
    <div class="orb orb-one"></div><div class="orb orb-two"></div><div class="orb orb-three"></div><div class="mesh-light"></div>
    <div class="site-shell">
        <?php include "nav.php"; ?>
        <main class="login-layout">
            <section class="glass-panel login-card">
                <span class="mini-label">Admin Access</span>
                <h1>Login to manage the bot</h1>
                <p>Use the admin panel to manage training, history, settings, and analytics.</p>
                <?php if ($error !== '') { ?><div class="alert-box error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
                <form method="POST" class="training-form">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="admin" required>
                    <label>Password</label>
                    <input type="password" name="password" placeholder="admin123" required>
                    <button class="btn primary full" type="submit">Login</button>
                </form>
                <div class="note-box">Default login: <b>admin</b> / <b>admin123</b>. Change it in Settings after login.</div>
            </section>
        </main>
    </div>
</body>
</html>
