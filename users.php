<?php
include('condb.php'); // Establish PDO connection as $conn

try {
    // Fetch only username, email, and password
    $stmt = $conn->query("SELECT user_id, username, email, password FROM userdata");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    die("Failed to load users. Please try again.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Users Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f4f6f9; /* Soft gray background */
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }
        .table thead th {
            background-color:rgb(1, 16, 32);
            color: white;
            font-weight: bold;
        }
        .btn-primary {
            background-color:rgb(2, 21, 42);
            border-color:rgb(3, 20, 39);
        }
        .btn-primary:hover {
            background-color:rgb(1, 19, 37);
            border-color:rgb(0, 20, 40);
        }
        .btn-danger {
            background-color:rgb(4, 104, 44);
            border-color:rgb(4, 104, 44);
        }
        .btn-danger:hover {
            background-color:rgb(4, 104, 44);
            border-color:rgb(4, 104, 44);
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Users Management</h1>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= str_repeat('*', strlen($user['password'])) ?> <!-- Mask password for security --> </td>
                        <td class="actions">
                            <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="delete_user.php?id=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>