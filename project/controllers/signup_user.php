<?php
class UserModel {
    private $db;

    public function __construct() {
        // Modify the connection details to match your database
        $host = 'localhost'; // Change this to your host
        $username = 'root'; // Change this to your username
        $password = ''; // Change this to your password
        $database = 'app'; // Change this to your database name

        // Create a new database connection
        $this->db = new mysqli($host, $username, $password, $database);

        // Check for connection errors
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function createUser($username, $employee_id, $hashedPassword, $registrationIp, $securityQuestion, $securityAnswer) {
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
            $is_admin = false; //1==true(admin) and 0==false(not admin)
            // Prepare the SQL statement to insert the new user
            $stmt = $this->db->prepare("INSERT INTO privilege (employee_id, is_admin)VALUES (?, ?)");
            $stmt->bind_param("is",$employee_id, $is_admin);
    
            // Execute the statement
            $stmt->execute();
        }

        // Close the statement
        $stmt->close();

        return $result;
    }
}
