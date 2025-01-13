<?php
session_start();

class DatabaseConnection {
    private $host;
    private $dbname;
    private $username;
    private $password;
    public $conn;

    public function __construct($host, $dbname, $username, $password) {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->conn;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

class OTPVerification {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserById($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyOTP($userId, $verificationCode) {
        $user = $this->getUserById($userId);

        if ($user && $user['otp_code'] === $verificationCode && strtotime($user['otp_expiration']) > time()) {
            $this->clearOTP($userId);
            return true;
        }
        return false;
    }

    public function clearOTP($userId) {
        $stmt = $this->conn->prepare("UPDATE users SET otp_code = NULL, otp_expiration = NULL WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }
}

// Main Logic
if (!isset($_SESSION['user_id'])) {
    header('Location: login_process.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbConnection = new DatabaseConnection('localhost', 'assignmentii', 'root', '');
    $conn = $dbConnection->connect();

    $otpVerification = new OTPVerification($conn);
    $verificationCode = htmlspecialchars(trim($_POST['verification_code']));
    $userId = $_SESSION['user_id'];

    if ($otpVerification->verifyOTP($userId, $verificationCode)) {
        header('Location: dashboard_login.php');
        exit();
    } else {
        $error = "Invalid or expired verification code.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
            <h3 class="text-center mb-4">Verify Your Account</h3>
            <p class="text-muted text-center mb-4">
                Enter the 6-digit code sent to your email to verify your account.
            </p>
            <form action="verification_login.php" method="POST">
                <div class="mb-3">
                    <label for="verificationCode" class="form-label">Verification Code</label>
                    <input type="text" id="verificationCode" name="verification_code" 
                           class="form-control" placeholder="Enter code" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Verify</button>
                </div>
                <div class="text-center mt-3">
                    <a href="resend_code.php" class="text-decoration-none">Resend Code</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

