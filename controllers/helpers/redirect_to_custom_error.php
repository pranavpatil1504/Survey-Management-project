<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/project/controllers/config.php';

// Function to redirect to custom_error_page
function redirect_to_custom_error($e1, $e2) {
    try{
        load_env();
        session_start();
        $_SESSION['error_message1'] = $e1;
        $_SESSION['error_message2'] = $e2;
        $custom_error_page = $_ENV['PROJECT_ROOT'].'/pages/custom_error.php';
        header("Location: $custom_error_page");
        exit;
    }catch(Exception $e){
        throw $e;
    }
}
?>
