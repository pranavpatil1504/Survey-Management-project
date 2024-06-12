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

function redirect_to_homepage($authenticated, $username){
    if($authenticated == true){
        // Generate token
        $timestamp = time();
        $token = base64_encode($username . '|' . $timestamp); // Basic token generation, replace with encryption

        // Redirect to homepage with token in header
        header("Location: ../pages/home.php?token=$token");
        exit;
    } else {
        // Authentication failed, handle accordingly
        echo "Authentication failed.";
    }
}

// Function to handle login
function handle_login($email, $password) {
    $conn = connect_to_database();

    // Check login details from the users table
    $sql = "SELECT id, hashed_pass, username FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $hashed_password = $row['hashed_pass'];
        $username = $row['username'];
        
        if (password_verify($password, $hashed_password)) {
            // Password is correct
            
            // create new record
            $sql = "INSERT INTO user_login_history (user_id, login_timestamp, ip_address) VALUES (?, CURRENT_TIMESTAMP, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $_SERVER['REMOTE_ADDR']);
            $stmt->execute();
            
            // Authentication success
            $authenticated = true;
            redirect_to_homepage($authenticated, $username);
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