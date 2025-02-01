<?php
session_start();

// Ensure database connection is included
include('connectdb.php');

if (!isset($connect) || $connect->connect_error) {
    die("Database connection failed: " . ($connect->connect_error ?? "Unknown error"));
}

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Validate the token
    $stmt = $connect->prepare("SELECT * FROM userdata WHERE token = ?");
    if (!$stmt) {
        die("Database error: " . $connect->error);
    }

    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('Invalid token.'); window.location.replace('forgotpassword.php');</script>";
        exit();
    }

    if (isset($_POST["reset_password"])) {
        $new_password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        if ($new_password !== $confirm_password) {
            echo "<script>alert('Passwords do not match.');</script>";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password and clear token
            $update_stmt = $connect->prepare("UPDATE userdata SET password = ?, token = NULL WHERE token = ?");
            if (!$update_stmt) {
                die("Database error: " . $connect->error);
            }

            $update_stmt->bind_param("ss", $hashed_password, $token);
            if ($update_stmt->execute()) {
                echo "<script>alert('Password reset successful. Please login.'); window.location.replace('login.php');</script>";
                exit();
            } else {
                echo "<script>alert('Error resetting password. Please try again.');</script>";
            }
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
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
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
