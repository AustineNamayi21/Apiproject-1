<?php
$servername = "localhost";
$username = "root";
$password = "root";

try {
    // Creating the database connection
  $conn = new PDO("mysql:host=$servername;dbname=signup_details", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
  print '<br>';
$sql ="INSERT INTO userdata(email, username, password , phone) VALUES ('paulwema@gmail.com','Paul Wema', 'Pauloo#1824', '0721778328')";
  // use exec() because no results are returned
  $conn->exec($sql);
  echo "New record created successfully";

} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

?>