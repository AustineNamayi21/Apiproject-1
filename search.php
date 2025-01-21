<?php
include('dbconnection.php'); // Database connection

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

    // Display products
    if (!empty($products)) {
        foreach ($products as $product) {
            echo '<div class="card mb-3">';
            echo '<div class="row g-0">';
            echo '<div class="col-md-4">';
            echo '<img src="' . htmlspecialchars($product['image_url']) . '" class="img-fluid rounded-start" alt="' . htmlspecialchars($product['name']) . '">';
            echo '</div>';
            echo '<div class="col-md-8">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>';
            echo '<p class="card-text">' . htmlspecialchars($product['description']) . '</p>';
            echo '<p class="card-text"><strong>Price:</strong> $' . htmlspecialchars($product['price']) . '</p>';
            echo '<p class="card-text"><strong>Brand:</strong> ' . htmlspecialchars($product['brand']) . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>No products found matching your criteria.</p>';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
