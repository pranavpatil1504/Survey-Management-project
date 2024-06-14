<?php 
function connect_to_database() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "app";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>