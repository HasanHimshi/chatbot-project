<?php
include "db.php";

$msg = "";

if(isset($_POST['submit'])){

    $keyword = strtolower($_POST['keyword']);
    $response = $_POST['response'];

    $sql = "INSERT INTO chatbot_training(keyword, response)
            VALUES('$keyword', '$response')";

    if(mysqli_query($conn, $sql)){
        $msg = "✅ Bot Trained Successfully!";
    }else{
        $msg = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<h2>Train Bot</h2>

<?php if($msg != "") echo $msg; ?>

<form method="POST">

    <input type="text" name="keyword" placeholder="Keyword" required>
    <br><br>

    <textarea name="response" placeholder="Response" required></textarea>
    <br><br>

    <button type="submit" name="submit">Training Bot</button>

</form>