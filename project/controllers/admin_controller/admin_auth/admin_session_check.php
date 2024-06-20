<?php
require_once 'db_connect.php';

function debug_log($message) {
    error_log($message, 3, 'debug.log');
}

function validate_admin_session($session_token) {
    $conn = db_connect();
    if (!$conn) {
        debug_log('Database connection failed: ' . mysqli_connect_error());
        return false;
    }

    $stmt = $conn->prepare("SELECT id FROM admins WHERE session_token = ?");
    if (!$stmt) {
        debug_log('Statement preparation failed: ' . $conn->error);
        $conn->close();
        return false;
    }
    $stmt->bind_param("s", $session_token);

    if (!$stmt->execute()) {
        debug_log('Statement execution failed: ' . $stmt->error);
        $stmt->close();
        $conn->close();
        return false;
    }

    $stmt->store_result();
    $result = $stmt->num_rows > 0; // Check if a row is found

    $stmt->close();
    $conn->close();

    return $result;
}



?>
