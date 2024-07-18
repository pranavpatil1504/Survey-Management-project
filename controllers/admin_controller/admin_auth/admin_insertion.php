<?php
// Database connection function
function db_connect() {
    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "app";

    // Create a new database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Insert admin credentials
function insert_admin($admin_name, $password) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Create database connection
    $conn = db_connect();

    // Prepare statement for insertion
    $stmt = $conn->prepare("INSERT INTO admins (admin_name, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $admin_name, $hashed_password);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New admin record inserted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}

// Usage example to insert admin1 with password123
$admin_name = "admin1";
$password = "password123";

// Insert the admin record
insert_admin($admin_name, $password);
?>
