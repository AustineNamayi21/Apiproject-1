<?php
require 'dbconnection.php'; // Ensure this file contains your database connection using PDO

try {
    $conn->beginTransaction();
    
    // Get the total quantity of each product sold from order_items
    $stmt = $conn->prepare("SELECT product_id, SUM(quantity) AS total_sold FROM order_items GROUP BY product_id");
    $stmt->execute();
    $soldProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update product quantities
    foreach ($soldProducts as $product) {
        $updateStmt = $conn->prepare("UPDATE products SET quantity = GREATEST(quantity - :sold, 0) WHERE product_id = :product_id");
        $updateStmt->bindParam(':sold', $product['total_sold'], PDO::PARAM_INT);
        $updateStmt->bindParam(':product_id', $product['product_id'], PDO::PARAM_INT);
        $updateStmt->execute();
    }
    
    $conn->commit();
    
    // Display message and redirect after 2 seconds
    echo "<script>
        alert('Stock refreshed successfully!');
        window.location.href = 'admin_products.php';
    </script>";
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error updating product quantities: " . $e->getMessage();
}
?>
