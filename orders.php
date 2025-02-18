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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f6f9; /* Soft gray background */
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }
        .table thead th {
            background-color:rgb(1, 16, 33);
            color: white;
            font-weight: bold;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa; /* Light hover effect */
        }
        .order-items {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .order-items li {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .status-tag {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .status-pending {
            background-color: #ffc107;
            color: #333;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Orders</h1>
        <table class="table table-striped table-hover">
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
                        <td>
                            <?php
                            $status = htmlspecialchars($order['payment_status']);
                            if ($status === 'Pending') {
                                echo '<span class="status-tag status-pending">' . $status . '</span>';
                            } elseif ($status === 'Completed') {
                                echo '<span class="status-tag status-completed">' . $status . '</span>';
                            } elseif ($status === 'Cancelled') {
                                echo '<span class="status-tag status-cancelled">' . $status . '</span>';
                            } else {
                                echo htmlspecialchars($status);
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td>
                            <ul class="order-items">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>