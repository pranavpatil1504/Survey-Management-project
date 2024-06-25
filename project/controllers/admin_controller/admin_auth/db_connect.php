<?php
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
?>
