<?php
session_start();
include('connectdb.php');
require 'vendor/autoload.php'; // Include PHPMailer's autoloader if using Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST["recover"])) {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');</script>";
        exit();
    }

    // Check if the email exists
    $stmt = $connect->prepare("SELECT * FROM userdata WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('No account exists with this email');</script>";
    } elseif (!$user["status"]) {
        echo "<script>alert('Account not verified. Please verify your account first.');</script>";
    } else {
        // Generate a secure token
        $token = bin2hex(random_bytes(50));

        // Store the token in the database
        $update_stmt = $connect->prepare("UPDATE userdata SET token = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $token, $email);
        if ($update_stmt->execute()) {
            // Send reset email
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'austinamayi254@gmail.com'; // Your email
                $mail->Password = 'rmhaynrmyyvmfsym'; // Your email password or app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('austinamayi254@gmail.com', 'resetpassword');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Password Recovery";
                $mail->Body = "
                    <b>Dear User</b>,<br><br>
                    We received a request to reset your password. Click the link below to reset your password:
                    <br><br>
                    <a href='http://localhost/Apiproject-1/resetpswd.php?token=$token'>Reset Password</a>
                    <br><br>
                    If you did not request this, please ignore this email.<br><br>
                    Regards,<br>Your Application Team
                ";

                $mail->send();
                echo "<script>alert('Password reset link has been sent to your email.');</script>";
            } catch (Exception $e) {
                echo "<script>alert('Mail error: {$mail->ErrorInfo}');</script>";
            }
        } else {
            echo "<script>alert('Error updating token. Please try again later.');</script>";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>Password Recovery</title>
    <link rel="stylesheet" href="recoverpassword.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Password Recovery</a>
    </div>
</nav>

<main class="login-form mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Recover Your Password</div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="email">E-Mail Address</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <button type="submit" name="recover" class="btn btn-primary btn-block">Recover Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>