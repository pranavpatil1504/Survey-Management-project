<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve new password and confirm password
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Perform password validation
    $errors = [];
    if (strlen($newPassword) < 8) {
        $errors['new_password'] = 'Password must be at least 8 characters long.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Update the user's password in the database
        // Add your database update logic here

        // Redirect the user after password reset
        header("Location: signin.php");
        exit;
    } else {
        // Redirect back to forgetpsw.php with errors
        header("Location: forgetpsw.php");
        exit;
    }
}
?>
