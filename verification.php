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
        $stmt = $this->conn->prepare("SELECT * FROM userdata WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyAndClearOTP($email, $verificationCode) {
        $user = $this->getUserByEmail($email);

        // Check if the OTP matches and is not expired
        if ($user && $user['otp_code'] === $verificationCode && strtotime($user['otp_expiration']) > time()) {
            // Mark the user as verified and clear the OTP fields
            $stmt = $this->conn->prepare("
                UPDATE userdata 
                SET is_verified = 1, otp_code = NULL, otp_expiration = NULL 
                WHERE email = :email
            ");
            $stmt->execute([':email' => $email]);
            return true;
        }
        return false;
    }
}

// Main Logic
$error = null; // Initialize error variable
if (isset($_GET['email'])) {
    $email = htmlspecialchars(trim($_GET['email']));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Initialize database connection
        $dbConnection = new DatabaseConnection('localhost', 'signup_details', 'root', 'root'); // Update credentials if necessary
        $conn = $dbConnection->connect();

        $otpVerification = new OTPVerification($conn);
        $verificationCode = htmlspecialchars(trim($_POST['verificationCode']));

        // Attempt to verify the OTP
        if ($otpVerification->verifyAndClearOTP($email, $verificationCode)) {
            // Redirect to home.html on successful verification
            header('Location: home.html');
            exit;
        } else {
            $error = "Invalid or expired verification code.";
        }
    }
} else {
    $error = "Invalid request. Email is missing.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        .container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .container form {
            display: flex;
            flex-direction: column;
        }
        .container input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .container button {
            padding: 10px;
            background: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .container button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>OTP Verification</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="verificationCode">Enter the OTP sent to your email:</label>
            <input type="text" id="verificationCode" name="verificationCode" placeholder="Enter OTP" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
