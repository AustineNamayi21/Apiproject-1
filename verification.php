<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'signup_details';
$username = 'root';
$password = 'root';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $otpCode = $data['otpCode'] ?? '';

    // Fetch user with matching OTP
    $stmt = $conn->prepare("SELECT * FROM userdata WHERE otp_code = :otp_code AND otp_expiration > NOW()");
    $stmt->bindParam(':otp_code', $otpCode);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Mark the user as verified
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $updateStmt = $conn->prepare("
            UPDATE userdata SET verified = 1, otp_code = NULL, otp_expiration = NULL WHERE user_id = :user_id
        ");
        $updateStmt->bindParam(':user_id', $user['user_id']);
        $updateStmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

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
    </style>
</head>
<body>
    <div class="container">
        <h1>OTP Verification</h1>
        <p id="error" class="error"></p>
        <form id="otpForm">
            <input type="text" id="otpCode" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>

    <script>
        document.getElementById('otpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const otpCode = document.getElementById('otpCode').value;

            try {
                const response = await fetch('verify_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ otpCode }),
                });

                const result = await response.json();

                if (result.success) {
                    alert('Verification successful! Redirecting...');
                    window.location.href = 'home.html';
                } else {
                    document.getElementById('error').textContent = result.message;
                }
            } catch (error) {
                console.error('Error verifying OTP:', error);
                document.getElementById('error').textContent = 'An error occurred. Please try again.';
            }
        });
    </script>
</body>
</html>
