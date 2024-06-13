<?php
session_start();
include '../models/login_process.php';

// Initialize variables
$showPassword = isset($_POST['show_password']);
$passwordInputType = $showPassword ? 'text' : 'password';

// Function to determine the input type for the password field
function getPasswordInputType($showPassword)
{
    return $showPassword ? 'text' : 'password';
}

// Initialize errors array
$errors = [];

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update the showPassword flag and the password input type
    $showPassword = isset($_POST['show_password']);
    $passwordInputType = getPasswordInputType($showPassword);

    // Validate CAPTCHA
    if (!isset($_SESSION['captcha_verified']) || !$_SESSION['captcha_verified']) {
        $errors['captcha'] = 'CAPTCHA verification is required.';
    }

    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = 'Email is required.';
    } else {
        $email = htmlspecialchars($_POST['email']);
    }

    // Validate password
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($_POST['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } else {
        $password = $_POST['password'];
    }

    // If there are no validation errors and CAPTCHA is verified, proceed with login
    if (empty($errors) && $_SESSION['captcha_verified']) {
        // Perform login validation with your database
        // This is a placeholder for your actual login process code
        $login_successful = login_process($email, $password); // Example function call
        if (!$login_successful) {
            $errors['login'] = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/cdnall.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mt-5">Sign In</h2>
                <?php if (isset($errors['captcha'])): ?>
                    <div class="alert alert-danger"><?= $errors['captcha'] ?></div>
                <?php endif; ?>
                <form method="post" action="signin.php" novalidate>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                            id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input type="<?= $passwordInputType ?>"
                                class="form-control <?= isset($errors['password']) || isset($errors['login']) ? 'is-invalid' : '' ?>"
                                id="password" name="password" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="togglePassword">
                                    <i class="fas fa-eye-slash" id="hidePassword"></i>
                                </span>
                            </div>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                        <?php if (isset($errors['login'])): ?>
                            <div class="invalid-feedback d-block"><?= $errors['login'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="captcha">CAPTCHA</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-secondary"
                                onclick="window.location.href='generate_captcha.php'">Complete CAPTCHA</button>
                        </div>
                        <?php if (isset($errors['captcha'])): ?>
                            <div class="invalid-feedback d-block"><?= $errors['captcha'] ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block <?= $_SESSION['captcha_verified'] ? 'disabled'  : 'btn-secondary' ?>" <?= $_SESSION['captcha_verified'] ? '' : 'disabled' ?>>Sign In</button>
                </form>
                <div class="text-center mt-3">
                    <p>No account? <a href="signup.php" class="btn btn-link">Sign Up</a></p>
                </div>
                <div class="text-center mt-2">
                    <p>Forgot your password? <a href="forgetpsw.php" class="btn btn-link">Reset Password</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="../bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../bootstrap/cdnall.min.js"></script>
    <?php echo "<script>
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
