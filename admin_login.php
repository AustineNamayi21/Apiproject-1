<?php
session_start();
include('condb.php'); // Include the updated database connection file

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize inputs
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validate inputs
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Username and password are required.";
            header("Location: admin_login.php");
            exit();
        }

        // Fetch admin details from the database
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password (no hashing, direct comparison)
            if ($password === $admin['password']) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Store admin details in the session
                $_SESSION['id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];

                // Log successful login
                error_log("Admin login successful for username: " . $admin['username']);

                // Redirect to admin_products.php
                header("Location: admin_products.php");
                exit();
            } else {
                $_SESSION['error'] = "Incorrect password.";
                header("Location: admin_login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "No admin account found with that username.";
            header("Location: admin_login.php");
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $_SESSION['error'] = "Connection failed. Please try again.";
    header("Location: admin_login.php");
    exit();
} catch (Exception $e) {
    error_log("An unexpected error occurred: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again.";
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f6f9; /* Soft gray background */
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color:rgb(1, 17, 34);
            box-shadow: 0 0 5px rgba(1, 26, 53, 0.5);
        }
        .btn-primary {
            background-color:rgb(2, 26, 52);
            border-color:rgb(1, 23, 46);
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color:rgb(1, 26, 52);
            border-color:rgb(1, 23, 47);
        }
        .alert {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        .footer-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color:rgb(1, 23, 46);
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center">Admin Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="admin_login.php" class="mt-4">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <a href="#" class="footer-link">Forgot Password?</a>
    </div>
</body>
</html>