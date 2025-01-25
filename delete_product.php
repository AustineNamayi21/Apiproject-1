<?php
// Include database connection
include('dbconnection.php');

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is required.");
}

$product_id = intval($_GET['id']);

// Delete the product from the database
try {
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);

    header("Location: admin_products.php?success=Product deleted successfully.");
    exit();
} catch (Exception $e) {
    die("Error deleting product: " . $e->getMessage());
}
?>
