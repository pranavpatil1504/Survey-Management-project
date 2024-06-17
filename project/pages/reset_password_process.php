<?php
include '../controllers/admin_controller/admin_auth/db_connect.php'; // Adjust the path as needed

function handle_password_reset($username, $new_password, $security_question, $security_answer) {
    $conn = db_connect();

    // Prepare the statement to check if user exists and security question/answer match
    $query = "SELECT * FROM users WHERE username=? AND security_question=? AND security_answer=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $security_question, $security_answer);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User found, update the password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET hashed_pass=? WHERE username=?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $hashed_password, $username);
        if ($update_stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to update the password. Please try again.'];
        }
    } else {
        return ['success' => false, 'message' => 'User not found or security question/answer mismatch. Please try again.'];
    }

    $stmt->close();
    $conn->close();
}
?>
