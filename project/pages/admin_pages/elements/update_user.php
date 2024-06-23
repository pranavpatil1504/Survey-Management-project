<?php
// Include necessary files
include '../../../controllers/helpers/connect_to_database.php';

session_start();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $edit_username = $_POST['edit_username'];
    $edit_employee_id = $_POST['edit_employee_id'];
    $old_employee_id = $_SESSION['OLD_EMP_ID'];

    // Update user in the database
    $conn = connect_to_database();
    
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Temporarily disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=0");

        // Update user details in users table
        $sql = "UPDATE users SET username = ?, employee_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $edit_username, $edit_employee_id, $userId);
        $stmt->execute();

        // Update employee_id in privilege table
        $sql = "UPDATE privilege SET employee_id = ? WHERE employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $edit_employee_id, $old_employee_id);
        $stmt->execute();

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");

        // Commit transaction
        $conn->commit();

        // Redirect back to users.php after update
        header("Location: ../../admin_pages/admin_dashboard.php?page=users");
        exit;
    } catch (mysqli_sql_exception $exception) {
        // Rollback transaction in case of error
        $conn->rollback();
        // Handle update error
        echo "Error: " . $exception->getMessage();
        exit;
    } finally {
        $stmt->close();
        $conn->close();
    }
} else {
    // Redirect back to users.php if accessed without proper submission
    header("Location: users.php");
    exit;
}
?>
