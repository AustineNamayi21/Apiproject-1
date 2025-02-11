<?php
session_start();
include('connect.php'); // Establish PDO connection as $conn
include('mailer.php'); // Include mailer functionality

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get and sanitize form input
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            header("Location: login.php");
            exit();
        }

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM userdata WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Store user details in the session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                // Log successful login
                error_log("Login successful for user ID: " . $_SESSION['user_id']);

                // Generate and send OTP
                $auth = new TwoFactorAuth($conn);
                $otp = $auth->updateOtp($email);
                if ($auth->sendOtpEmail($email, $user['username'], $otp)) {
                    // Redirect to the verification page
                    header("Location: verification.php?email=" . urlencode($email));
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to send OTP email.";
                    header("Location: login.php");
                    exit();
                }
            } else {
                $_SESSION['error'] = "Incorrect password.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "No account found with that email.";
            header("Location: login.php");
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $_SESSION['error'] = "Connection failed. Please try again.";
    header("Location: login.php");
    exit();
} catch (Exception $e) {
    error_log("An unexpected error occurred: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again.";
    header("Location: login.php");
    exit();
}
?>