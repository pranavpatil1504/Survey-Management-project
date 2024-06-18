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

    public function createUser($username, $email, $hashedPassword, $registrationIp, $securityQuestion, $securityAnswer) {
        // Check if username already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // If username exists, return false or throw an error
        if ($stmt->num_rows > 0) {
            return false; // Username already exists
        }

        // Hash the security answer
        $hashed_security_answer = password_hash($securityAnswer, PASSWORD_BCRYPT);

        // Prepare the SQL statement to insert user data
        $stmt = $this->db->prepare("INSERT INTO users (username, hashed_pass, email, registration_ip, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param("ssssss", $username, $hashedPassword, $email, $registrationIp, $securityQuestion, $hashed_security_answer);

        // Execute the statement
        $stmt->execute();

        // Check for errors
        if ($stmt->error) {
            die("Error: " . $stmt->error);
        }

        // Close the statement
        $stmt->close();

        return true; // User created successfully
    }
}
?>
