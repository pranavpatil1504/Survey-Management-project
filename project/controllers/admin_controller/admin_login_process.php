<?php
session_start();

// Include the database connection function
include 'admin_auth/db_connect.php';

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $admin_name = $_POST['admin_name'];
    $password = $_POST['password'];

    // Create a new database connection
    $conn = db_connect();

    // Prepare and bind to fetch admin data
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE admin_name = ?");
    $stmt->bind_param("s", $admin_name);
    $stmt->execute();
    $stmt->store_result(); // Store the result to free the statement

    // Bind result variables
    $stmt->bind_result($admin_id, $hashed_password);

    // Fetch the result
    if ($stmt->fetch()) {
        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Generate a new session token
            $session_token = bin2hex(random_bytes(32));

            // Prepare update statement for session_token
            $update_stmt = $conn->prepare("UPDATE admins SET session_token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $session_token, $admin_id);
            $update_stmt->execute(); // Execute the update statement

            // Check if update was successful
            if ($update_stmt->affected_rows > 0) {
                // Store the session token in the session
                $_SESSION['session_token'] = $session_token;

                // Redirect to admin dashboard or desired page on successful login
                header("Location: ../../pages/admin_pages/admin_dashboard.php");
                exit;
            } else {
                // Handle update failure
                header("Location: ../../pages/admin_pages/admin_login.php?error=update_failed");
                exit;
            }
        } else {
            // Invalid credentials
            header("Location: ../../pages/admin_pages/admin_login.php?error=invalid_credentials");
            exit;
        }
    } else {
        // Invalid admin_name
        header("Location: ../../pages/admin_pages/admin_login.php?error=invalid_admin_name");
        exit;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect back to sign-in page if accessed directly
    header("Location: ../../pages/admin_pages/admin_login.php");
    exit;
}
?>
