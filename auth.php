<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] > 0;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adminName() {
    return $_SESSION['admin_username'] ?? 'Admin';
}
?>
