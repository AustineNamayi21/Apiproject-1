<?php
session_start();
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="checkout.css">

</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Checkout</h1>
        <form action="process_checkout.php" method="POST">
            <div class="mb-3">
                <label for="address" class="form-label">Shipping Address:</label>
                <textarea id="address" name="address" class="form-control" required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Place Order</button>
            </div>
        </form>
    </div>
</body>
</html>
