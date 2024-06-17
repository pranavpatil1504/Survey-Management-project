<?php
session_start();
include '../../../controllers/helpers/connect_to_database.php';

// Check if the admin is logged in
if (!isset($_SESSION['session_token'])) {
    header("Location: admin_signin.php");
    exit;
}

// Handle form submission to delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $userId = $_POST['id'];
    
    // Delete user from database
    $conn = connect_to_database();
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    // Redirect back to users.php or admin_dashboard.php after deletion
    header("Location: ?page=users");
    exit;
} else {
    // Redirect to users.php or admin_dashboard.php if ID is not provided
    header("Location: ?page=users");
    exit;
}
?>
