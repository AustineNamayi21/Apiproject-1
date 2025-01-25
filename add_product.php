<?php
// Include database connection
include('dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Capture and sanitize inputs
        $name = htmlspecialchars(trim($_POST['name']));
        $description = htmlspecialchars(trim($_POST['description']));
        $price = htmlspecialchars(trim($_POST['price']));
        $category = htmlspecialchars(trim($_POST['category']));
        $quantity = htmlspecialchars(trim($_POST['quantity']));

        // Check if all required fields are filled
        if (empty($name) || empty($description) || empty($price) || empty($category) || empty($quantity)) {
            throw new Exception("All fields are required!");
        }

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = $_FILES['image'];
            $imageName = basename($image['name']);
            $imageTempPath = $image['tmp_name'];
            $imageSize = $image['size'];
            $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

            // Validate image file
            if (!in_array($imageExt, $allowedExts)) {
                throw new Exception("Only JPG, JPEG, PNG, and GIF files are allowed.");
            }
            if ($imageSize > 2 * 1024 * 1024) { // 2MB max size
                throw new Exception("Image size should not exceed 2MB.");
            }

            // Generate a unique name for the image
            $newImageName = uniqid('product_', true) . '.' . $imageExt;
            $uploadDir = 'uploads/';
            $uploadPath = $uploadDir . $newImageName;

            // Ensure the uploads directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Move the uploaded file to the uploads directory
            if (!move_uploaded_file($imageTempPath, $uploadPath)) {
                throw new Exception("Failed to upload the image.");
            }
        } else {
            throw new Exception("Product image is required.");
        }

        // Insert product into the database
        $stmt = $conn->prepare("
            INSERT INTO products (name, description, price, category, quantity, image_url)
            VALUES (:name, :description, :price, :category, :quantity, :image_url)
        ");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':category' => $category,
            ':quantity' => $quantity,
            ':image_url' => $uploadPath
        ]);

        // Redirect to the admin interface with a success message
        header("Location: admin_products.php?success=Product added successfully");
        exit();

    } catch (Exception $e) {
        // Handle errors
        $errorMessage = $e->getMessage();
        header("Location: admin_products.php?error=" . urlencode($errorMessage));
        exit();
    }
} else {
    // Redirect back if accessed without a POST request
    header("Location: admin_products.php");
    exit();
}
?>
