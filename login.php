<?php
// Include the required files
include('dbconnection.php'); // Ensure this establishes a PDO connection as $conn
include('mailer.php'); // Include the mailer functionality

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the form input
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        // Prepare the SQL statement to check for matching email
        $stmt = $conn->prepare("SELECT * FROM userdata WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Fetch the user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Generate and send OTP using the mailer
                $auth = new TwoFactorAuth($conn);
                $otp = $auth->updateOtp($email);

                if ($auth->sendOtpEmail($email, $user['username'], $otp)) {
                    // Redirect to the verification page
                    header("Location: verification.php?email=" . urlencode($email));
                    exit();
                } else {
                    echo "Failed to send OTP email.";
                }
            } else {
                echo "Incorrect password.";
            }
        } else {
            echo "No account found with that email.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

