<?php
// Include the required files
include('connect.php'); // Ensure this establishes a PDO connection as $conn
session_start();

try {
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize user inputs
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $otp_code = htmlspecialchars(trim($_POST['otp_code'] ?? ''));

        // Validate inputs
        if (empty($email) || empty($otp_code)) {
            $_SESSION['error'] = "Email and OTP code are required.";
            header("Location: verification.php?email=" . urlencode($email));
            exit();
        }

        // Retrieve the user's OTP and expiration from the database
        $stmt = $conn->prepare("
            SELECT otp_code, otp_expiration 
            FROM userdata 
            WHERE email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = "No account found with the provided email.";
            header("Location: verification.php");
            exit();
        }

        // Validate OTP
        $current_time = new DateTime();
        $otp_expiration = new DateTime($user['otp_expiration']);

        if ($user['otp_code'] !== $otp_code) {
            $_SESSION['error'] = "Invalid OTP code. Please try again.";
            header("Location: verification.php?email=" . urlencode($email));
            exit();
        }

        if ($current_time > $otp_expiration) {
            $_SESSION['error'] = "OTP code has expired. Please request a new one.";
            header("Location: verification.php?email=" . urlencode($email));
            exit();
        }

        // Mark the user as verified in the database
        $updateStmt = $conn->prepare("
            UPDATE userdata 
            SET verified = 1, otp_code = NULL, otp_expiration = NULL 
            WHERE email = :email
        ");
        $updateStmt->bindParam(':email', $email);

        if ($updateStmt->execute()) {
            $_SESSION['success'] = "Your account has been successfully verified.";
            header("Location: search.html");
            exit();
        } else {
            $_SESSION['error'] = "Failed to verify your account. Please try again.";
            header("Location: verification.php?email=" . urlencode($email));
            exit();
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: verification.php");
    exit();
} catch (Exception $e) {
    $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    header("Location: verification.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="verification.css">

</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="text-center mb-4">Verify Your OTP</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="verification.php">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                <div class="mb-3">
                    <label for="otp_code" class="form-label">OTP Code</label>
                    <input type="text" class="form-control" id="otp_code" name="otp_code" placeholder="Enter your OTP code" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>