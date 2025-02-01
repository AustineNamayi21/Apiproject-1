<?php
$servername = "localhost"; // or your database host
$username = "root"; // your database username
$password = "root"; // your database password
$dbname = "signup_details"; // your database name

// Create connection
$connect = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
} else {
    echo "Connected successfully";
}
?>
