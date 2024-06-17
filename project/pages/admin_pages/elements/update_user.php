<?php
// Include necessary files
include '../../../controllers/helpers/connect_to_database.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $edit_username = $_POST['edit_username'];
    $edit_email = $_POST['edit_email'];

    // Update user in the database
    $conn = connect_to_database();
    $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $edit_username, $edit_email, $userId);
    $stmt->execute();

    // Check if update was successful
    if ($stmt->affected_rows > 0) {
        // Redirect back to users.php after update
        header("Location: ../admin_dashboard.php");
        exit;
    } else {
        // Handle update (If users already Exists)
        echo "Error:404, User Already Exits. Please try updating with new username!!";
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect back to users.php if accessed without proper submission
    header("Location: ../admin_dashboard.php");
    exit;
}
?>
