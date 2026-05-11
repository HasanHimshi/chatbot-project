<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "sl_education_chatbot";

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn){
    die("Database Connection Failed");
}

?>