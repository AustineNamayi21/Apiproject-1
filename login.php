<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "signup_details";

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the form input
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Prepare the SQL statement to check for matching email
        $stmt = $conn->prepare("SELECT * FROM userdata WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Fetch the user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if (password_verify($password, $user['password'])) {
                echo "Login successful. Welcome, " . htmlspecialchars($user['username']) . "!";
                // Redirect to a dashboard or homepage
                header('Location: home.html');
                exit();
            } else {
                echo "Incorrect password.";
            }
        } else {
            echo "No account found with that email.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
