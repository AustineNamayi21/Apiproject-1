<?php
require 'dbconnection.php'; // Database connection
require 'vendor/autoload.php'; // For PDF generation (dompdf)

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // ✅ 1. Total Revenue
    $stmt = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM orders");
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

    // ✅ 2. Number of Orders
    $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;

    // ✅ 3. Average Order Value
    $stmt = $conn->query("SELECT AVG(total_amount) AS avg_order_value FROM orders");
    $avgOrderValue = $stmt->fetch(PDO::FETCH_ASSOC)['avg_order_value'] ?? 0;

    // ✅ 4. Top Selling Products (By Quantity Sold)
    $stmt = $conn->query("
        SELECT p.name AS product_name, SUM(oi.quantity) AS total_sold 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        GROUP BY oi.product_id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ 5. Monthly Revenue Trends
    $stmt = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS revenue
        FROM orders
        GROUP BY month
        ORDER BY month ASC
    ");
    $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ 6. Active Users (Users with the Most Orders)
    $stmt = $conn->query("
        SELECT u.username, COUNT(o.order_id) AS total_orders
        FROM orders o
        JOIN userdata u ON o.user_id = u.user_id
        GROUP BY o.user_id
        ORDER BY total_orders DESC
        LIMIT 5
    ");
    $activeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching analytics: " . $e->getMessage());
    die("Failed to load analytics. Please try again.");
}

// ✅ Generate PDF Report
if (isset($_GET['export_pdf'])) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($options);
    $html = '<h1>Tech Store - Orders Analytics Report</h1>';
    $html .= "<p><strong>Total Revenue:</strong> $" . number_format($totalRevenue, 2) . "</p>";
    $html .= "<p><strong>Total Orders:</strong> " . $totalOrders . "</p>";
    $html .= "<p><strong>Average Order Value:</strong> $" . number_format($avgOrderValue, 2) . "</p>";

    $html .= "<h2>Top Selling Products</h2><ul>";
    foreach ($topProducts as $product) {
        $html .= "<li>" . $product['product_name'] . " - " . $product['total_sold'] . " sold</li>";
    }
    $html .= "</ul>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("analytics_report.pdf", ["Attachment" => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <!-- Custom CSS -->
     <link rel="stylesheet" href="analytics.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Tech Store - Orders Analytics</h1>
        <a href="analytics.php?export_pdf=true" class="btn btn-danger mb-3">Export as PDF</a>

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

        <h3>Top Selling Products</h3>
        <ul>
            <?php foreach ($topProducts as $product): ?>
                <li><?= $product['product_name'] ?> - <?= $product['total_sold'] ?> sold</li>
            <?php endforeach; ?>
        </ul>

        <h3>Monthly Revenue Trends</h3>
        <canvas id="revenueChart"></canvas>

        <h3>Active Users</h3>
        <ul>
            <?php foreach ($activeUsers as $user): ?>
                <li><?= $user['username'] ?> - <?= $user['total_orders'] ?> orders</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        // Monthly Revenue Chart
        const revenueLabels = <?= json_encode(array_column($monthlyRevenue, 'month')) ?>;
        const revenueData = <?= json_encode(array_column($monthlyRevenue, 'revenue')) ?>;

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
