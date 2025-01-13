<?php
// Include the database connection file
include('dbconnection.php');
require 'vendor/autoload.php'; // Include PHPMailer's autoloader if using Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserRegistration {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function sanitizeInput($input) {
        return htmlspecialchars(trim($input));
    }

    public function isUserExists($email, $username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
        $stmt->execute([':email' => $email, ':username' => $username]);
        return $stmt->fetch() !== false;
    }

    public function registerUser($firstname, $lastname, $mobile, $username, $email, $password) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otp_expiration = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $stmt = $this->conn->prepare(
            "INSERT INTO users (firstname, lastname, mobile, username, email, password_hash, otp_code, otp_expiration) 
            VALUES (:firstname, :lastname, :mobile, :username, :email, :password_hash, :otp_code, :otp_expiration)"
        );

        $stmt->execute([
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':mobile' => $mobile,
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':otp_code' => $otp,
            ':otp_expiration' => $otp_expiration,
        ]);

        return $otp;
    }

    public function sendOtpEmail($recipientEmail, $recipientName, $otp) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'taonga.phiri@strathmore.edu';
            $mail->Password   = 'ycucituozciixvta'; // Replace with your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipient settings
            $mail->setFrom('from@example.com', 'BBIT Exempt');
            $mail->addAddress('taonga.phiri@strathmore.edu', 'Taonga Bheka');     //Add a recipient

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body    = "Your OTP code is: <strong>$otp</strong>. It expires in 5 minutes.";

            return $mail->send();
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userRegistration = new UserRegistration($conn);

    $firstname = $userRegistration->sanitizeInput($_POST['firstname']);
    $lastname = $userRegistration->sanitizeInput($_POST['lastname']);
    $mobile = $userRegistration->sanitizeInput($_POST['mobile']);
    $username = $userRegistration->sanitizeInput($_POST['username']);
    $email = $userRegistration->sanitizeInput($_POST['email']);
    $password = $userRegistration->sanitizeInput($_POST['password']);

    try {
        if ($userRegistration->isUserExists($email, $username)) {
            die("Email or username already exists.");
        }

        $otp = $userRegistration->registerUser($firstname, $lastname, $mobile, $username, $email, $password);

        if ($userRegistration->sendOtpEmail($email, "$firstname $lastname", $otp)) {
            header('Location: verification.php?email=' . urlencode($email));
            exit;
        } else {
            echo "Failed to send OTP email.";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}