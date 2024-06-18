<?php
// Include the database connection function
require_once 'db_connect.php';

// Function to log debug messages
function debug_log($message) {
    error_log($message, 3, 'debug.log');
}

function validate_admin_session($session_token) {
    // Create a new database connection
    $conn = db_connect();
    if (!$conn) {
        debug_log('Database connection failed: ' . mysqli_connect_error());
        return false;
    }

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id FROM admins WHERE session_token = ?");
    if (!$stmt) {
        debug_log('Statement preparation failed: ' . $conn->error);
        $conn->close();
        return false;
    }
    $stmt->bind_param("s", $session_token);

    // Execute the statement
    if (!$stmt->execute()) {
        debug_log('Statement execution failed: ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    // Bind result variables
    $stmt->bind_result($admin_id);

    // Fetch the result
    $result = $stmt->fetch();

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return true if the session token matches, otherwise false
    return $result ? true : false;
}
?>
