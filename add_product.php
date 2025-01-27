<?php
// Include database connection
include('dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $brand = $_POST['brand']; // Fetch the brand input
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $image = $_FILES['image'];

    // Handle image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image["name"]);
    move_uploaded_file($image["tmp_name"], $target_file);

    // Insert product into the database
    try {
        $stmt = $conn->prepare("INSERT INTO products (name, brand, description, price, category, quantity, image_url) 
                                VALUES (:name, :brand, :description, :price, :category, :quantity, :image_url)");
        $stmt->execute([
            ':name' => $name,
            ':brand' => $brand,
            ':description' => $description,
            ':price' => $price,
            ':category' => $category,
            ':quantity' => $quantity,
            ':image_url' => $target_file,
        ]);

        header("Location: admin_products.php?success=Product added successfully.");
        exit();
    } catch (Exception $e) {
        die("Error adding product: " . $e->getMessage());
    }
}
?>
