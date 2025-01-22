<?php
session_start();
include('connect.php');

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Validate the token
    $stmt = $connect->prepare("SELECT * FROM userdata WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('Invalid token.');</script>";
        exit();
    }

    if (isset($_POST["reset_password"])) {
        $new_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        // Update the password and clear the token
        $update_stmt = $connect->prepare("UPDATE userdata SET password = ?, token = NULL WHERE token = ?");
        $update_stmt->bind_param("ss", $new_password, $token);
        if ($update_stmt->execute()) {
            echo "<script>alert('Password reset successful.'); window.location.replace('login.php');</script>";
        } else {
            echo "<script>alert('Error resetting password. Please try again.');</script>";
        }
    }
} else {
    echo "<script>alert('No token provided.');</script>";
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>Reset Password</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Reset Password</a>
    </div>
</nav>

<main class="login-form mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Reset Your Password</div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="reset_password" class="btn btn-primary btn-block">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>