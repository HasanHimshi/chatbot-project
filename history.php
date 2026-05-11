<?php

include 'db.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Chat History</title>

    <link rel="stylesheet"
          href="style.css">

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        🎓 SL Education AI
    </div>

    <ul>

        <li>
            <a href="index.php">Home</a>
        </li>

        <li>
            <a href="chat.php">Chat</a>
        </li>

        <li>
            <a href="history.php">History</a>
        </li>

        <li>
            <a href="traning.php">Training</a>
        </li>

    </ul>

</div>

<!-- HISTORY CONTAINER -->

<div class="history-page">

    <h1>
        💬 Chat History
    </h1>

    <!-- SEARCH -->

    <input type="text"
           id="searchInput"
           placeholder="Search history..."
           onkeyup="searchHistory()">

    <!-- HISTORY LIST -->

    <div id="historyList">

        <?php

        $result = mysqli_query($conn,

        "SELECT * FROM chat_history
         ORDER BY created_at DESC");

        while($row = mysqli_fetch_assoc($result)){

        ?>

        <div class="history-card">

            <div class="history-user">

                👨 <strong>User:</strong>

                <?php
                echo $row['user_message'];
                ?>

            </div>

            <div class="history-bot">

                🤖 <strong>Bot:</strong>

                <?php
                echo $row['bot_reply'];
                ?>

            </div>

            <div class="history-time">

                🕒

                <?php
                echo $row['created_at'];
                ?>

            </div>

            <!-- DELETE BUTTON -->

            <a href="delete.php?id=<?php
            echo $row['id'];
            ?>">

                <button class="delete-btn">

                    Delete

                </button>

            </a>

        </div>

        <?php } ?>

    </div>

</div>

<script>

function searchHistory(){

    let input =
    document.getElementById("searchInput")
    .value.toLowerCase();

    let cards =
    document.getElementsByClassName(
    "history-card");

    for(let i=0; i<cards.length; i++){

        let text =
        cards[i].innerText.toLowerCase();

        if(text.includes(input)){

            cards[i].style.display = "";

        }else{

            cards[i].style.display = "none";
        }
    }
}

</script>

</body>
</html>