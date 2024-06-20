<?php
// Include necessary files
include '../../../controllers/helpers/connect_to_database.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $edit_username = $_POST['edit_username'];
    $edit_employee_id = $_POST['edit_employee_id'];

    // Update user in the database
    $conn = connect_to_database();
    $sql = "UPDATE users SET username = ?, employee_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $edit_username, $edit_employee_id, $userId);
    $stmt->execute();

    // Check if update was successful
    if ($stmt->affected_rows > 0) {
        // Redirect back to users.php after update
        header("Location: ../../admin_pages/admin_dashboard.php?page=users");
        exit;
    } else {
        // Handle update (If employee ID already Exists)
        echo "Error: Employee ID Already Exists. Please try updating with a new employee ID!!";
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect back to users.php if accessed without proper submission
    header("Location: users.php");
    exit;
}

?>
