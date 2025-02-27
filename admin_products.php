<?php
session_start(); // Start the session

// Check if the admin is logged in
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    $_SESSION['error'] = "You must be logged in as an admin to access this page.";
    header("Location: admin_login.php");
    exit();
}

// Include database connection
include('dbconnection.php');

// Fetch all products from the database
try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Product Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Product Management</h1>

        <!-- Display Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        
        
            <?php endif; ?>
       

            <div class="content" style="text-align: center; padding: 20px; background-color: #f4f4f4; border-radius: 10px;">
    <ol id="toplist" style="list-style: none; padding: 0; display: flex; justify-content: center; gap: 15px;">
        <li class="left" style="display: inline; padding: 10px 15px; background-color:rgb(2, 25, 50); border-radius: 5px;">
            <a href="users.php" style="color: white; text-decoration: none; font-weight: bold;">User Management</a>
        </li>
        <li class="right" style="display: inline; padding: 10px 15px; background-color:rgb(5, 88, 25); border-radius: 5px;">
            <a href="orders.php" style="color: white; text-decoration: none; font-weight: bold;">Orders</a>
        </li>
        <li class="right" style="display: inline; padding: 10px 15px; background-color:rgb(118, 5, 16); border-radius: 5px;">
            <a href="analytics.php" style="color: white; text-decoration: none; font-weight: bold;">Analytics</a>
        </li>
        <li class="right" style="display: inline; padding: 10px 15px; background-color:rgb(118, 107, 5); border-radius: 5px;">
            <a href="refresh.php" style="color: white; text-decoration: none; font-weight: bold;">Refresh</a>
        </li>
        <li class="right" style="display: inline; padding: 10px 15px; background-color:rgb(171, 86, 6); border-radius: 5px;">
            <a href="admin_logout.php" style="color: white; text-decoration: none; font-weight: bold;">Log out</a>
        </li>
       
    </ol>
    <h1 style="color: #333; margin-top: 20px;">
        <strong>Austine's Tech City Kenya</strong>
    </h1>
</div>






        <!-- Add Product Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2>Add New Product</h2>
            </div>
            <div class="card-body">
                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="brand" class="form-label">Brand:</label>
                        <input type="text" id="brand" name="brand" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea id="description" name="description" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category:</label>
                        <input type="text" id="category" name="category" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image:</label>
                        <input type="file" id="image" name="image" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Product</button>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <h2 class="mt-4 mb-3">All Products</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" style="width: 100px;">
                            </td>
                            <td>
                                <a href="edit_product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>