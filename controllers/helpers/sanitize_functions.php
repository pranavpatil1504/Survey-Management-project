<?php

function sanitize_string($input) {
    // If input is null, return null
    if ($input === null) {
        return null;
    }
    
    // Remove tags and encode special characters for HTML context
    return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_email($email) {
    // If email is null, return null
    if ($email === null) {
        return null;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null; // or return an empty string if you prefer
    }

    // Escape special characters (usually not needed for email)
    return htmlspecialchars($email, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_numeric($number) {
    // If number is null, return null
    if ($number === null) {
        return null;
    }

    // Sanitize numeric input by allowing digits, decimal points, and minus signs
    return preg_replace('/[^0-9.\-]/', '', $number);
}

?>
