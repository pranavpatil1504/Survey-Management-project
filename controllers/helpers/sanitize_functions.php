<?php

function sanitize_string($input) {
    // If input is null, return null
    if ($input === null) {
        return null;
    }
    // Remove tags and encode special characters for HTML context
    $sanitized = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
    // Replace single quotes with an escaped version
    $sanitized = str_replace("'", "\'", $sanitized);  
    // Replace double quotes with an escaped version
    $sanitized = str_replace('"', '\"', $sanitized);
    // Replace commas with an escaped version (if needed)
    $sanitized = str_replace(',', '\,', $sanitized);
    // Replace NULL byte with an escaped version
    $sanitized = str_replace("\0", '\0', $sanitized);
    return $sanitized;
}

function sanitize_email($email) {
    // If email is null, return null
    if ($email === null) {
        return null;
    }
    // Replace single quotes with an escaped version
    $sanitized = str_replace("'", "\'", $email);
    // Replace double quotes with an escaped version
    $sanitized = str_replace('"', '\"', $sanitized);   
    // Replace commas with an escaped version (if needed)
    $sanitized = str_replace(',', '\,', $sanitized);   
    // Replace NULL byte with an escaped version
    $sanitized_email = str_replace("\0", '\0', $sanitized);
    return $sanitized_email;
}

function sanitize_numeric($number) {
    // If number is null, return null
    if ($number === null) {
        return null;
    }
    // Remove tags and encode special characters for HTML context (if needed)
    // For numeric input, this step is typically not necessary
    // Sanitize numeric input
    $sanitized_number = preg_replace('/[^0-9\.\-]/', '', $number);
    return $sanitized_number;
}

?>
