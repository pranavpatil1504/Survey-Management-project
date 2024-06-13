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

    public function createUser($username, $email, $hashedPassword, $registrationIp) {
        // Prepare the SQL statement
        $stmt = $this->db->prepare("INSERT INTO users (username, hashed_pass, email, registration_ip) VALUES (?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param("ssss", $username, $hashedPassword, $email, $registrationIp);

        // Execute the statement
        $stmt->execute();

        // Check for errors
        if ($stmt->error) {
            die("Error: " . $stmt->error);
        }else{
            header('Location: home.php');
            $stmt->close();
            exit;
        }
        // Close the statement
        $stmt->close();
    }
}
?>
