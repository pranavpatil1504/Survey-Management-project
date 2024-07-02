<?php
include "helpers/connect_to_database.php"; // Include database connection function
require_once "admin_controller/admin_login_process.php";

function redirect_to_homepage($authenticated, $username)
{
    if ($authenticated) {
        // Start session
        session_start();

        // Connect to the database
        $db = connect_to_database();

        // Generate a random session token
        $session_token = bin2hex(random_bytes(32)); // You can adjust the length of the token as needed

        // Set expiration time (e.g., 4 sec from now)
        $expiration_time = time() + 3600; // 4 sec

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

function handle_login($employee_id, $password)
{
    $conn = connect_to_database();

    // Check login details from the users table
    $sql = "SELECT u.id, u.password, u.username, p.is_admin 
            FROM users u
            LEFT JOIN privilege p ON u.employee_id = p.employee_id
            WHERE u.employee_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $hashed_password = $row['password'];
        $username = $row['username'];
        $is_admin = $row['is_admin'];

        if (password_verify($password, $hashed_password)) {
            // Password is correct

            // Create new record in login history
            $insert_history_sql = "INSERT INTO user_login_history (user_id, login_timestamp, ip_address, username) 
                                   VALUES (?, CURRENT_TIMESTAMP, ?, ?)";
            $stmt_insert = $conn->prepare($insert_history_sql);
            $stmt_insert->bind_param("iss", $user_id, $_SERVER['REMOTE_ADDR'], $username);
            $stmt_insert->execute();

            // Check if user is admin
            if ($is_admin) {
                // Admin login
                redirect_to_admin_dashboard($username);
            } else {
                // Regular user login
                redirect_to_homepage(true, $username); // Assuming redirect_to_homepage() handles authenticated users
            }
        } else {
            // Incorrect password
            return ['error' => true, 'message' => 'Invalid employee ID or password.'];
        }
    } else {
        // User not found
        return ['error' => true, 'message' => 'User not found, please sign up!'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (!is_numeric($employee_id)) {
        $errors['employee_id'] = 'Please enter a valid employee ID.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        $login_result = handle_login($employee_id, $password);
        if (isset($login_result['error'])) {
            // Login failed, add error message
            $errors['login'] = $login_result['message'];
        }
    }
}

function redirect_to_admin_dashboard($username){
    create_admin_session_token($username);
}
