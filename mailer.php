<?php
// Include the database connection file
include('connect.php'); // Ensure this file defines the $conn variable
require 'vendor/autoload.php'; // Include PHPMailer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TwoFactorAuth {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Sanitize user input
    public function sanitizeInput($input) {
        return htmlspecialchars(trim($input ?? ''));
    }

    // Generate OTP
    private function generateOtp() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Update OTP and expiration in the database
    public function updateOtp($email) {
        $otp = $this->generateOtp();
        $otpExpiration = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $stmt = $this->conn->prepare("
            UPDATE userdata 
            SET otp_code = :otp_code, otp_expiration = :otp_expiration 
            WHERE email = :email
        ");
        $success = $stmt->execute([
            ':otp_code' => $otp,
            ':otp_expiration' => $otpExpiration,
            ':email' => $email,
        ]);

        if (!$success) {
            throw new Exception("Failed to update OTP in the database.");
        }

        return $otp;
    }

    // Send OTP email
    public function sendOtpEmail($recipientEmail, $recipientName, $otp) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'austinamayi254@gmail.com'; // Your email
            $mail->Password   = 'xkacllgewnsmdrvl';         // Your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Sender & Recipient
            $mail->setFrom('austinamayi254@gmail.com', 'verification');
            $mail->addAddress($recipientEmail, $recipientName);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body    = "Your OTP code is: <strong>$otp</strong>. It expires in 5 minutes.";
            $mail->AltBody = "Your OTP code is: $otp. It expires in 5 minutes.";

            // Send email and return true if successful
            return $mail->send();
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}

// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure $conn is defined and passed
    if (!isset($conn)) {
        die("Database connection is not initialized.");
    }

    $auth = new TwoFactorAuth($conn);

    // Sanitize input
    $email = $auth->sanitizeInput($_POST['email'] ?? null);
    $username = $auth->sanitizeInput($_POST['username'] ?? null);

    // Validate input
    if (empty($email)) {
        die("Email is required.");
    }

    try {
        // Generate and update OTP for the user
        $otp = $auth->updateOtp($email);

        // Send the OTP to the user's email
        if ($auth->sendOtpEmail($email, $username, $otp)) {
            header('Location: verification.php?email=' . urlencode($email));
            exit();
        } else {
            echo "Failed to send OTP email.";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>
