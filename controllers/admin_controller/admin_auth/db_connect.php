<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/project/controllers/config.php';

function db_connect() {
    try{
        load_env();
        // Database credentials
        $servername = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];
        // Create a new database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }catch(Exception $e){
        session_start();
        load_env();
        // Check if previous page URL is set in session
        $_SESSION['error_message'] = $e;
        $sql_error_page = $_ENV['PROJECT_ROOT'].'/pages/sqlerrors.php';
        header("Location: $sql_error_page");
        exit;
    }
}
?>
