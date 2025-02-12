<?php
session_start();
include('dbconnection.php'); // Ensure this establishes a PDO connection as $conn

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to view your order confirmation.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the latest order details for the logged-in user
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([':user_id' => $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $order_id = $order['order_id'];
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "No recent order found.";
        header("Location: cart.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Order confirmation error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to retrieve order details. Please try again.";
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="orderconfirmation.css">
</head>
<body>

<div class="container mt-5">
        <!-- Logout Button -->
        <div class="text-end mb-4">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

    <div class="container mt-5">
        <h1 class="text-center">Order Confirmation</h1>
        <?php if ($order): ?>
            <div class="alert alert-success">
                <strong>Order placed successfully!</strong> Your order ID is <?= htmlspecialchars($order['order_id']) ?>.
            </div>
            <h2>Order Details</h2>
            <ul class="list-group mb-3">
                <li class="list-group-item"><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></li>
                <li class="list-group-item"><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></li>
                <li class="list-group-item"><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></li>
            </ul>
            <h2>Ordered Items</h2>
            <ul class="list-group">
                <?php foreach ($order_items as $item): ?>
                    <li class="list-group-item">
                        <strong>Product ID:</strong> <?= htmlspecialchars($item['product_id']) ?><br>
                        <strong>Quantity:</strong> <?= htmlspecialchars($item['quantity']) ?><br>
                        <strong>Price:</strong> $<?= number_format($item['price'], 2) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-danger">No order details available.</div>
        <?php endif; ?>
    </div>
</body>
</html>
