<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "signup_details";

// Create connection
$connect = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
} else {
    echo "Connected successfully";
}
?>
