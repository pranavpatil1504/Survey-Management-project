<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/project/controllers/config.php';
// Function to redirect to sql_error_page
function redirect_to_sql_error_page($e) {
    session_start();
    load_env();
    // Check if previous page URL is set in session
    $_SESSION['error_message'] = $e;
    $sql_error_page = $_ENV['PROJECT_ROOT'].'/pages/sqlerrors.php';
    header("Location: $sql_error_page");
    exit;
}
?>
