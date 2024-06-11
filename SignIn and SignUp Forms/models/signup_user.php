<?php
class UserModel {
    private $db;

    public function __construct() {
        $host = 'localhost';
        $username = 'root'; 
        $password = ''; 
        $database = 'app'; 

        // Create a new database connection
        $this->db = new mysqli($host, $username, $password, $database);

        // Check for connection errors
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function createUser($username, $email, $hashedPassword) {
        // Get the user's IP address
        $registrationIp = $_SERVER['REMOTE_ADDR'];

        // Prepare the SQL statement
        $stmt = $this->db->prepare("INSERT INTO users (username, hashed_pass, email, registration_ip) VALUES (?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param("ssss", $username, $hashedPassword, $email, $registrationIp);

        // Execute the statement
        $stmt->execute();

        // Check for errors
        if ($stmt->error) {
            die("Error: " . $stmt->error);
        } else {
            header('Location: home.php');
            exit;
        }
        // Close the statement
        $stmt->close();
    }
}
?>
