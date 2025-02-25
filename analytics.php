<?php
include('dbconnection.php'); // Establish PDO connection as $conn

try {
    // Total Revenue
    $stmt = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM orders");
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

    // Number of Orders
    $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;

    // Average Order Value
    $stmt = $conn->query("SELECT AVG(total_amount) AS avg_order_value FROM orders");
    $avgOrderValue = $stmt->fetch(PDO::FETCH_ASSOC)['avg_order_value'] ?? 0;
} catch (PDOException $e) {
    error_log("Error fetching analytics: " . $e->getMessage());
    die("Failed to load analytics. Please try again.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="analytics.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Orders Analytics</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="card-text">$<?= number_format($totalRevenue, 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <p class="card-text"><?= $totalOrders ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Average Order Value</h5>
                        <p class="card-text">$<?= number_format($avgOrderValue, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>