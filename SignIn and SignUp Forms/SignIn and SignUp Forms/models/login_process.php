<?php
// login_process.php

// Database connection function
function connect_to_database() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "app";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to handle login
function handle_login($email, $password) {
    $conn = connect_to_database();

    // Check login details from the users table
    $sql = "SELECT id, hashed_pass FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $hashed_password = $row['hashed_pass'];
        
        if (password_verify($password, $hashed_password)) {
            // Password is correct
            
            // Check if user exists in user_login_history
            $sql = "SELECT * FROM user_login_history WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                // User exists in user_login_history, update timestamp and IP address
                $sql = "UPDATE user_login_history SET login_timestamp = CURRENT_TIMESTAMP, ip_address = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $_SERVER['REMOTE_ADDR'], $user_id);
                $stmt->execute();
            } else {
                // User doesn't exist in user_login_history, insert new record
                $sql = "INSERT INTO user_login_history (user_id, login_timestamp, ip_address) VALUES (?, CURRENT_TIMESTAMP, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $user_id, $_SERVER['REMOTE_ADDR']);
                $stmt->execute();
            }
            
            // Redirect to welcome page
            header('Location: home.php');
            exit;
        } else {
            // Incorrect password
            return 'Incorrect password.';
        }
    } else {
        // User not found
        return 'User not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        $login_result = handle_login($email, $password);
        if ($login_result !== true) {
            // Login failed, add error message
            $errors['login'] = $login_result;
        }
    }
}
?>
