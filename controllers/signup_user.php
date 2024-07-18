<?php

require_once 'helpers/sanitize_functions.php';
require_once 'helpers/redirect_to_custom_error.php';
require_once 'helpers/connect_to_database.php'; 

class UserModel {
    private $db;

    public function __construct() {
        try{
        // Use the connect_to_database function to establish the database connection
            $this->db = connect_to_database();
        }catch(Exception $e){
            redirect_to_custom_error("Server Error","Unable to register user ");
        }

    }

    public function createUser($username, $employee_id, $hashedPassword, $registrationIp, $securityQuestion, $securityAnswer) {
        try{

            $username = sanitize_string($username);
            $securityAnswer = sanitize_string($securityAnswer);

            // Check if username or employee ID already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR employee_id = ?");
            $stmt->bind_param("si", $username, $employee_id);
            $stmt->execute();
            $stmt->store_result();

            // If username or employee ID exists, return false or throw an error
            if ($stmt->num_rows > 0) {
                return false; // Username or Employee ID already exists
            }

            // Hash the security answer
            $hashed_security_answer = password_hash($securityAnswer, PASSWORD_BCRYPT);

            // Prepare the SQL statement to insert the new user
            $stmt = $this->db->prepare("INSERT INTO users (username, employee_id, password, registration_ip, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissss", $username, $employee_id, $hashedPassword, $registrationIp, $securityQuestion, $hashed_security_answer);

            // Execute the statement
            $result = $stmt->execute();

            if ($result) {
                $is_admin = false; // 1 == true (admin) and 0 == false (not admin)
                // Prepare the SQL statement to insert the new user privilege
                $stmt = $this->db->prepare("INSERT INTO privilege (employee_id, is_admin) VALUES (?, ?)");
                $stmt->bind_param("is", $employee_id, $is_admin);
        
                // Execute the statement
                $stmt->execute();
            }

            // Close the statement
            $stmt->close();

            return $result;
        }catch(Exception $e){
            redirect_to_custom_error("Server Error","Unable to register user ");
        }

    }
}
?>