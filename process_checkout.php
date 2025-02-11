<?php
session_start();
include('dbconnection.php'); // Establish PDO connection as $conn

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to place an order.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Debugging: Check if user_id is set
if (!$user_id) {
    error_log("User ID is not set in the session.");
} else {
    error_log("User ID from session: " . $user_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the address input
    if (empty($_POST['address'])) {
        $_SESSION['error'] = "Shipping address is required.";
        header("Location: cart.php");
        exit();
    }
    $address = trim($_POST['address']);

    // Ensure cart is not empty before proceeding
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        $_SESSION['error'] = "Your cart is empty.";
        header("Location: cart.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        // Calculate total order amount
        $total_amount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $_SESSION['cart']));

        // Insert order with user_id
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
            if (!is_numeric($product_id) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                throw new Exception("Invalid cart data.");
            }
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }

        $conn->commit();

        // Clear the cart after successful checkout
        unset($_SESSION['cart']);

        // Log successful checkout
        error_log("Order placed successfully for user ID: " . $user_id);

        $_SESSION['success'] = "Order placed successfully!";
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