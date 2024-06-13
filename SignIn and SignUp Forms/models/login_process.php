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
        // Start session
        session_start();

        // Connect to the database
        $db = connect_to_database();

        // Generate a random session token
        $session_token = bin2hex(random_bytes(32)); // You can adjust the length of the token as needed
        
        // Set expiration time (e.g., 4sec from now)
        $expiration_time = time() + 4; // 4sec
        
        // Check if the session token exists for the user
        $check_sql = "SELECT * FROM user_session_token WHERE username='$username'";
        $check_result = $db->query($check_sql);

        if ($check_result->num_rows > 0) {
            // Session token already exists, update it
            $update_sql = "UPDATE user_session_token SET session_token='$session_token', expiration_time='$expiration_time' WHERE username='$username'";
        } else {
            // Session token doesn't exist, insert a new entry
            $update_sql = "INSERT INTO user_session_token (username, session_token, expiration_time) VALUES ('$username', '$session_token', '$expiration_time')";
        }

        if ($db->query($update_sql) === TRUE) {
            // Store session token and expiration time in the session
            $_SESSION['session_token'] = $session_token;
            $_SESSION['expiration_time'] = $expiration_time;
            $_SESSION['username'] = $username;

            // Redirect to homepage
            // header("Location: homepage.php");

            $session_data = base64_encode(json_encode(array(
                'session_token' => $session_token,
                'expiration_time' => $expiration_time,
                'username' => $username
            )));
    
            // Redirect to homepage with session data as query parameter
            $redirect_url = "../pages/home.php?session_data=$session_data";
            header("Location: $redirect_url");

            exit();
        } else {
            echo "Error: " . $update_sql . "<br>" . $db->error;
        }
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
            $sql = "INSERT INTO user_login_history (user_id, login_timestamp, ip_address, username) VALUES (?, CURRENT_TIMESTAMP, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $_SERVER['REMOTE_ADDR'],$username);
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