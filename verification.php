<?php
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

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyAndClearOTP($email, $verificationCode) {
        $user = $this->getUserByEmail($email);

        if ($user && $user['otp_code'] === $verificationCode && strtotime($user['otp_expiration']) > time()) {
            $stmt = $this->conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiration = NULL WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return true;
        }
        return false;
    }
}

// Main Logic
if (isset($_GET['email'])) {
    $email = htmlspecialchars(trim($_GET['email']));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dbConnection = new DatabaseConnection('localhost', 'assignmentii', 'root', '');
        $conn = $dbConnection->connect();

        $otpVerification = new OTPVerification($conn);
        $verificationCode = htmlspecialchars(trim($_POST['verificationCode']));

        if ($otpVerification->verifyAndClearOTP($email, $verificationCode)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid or expired verification code.";
        }
    }
} else {
    $error = "Invalid request.";
}
?>
