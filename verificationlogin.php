<?php
header('Content-Type: text/html');

// Database connection
$host = 'localhost';
$dbname = 'signup_details';
$username = 'root';
$password = 'root';

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $otpCode = trim($_POST['otpCode'] ?? '');

        if (empty($otpCode)) {
            $response = ['success' => false, 'message' => 'OTP code is required.'];
        } else {
            // Fetch user with matching OTP
            $stmt = $conn->prepare("
                SELECT * FROM userdata 
                WHERE otp_code = :otp_code 
                AND otp_expiration > NOW()
            ");
            $stmt->bindParam(':otp_code', $otpCode);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Mark user as verified
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $updateStmt = $conn->prepare("
                    UPDATE userdata 
                    SET verified = 1, otp_code = NULL, otp_expiration = NULL 
                    WHERE user_id = :user_id
                ");
                $updateStmt->bindParam(':user_id', $user['user_id']);
                $updateStmt->execute();

                $response = ['success' => true, 'message' => 'Verification successful!'];
            } else {
                $response = ['success' => false, 'message' => 'Invalid or expired OTP.'];
            }
        }
    } catch (PDOException $e) {
        $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            text-align: center;
        }
        input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }
        button {
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>OTP Verification</h1>
        <?php if ($response): ?>
            <p class="<?php echo $response['success'] ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($response['message']); ?>
            </p>
            <?php if ($response['success']): ?>
                <script>
                    setTimeout(() => {
                        window.location.href = 'home.html';
                    }, 2000);
                </script>
            <?php endif; ?>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="otpCode" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>
