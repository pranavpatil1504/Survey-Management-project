<?php
require_once 'db_connect.php';

function validate_admin_session($session_token, $username) {
    $conn = db_connect();
    try{
        // Prepare SQL statement to fetch session_token for the given username
        $sql = "SELECT session_token FROM user_session_token WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);

        // Execute the query
        $stmt->execute();

        // Bind the result variables
        $stmt->bind_result($stored_session_token);

        // Fetch the result
        $stmt->fetch();

        // Close statement
        $stmt->close();

        // Close connection
        $conn->close();

        // Compare the stored session token with the provided session token
        if ($stored_session_token === $session_token) {
            return true; // Session is valid
        } 
        return false; // Session is not valid

    }catch(Exception $e){
        $conn->close();
        return false;
    }
}
?>
