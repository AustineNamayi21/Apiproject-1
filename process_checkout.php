<?php
session_start();
include('dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];

    try {
        $conn->beginTransaction();

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (total_amount, shipping_address) VALUES (:total_amount, :address)");
        $stmt->execute([
            ':total_amount' => array_sum(array_map(function ($item) {
                return $item['price'] * $item['quantity'];
            }, $_SESSION['cart'])),
            ':address' => $address
        ]);

        $order_id = $conn->lastInsertId();

        // Insert order items
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }

        $conn->commit();
        unset($_SESSION['cart']);
        echo "Order placed successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
