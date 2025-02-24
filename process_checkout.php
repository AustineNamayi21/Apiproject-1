<?php
session_start();
require 'dbconnection.php'; // Establish PDO connection as $conn
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to place an order.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['address'])) {
        $_SESSION['error'] = "Shipping address is required.";
        header("Location: cart.php");
        exit();
    }
    $address = trim($_POST['address']);

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Your cart is empty.";
        header("Location: cart.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        // Fetch user email
        $stmt = $conn->prepare("SELECT email FROM userdata WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_email = $user['email'];

        // Calculate total order amount
        $total_amount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $_SESSION['cart']));

        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount, shipping_address, payment_status) 
            VALUES (:user_id, :total_amount, :address, 'Pending')
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':total_amount' => $total_amount,
            ':address' => $address
        ]);
        $order_id = $conn->lastInsertId();

        // Insert order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (:order_id, :product_id, :quantity, :price)
        ");
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }

        $conn->commit();

        // Generate Invoice Email
        $subject = "Your Invoice - Order #$order_id";
        $message = "<h2>Thank you for your order!</h2>";
        $message .= "<p><strong>Order ID:</strong> $order_id</p>";
        $message .= "<p><strong>Shipping Address:</strong> $address</p>";
        $message .= "<p><strong>Total Amount:</strong> $" . number_format($total_amount, 2) . "</p>";
        $message .= "<h3>Order Details:</h3><ul>";

        foreach ($_SESSION['cart'] as $item) {
            $message .= "<li>{$item['quantity']} x Product ID: {$item['product_id']} - $" . number_format($item['price'], 2) . "</li>";
        }
        $message .= "</ul>";

        // Send Email Using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Change this to your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'austinamayi254@gmail.com'; // Your email
            $mail->Password = 'htdcvywvvyiaxpds'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Content
            $mail->setFrom('austinamayi254@gmail.com', 'orderconfirmation');
            $mail->addAddress($user_email);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $message;

            // Send Email
            $mail->send();

            $_SESSION['success'] = "Order placed successfully! Invoice sent to your email.";
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Order placed, but email failed to send.";
        }

        // Clear the cart
        unset($_SESSION['cart']);

        header("Location: order_confirmation.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Checkout error: " . $e->getMessage());
        $_SESSION['error'] = "Checkout failed. Please try again.";
        header("Location: cart.php");
        exit();
    }
}
?>
