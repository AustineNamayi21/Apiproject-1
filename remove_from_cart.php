<?php
session_start();

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Ensure the cart exists and the product is in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]); // Remove the product from the cart
    }
}

// Redirect back to the shopping cart page
header("Location: cart.php");
exit();
?>
