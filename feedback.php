<?php
include "db.php";
header("Content-Type: text/plain; charset=UTF-8");

$rating = trim($_POST['rating'] ?? '');
$userMessage = trim($_POST['user_message'] ?? '');
$botReply = trim($_POST['bot_reply'] ?? '');
$sessionKey = trim($_POST['session_key'] ?? '');

if (!in_array($rating, ['like', 'dislike'], true)) {
    http_response_code(400);
    echo "Invalid feedback.";
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO feedback (session_key, user_message, bot_reply, rating) VALUES (?, ?, ?, ?)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssss", $sessionKey, $userMessage, $botReply, $rating);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo "Feedback saved.";
    exit;
}

http_response_code(500);
echo "Feedback save failed.";
?>
