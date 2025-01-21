<?php
$servername = "localhost";
$username = "root";
$password = "root";

try {
    // Creating the database connection
    $conn = new PDO("mysql:host=$servername;dbname=tech_store", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully<br>";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
