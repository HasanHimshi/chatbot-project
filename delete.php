<?php

include 'db.php';

$id = $_GET['id'];

mysqli_query($conn,

"DELETE FROM chat_history
WHERE id='$id'");

header("Location: history.php");

?>
