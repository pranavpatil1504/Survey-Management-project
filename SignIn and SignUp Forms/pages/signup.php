<?php
// Include the user model
require_once('../models/signup_user.php');

// Initialize variables
$showPassword = isset($_POST['show_password']);
$passwordInputType = $showPassword ? 'text' : 'password';

// Function to determine the input type for the password field
function getPasswordInputType($showPassword) {
    return $showPassword ? 'text' : 'password';
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update the showPassword flag and the password input type
    $showPassword = isset($_POST['show_password']);
    $passwordInputType = getPasswordInputType($showPassword);
}

// Validate email using regular expression
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password using regular expression
function validatePassword($password) {
    // Password must be at least 8 characters long, contain at least one capital letter, at least one special character, and at least one number
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters long.';
    }

    if (!validateEmail($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8 || !validatePassword($password)) {
        $errors['password'] = 'Password must be at least 8 characters long and contain at least one capital letter, one special character, and one number.';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Create a new instance of UserModel
        $userModel = new UserModel();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4 address
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Insert user data into the database
        $userModel->createUser($username, $email, $hashedPassword, $ipAddress);

        // Redirect to the sign-in page
        header('Location: signin.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mt-5">Sign Up</h2>
                <form method="post" action="signup.php" novalidate>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" required minlength="4" value="<?= htmlspecialchars($username ?? '') ?>">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input type="<?= $passwordInputType ?>" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="togglePassword">
                                    <i class="fas fa-eye-slash" id="hidePassword"></i>
                                </span>
                            </div>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                </form>
                <div class="text-center mt-3">
                    <p>Already Registered? <a href="signin.php" class="btn btn-link">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="../bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../bootstrap/cdnall.min.js"></script>
    <?php  echo "<script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const hidePasswordIcon = document.getElementById('hidePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                hidePasswordIcon.classList.remove('fa-eye-slash');
                hidePasswordIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                hidePasswordIcon.classList.remove('fa-eye');
                hidePasswordIcon.classList.add('fa-eye-slash');
            }
        });
    </script>"; ?>
</body>
</html>
