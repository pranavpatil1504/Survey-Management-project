<?php
require_once 'admin_auth/db_connect.php';

function create_admin_session_token($username) {
    session_start();
    $conn = db_connect(); // Assuming db_connect() function establishes database connection
    

    // Generate a new session token
    $session_token = bin2hex(random_bytes(32));
    $_SESSION['ADMIN_TOKEN'] = $session_token;
    $_SESSION['ADMIN_NAME'] = $username;

    // Check if the username already exists in user_session_token table
    $sql_check = "SELECT * FROM user_session_token WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Username already exists, update the session_token
        $sql_update = "UPDATE user_session_token SET session_token = ? WHERE username = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $session_token, $username);
        $stmt_update->execute();
        $stmt_check->close();
        if (isset($stmt_update)) {
            $stmt_update->close();
        }
    
        $conn->close();
        header("Location: ../pages/admin_pages/admin_dashboard.php?page=dashboard");
        exit;


    } else {
        // Username does not exist, insert new session_token
        $sql_insert = "INSERT INTO user_session_token (username, session_token) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ss", $username, $session_token);
        $stmt_insert->execute();
        $stmt_check->close();
        if (isset($stmt_update)) {
            $stmt_update->close();
        }
    
        $stmt_insert->close();
        $conn->close();
        header("Location: ../pages/admin_pages/admin_dashboard.php");
        exit;
    }


    // $stmt_check->close();
    if (isset($stmt_update)) {
        $stmt_update->close();
    }

    $stmt_insert->close();
    $conn->close();


}
?>
