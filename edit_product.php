<?php
// Include database connection
include('dbconnection.php');

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is required.");
}

$product_id = intval($_GET['id']);

// Fetch the product data from the database
try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }
} catch (Exception $e) {
    die("Error fetching product: " . $e->getMessage());
}

// Update the product in the database if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price = floatval($_POST['price']);
    $category = htmlspecialchars(trim($_POST['category']));
    $quantity = intval($_POST['quantity']);
    $image_url = $product['image_url']; // Default to the existing image URL

    // Check if a new image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $upload_dir = 'uploads/';
        $image_path = $upload_dir . basename($image['name']);

        // Move uploaded file to the directory
        if (move_uploaded_file($image['tmp_name'], $image_path)) {
            $image_url = $image_path;
        } else {
            die("Failed to upload the image.");
        }
    }

    try {
        // Update product in the database
        $stmt = $conn->prepare("
            UPDATE products 
            SET name = :name, description = :description, price = :price, 
                category = :category, quantity = :quantity, image_url = :image_url 
            WHERE product_id = :id
        ");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':category' => $category,
            ':quantity' => $quantity,
            ':image_url' => $image_url,
            ':id' => $product_id,
        ]);

        header("Location: admin_products.php?success=Product updated successfully.");
        exit();
    } catch (Exception $e) {
        die("Error updating product: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Edit Product</h1>

        <div class="card">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category:</label>
                        <input type="text" id="category" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" value="<?php echo $product['quantity']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image:</label><br>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" style="width: 100px; margin-bottom: 10px;"><br>
                        <input type="file" id="image" name="image" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
