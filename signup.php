<?php
// Include the required files
include('dbconnection.php'); // Ensure this establishes a PDO connection as $conn
include('mailer.php'); // Include the mailer functionality

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Capture form data
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $username = htmlspecialchars(trim($_POST['username'] ?? ''));
        $password = htmlspecialchars(trim($_POST['password'] ?? ''));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));

        // Validate inputs
        if (empty($email) || empty($username) || empty($password) || empty($phone)) {
            die("All fields are required!");
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT email FROM userdata WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            die("Email already exists!");
        }

        // Insert the new user into the database
        $insertStmt = $conn->prepare("
            INSERT INTO userdata (email, username, password, phone, is_verified) 
            VALUES (:email, :username, :password, :phone, 0)
        ");
        $insertStmt->bindParam(':email', $email);
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':password', $passwordHash);
        $insertStmt->bindParam(':phone', $phone);

        if ($insertStmt->execute()) {
            // Generate and send OTP using the mailer
            $auth = new TwoFactorAuth($conn);
            $otp = $auth->updateOtp($email);

            if ($auth->sendOtpEmail($email, $username, $otp)) {
                header("Location: verification.php?email=" . urlencode($email));
                exit();
            } else {
                die("Failed to send OTP email.");
            }
        } else {
            die("Failed to insert user data.");
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
