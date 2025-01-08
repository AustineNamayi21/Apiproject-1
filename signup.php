<?php
// Database connection credentials
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "root";
$dbName = "signup_details";

try {
    // Establish a database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbName", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Capture form data
        $email = $_POST['email'] ?? null;
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;
        $phone = $_POST['phone'] ?? null;

        // Validate inputs
        if (empty($email) || empty($username) || empty($password) || empty($phone)) {
            echo "All fields are required!";
            exit();
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $checkStmt = $conn->prepare("SELECT email FROM userdata WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            echo "Email already exists!";
        } else {
            // Insert data
            $insertStmt = $conn->prepare("
                INSERT INTO userdata (email, username, password, phone) 
                VALUES (:email, :username, :password, :phone)
            ");
            $insertStmt->bindParam(':email', $email);
            $insertStmt->bindParam(':username', $username);
            $insertStmt->bindParam(':password', $passwordHash);
            $insertStmt->bindParam(':phone', $phone);
            
            if ($insertStmt->execute()) {
                echo "Signup successful!";
                header("Location: home.html");
                exit();
            } else {
                echo "Failed to insert data!";
            }
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
