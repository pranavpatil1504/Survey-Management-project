<?php
// Function to redirect to previous page
function redirect_to_previous_page() {
    // Check if previous page URL is set in session
    if (isset($_SESSION['previous_page']) && !empty($_SESSION['previous_page'])) {
        $previous_page = $_SESSION['previous_page'];
    } elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        $previous_page = $_SERVER['HTTP_REFERER'];
    } else {
        // If no previous page is set, redirect to a default page
       echo "Error";
       exit;
    }

    // Redirect to the determined previous page
    header("Location: $previous_page");
    exit;
}

?>