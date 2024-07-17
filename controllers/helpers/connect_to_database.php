<?php 
include_once 'redirect_to_sqlerrorpage.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/project/controllers/config.php';



function connect_to_database() {
    try{
        load_env();
        $servername = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $dbname = $_ENV['DB_NAME'];
    
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    catch(Exception $e){
        $_SESSION['error']= $e;
        redirect_to_sql_error_page("SERVER OFFLINE");
    }
}
?>

