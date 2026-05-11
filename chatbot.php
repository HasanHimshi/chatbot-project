<?php
include "db.php";

$message = strtolower($_POST['message']);

$reply = "";

/* 1️⃣ NORMAL RESPONSES */
$sql = "SELECT * FROM chatbot_responses";
$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result)){

    if(strpos($message, strtolower($row['keywords'])) !== false){
        $reply = $row['response'];
        echo $reply;
        exit;
    }
}

/* 2️⃣ TRAINED RESPONSES (THIS CONNECTS BUTTON TO AI) */
$sql2 = "SELECT * FROM chatbot_training";
$result2 = mysqli_query($conn, $sql2);

while($row = mysqli_fetch_assoc($result2)){

    if(strpos($message, strtolower($row['keyword'])) !== false){
        $reply = $row['response'];
        echo "🤖 (Trained AI): " . $reply;
        exit;
    }
}

/* 3️⃣ UNKNOWN */
echo "Sorry, I don't know this yet. Please train me.";
?>