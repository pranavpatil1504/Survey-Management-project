<?php
include '../controllers/admin_controller/admin_auth/db_connect.php'; // Adjust the path as needed
require_once 'helpers/sanitize_functions.php';
require_once 'helpers/redirect_to_custom_error.php';

function handle_password_reset($username, $new_password, $security_question, $security_answer) {
    try{
        $conn = db_connect();

        $username = sanitize_string($username);
    
        // Hash the security answer
        $hashed_security_answer = password_hash($security_answer, PASSWORD_BCRYPT);
    
        // Prepare the statement to check if user exists and security question/answer match
        $query = "SELECT * FROM users WHERE username=? AND security_question=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $security_question);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($security_answer, $user['security_answer'])) {
                // User found and security answer matches, update the password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_query = "UPDATE users SET password=? WHERE username=?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ss", $hashed_password, $username);
                if ($update_stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    return ['success' => true];
                } else {
                    $stmt->close();
                    $conn->close();
                    return ['success' => false, 'message' => 'Failed to update the password. Please try again.'];
                }
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Security answer mismatch. Please try again.'];
            }
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'User not found or security question mismatch. Please try again.'];
        }
        $stmt->close();
        $conn->close();
        
        
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to reset password");
    }
}
?>
