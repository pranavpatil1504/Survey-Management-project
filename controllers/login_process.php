<?php
include "helpers/connect_to_database.php"; // Include database connection function
require_once "admin_controller/admin_login_process.php";
require_once "helpers/redirect_to_sqlerrorpage.php";
require_once "helpers/redirect_to_custom_error.php";
require_once "helpers/sanitize_functions.php";

function redirect_to_complques($authenticated, $username) {
    try {
        if ($authenticated) {
            // Start session
            session_start();

            // Connect to the database
            $db = connect_to_database();

            // Generate a random session token
            $session_token = bin2hex(random_bytes(32));

            // Set expiration time (e.g., 1 hour from now)
            $expiration_time = time() + 3600;

            // Check if the session token exists for the user
            $check_sql = "SELECT * FROM user_session_token WHERE username=?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                // Session token already exists, update it
                $update_sql = "UPDATE user_session_token SET session_token=?, expiration_time=? WHERE username=?";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->bind_param("sis", $session_token, $expiration_time, $username);
            } else {
                // Session token doesn't exist, insert a new entry
                $update_sql = "INSERT INTO user_session_token (username, session_token, expiration_time) VALUES (?, ?, ?)";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->bind_param("sss", $username, $session_token, $expiration_time);
            }

            if ($update_stmt->execute()) {
                // Store session token and expiration time in the session
                $_SESSION['session_token'] = $session_token;
                $_SESSION['expiration_time'] = $expiration_time;
                $_SESSION['username'] = $username;

                $check_already_submitted = "SELECT * FROM users_submitted WHERE username=?";
                $check_already_submitted_stmt = $db->prepare($check_already_submitted);
                $check_already_submitted_stmt->bind_param("s", $username);
                $check_already_submitted_stmt->execute();
                $check_already_submitted_result = $check_already_submitted_stmt->get_result();

                if ($check_already_submitted_result->num_rows > 0) {
                    redirect_to_custom_error("User Already Submitted the Response", "Please contact admin for further details");
                    exit();
                } else {
                    // Redirect to complques.php
                    header("Location: complques.php");
                    exit();
                }
            } else {
                redirect_to_custom_error("Session Error","Failed to update or insert session token.");
            }
        } else {
            // Authentication failed, handle accordingly
            redirect_to_custom_error("Invalid User","Authentication failed.");
        }
    } catch (Exception $e) {
        redirect_to_custom_error("Error","Please contact admin for further details");
        exit();
    }
}

function handle_login($employee_id, $password)
{   
    try{
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
                } else if ($is_admin == 0) {
                    // Regular user login
                    redirect_to_complques(true, $username); // Redirect to complques.php for non-admin users
                } else if ($is_admin == null || empty($is_admin)){
                    redirect_to_custom_error("Privileges Error", "Privilege for the current account aren't defined");
                }
            } else {
                // Incorrect password
                return ['error' => true, 'message' => 'Invalid employee ID or password.'];
            }
        } else {
            // User not found
            return ['error' => true, 'message' => 'User not found, please sign up!'];
        }
    }catch(Exception $e){
        redirect_to_sql_error_page("Server Error");
    }

}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'] ?? '';
    $password = $_POST['password'] ?? '';

    $employee_id = sanitize_numeric($employee_id);

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

function redirect_to_admin_dashboard($username)
{
    create_admin_session_token($username);
}
