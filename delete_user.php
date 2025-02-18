<?php
include('condb.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user
    $stmt = $conn->prepare("DELETE FROM userdata WHERE user_id = ?");
    $stmt->execute([$user_id]);

    header("Location: users.php");
    exit();
} else {
    die("Invalid request.");
}
?>
