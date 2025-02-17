<?php
include('dbconnection.php'); // Establish PDO connection as $conn

try {
    // Fetch all orders
    $stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Function to fetch order items for a specific order
    function getOrderItems($conn, $order_id) {
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    die("Failed to load orders. Please try again.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Orders</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Total Amount</th>
                    <th>Shipping Address</th>
                    <th>Payment Status</th>
                    <th>Created At</th>
                    <th>Order Items</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                        <td><?= htmlspecialchars($order['user_id']) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><?= htmlspecialchars($order['shipping_address']) ?></td>
                        <td><?= htmlspecialchars($order['payment_status']) ?></td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td>
                            <ul>
                                <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                    <li>
                                        Product ID: <?= htmlspecialchars($item['product_id']) ?>, 
                                        Quantity: <?= htmlspecialchars($item['quantity']) ?>, 
                                        Price: $<?= number_format($item['price'], 2) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
