<?php
include '../controllers/admin_controller/admin_auth/db_connect.php'; // Adjust the path as needed

function handle_password_reset($username, $new_password, $security_question, $security_answer) {
    $conn = db_connect();

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
            $update_query = "UPDATE users SET hashed_pass=? WHERE username=?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $hashed_password, $username);
            if ($update_stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update the password. Please try again.'];
            }
        } else {
            return ['success' => false, 'message' => 'Security answer mismatch. Please try again.'];
        }
    } else {
        return ['success' => false, 'message' => 'User not found or security question mismatch. Please try again.'];
    }

    $stmt->close();
    $conn->close();
}
?>
