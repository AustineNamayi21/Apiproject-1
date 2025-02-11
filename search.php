<?php
include('dbconnection.php'); // Database connection

session_start(); // Start session to manage the cart

try {
    // Capture search and filter inputs
    $query = htmlspecialchars(trim($_GET['query'] ?? ''));
    $category = htmlspecialchars(trim($_GET['category'] ?? ''));
    $min_price = htmlspecialchars(trim($_GET['min_price'] ?? ''));
    $max_price = htmlspecialchars(trim($_GET['max_price'] ?? ''));
    $brand = htmlspecialchars(trim($_GET['brand'] ?? ''));

    // Build SQL query dynamically based on inputs
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if (!empty($query)) {
        $sql .= " AND (name LIKE :query OR description LIKE :query)";
        $params[':query'] = '%' . $query . '%';
    }
    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }
    if (!empty($min_price)) {
        $sql .= " AND price >= :min_price";
        $params[':min_price'] = $min_price;
    }
    if (!empty($max_price)) {
        $sql .= " AND price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    if (!empty($brand)) {
        $sql .= " AND brand = :brand";
        $params[':brand'] = $brand;
    }

    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View all Products</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">ALL PRODUCTS</h1>

        <!-- Display products -->
        <?php if (!empty($products)): ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="card-text"><strong>Price:</strong> $<?= htmlspecialchars($product['price']) ?></p>
                                <p class="card-text"><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
                                <button class="btn btn-success add-to-cart" 
                                    data-id="<?= $product['product_id'] ?>" 
                                    data-name="<?= htmlspecialchars($product['name']) ?>" 
                                    data-price="<?= $product['price'] ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No products found matching your criteria.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // AJAX function to add product to the cart
        $(document).on('click', '.add-to-cart', function () {
            const productId = $(this).data('id');
            const productName = $(this).data('name');
            const productPrice = $(this).data('price');

            $.ajax({
                url: 'add_to_cart.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    product_name: productName,
                    product_price: productPrice
                },
                success: function (response) {
                    alert(response.message); // Display success message
                },
                error: function () {
                    alert('Error adding product to the cart. Please try again.');
                }
            });
        });
    </script>
</body>
</html>
