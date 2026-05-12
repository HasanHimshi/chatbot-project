<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function navActive($file) {
    global $currentPage;
    return $currentPage === $file ? 'active' : '';
}
?>
<nav class="main-nav">
    <a href="index.php" class="brand-block">
        <span class="brand-icon">🎓</span>
        <span><strong>Himshi AI EduBot</strong><small>Made by Himshi</small></span>
    </a>
    <ul class="nav-links">
        <li><a class="<?php echo navActive('index.php'); ?>" href="index.php">Home</a></li>
        <li><a class="<?php echo navActive('chat.php'); ?>" href="chat.php">Chat</a></li>
        <li><a class="<?php echo navActive('dashboard.php'); ?>" href="dashboard.php">Dashboard</a></li>
        <li><a class="<?php echo navActive('training.php'); ?>" href="training.php">Training</a></li>
        <li><a class="<?php echo navActive('history.php'); ?>" href="history.php">History</a></li>
        <li><a class="<?php echo navActive('settings.php'); ?>" href="settings.php">Settings</a></li>
        <li><a href="index.php#about">About</a></li>
        <li><a href="index.php#faq">FAQ</a></li>
        <?php if (function_exists('isAdminLoggedIn') && isAdminLoggedIn()) { ?>
            <li><a href="logout.php">Logout</a></li>
        <?php } else { ?>
            <li><a class="<?php echo navActive('login.php'); ?>" href="login.php">Admin</a></li>
        <?php } ?>
    </ul>
</nav>
