<?php 
// Validate email using regular expression
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password using regular expression
function validatePassword($password) {
    // Password must be at least 8 characters long, contain at least one capital letter, at least one special character, and at least one number
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password);
}

?>