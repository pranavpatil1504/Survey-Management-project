<?php
session_start();
require_once '../../../controllers/admin_controller/admin_auth/admin_session_check.php';
include '../../../controllers/helpers/connect_to_database.php';
include_once '../../controllers/helpers/redirect_to_custom_error.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    try{
        $userId = $_POST['id'];
    
        // Connect to database
        $conn = connect_to_database();
        
        // Delete from users table
        $sql_users = "DELETE FROM users WHERE id = ?";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("i", $userId);
        $stmt_users->execute();
        
        if ($stmt_users->affected_rows > 0) {
            $stmt_users->close();
            
            // Delete from privilege table
            $sql_privilege = "DELETE FROM privilege WHERE employee_id = ?";
            $stmt_privilege = $conn->prepare($sql_privilege);
            $stmt_privilege->bind_param("i", $userId);
            $stmt_privilege->execute();
            
            if ($stmt_privilege->affected_rows > 0) {
                $stmt_privilege->close();
                
                // Close connection
                $conn->close();
                
                // Redirect back to admin_dashboard.php?page=users after deletion
                header("Location: ../../admin_pages/admin_dashboard.php?page=users");
                exit;
            } else {
                // Handle case where deletion from privilege table fails
                $stmt_privilege->close();
            }
        } else {
            // Handle case where deletion from users table fails
            $stmt_users->close();
        }
        
        // Close connection
        $conn->close();
        
        // Redirect to admin_dashboard.php?page=users on failure
        header("Location: ../../admin_pages/admin_dashboard.php?page=users");
        exit;
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect server");
    }

} else {
    // Redirect to admin_dashboard.php?page=users if no POST data
    header("Location: ../../admin_pages/admin_dashboard.php?page=users");
    exit;
}
?>
