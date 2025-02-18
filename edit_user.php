<?php
include('condb.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user details
    $stmt = $conn->prepare("SELECT username, email FROM userdata WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];

        $updateStmt = $conn->prepare("UPDATE userdata SET username = ?, email = ? WHERE user_id = ?");
        $updateStmt->execute([$username, $email, $user_id]);

        header("Location: users.php");
        exit();
    }
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit User</h1>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
