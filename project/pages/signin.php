<?php
session_start();
include '../controllers/login_process.php';
include 'generate_captcha.php'; // Include the CAPTCHA generation script

// Initialize variables
$showPassword = isset($_POST['show_password']);
$passwordInputType = getPasswordInputType($showPassword); // Call getPasswordInputType here

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
        $login_result = handle_login($email, $password); // Example function call
        if (isset($login_result['error'])) {
            $errors['login'] = $login_result['message'];
        }
    }
}

// Generate CAPTCHA
$captchaData = generateCaptcha();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/cdnall.min.css" rel="stylesheet">
    <style>
        .captcha-image {
            width: 100px;
            height: 100px;
            margin: 5px;
            cursor: pointer;
            border-radius: 5px;
            border: 2px solid transparent;
        }

        .captcha-image.selected {
            border-color: blue;
            border-width: 5px;
            border-radius: 10px;
        }

        .captcha-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-verify {
            margin-top: 10px;
            display: block;
            width: 100%;
        }

        .captcha-error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mt-5">Sign In</h2>
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

                    <!-- CAPTCHA Card -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">CAPTCHA Challenge</h5>
                            <p class="card-text">Select all images with <?= $captchaData['category'] ?>.</p>
                            <div class="captcha-grid">
                                <?php foreach ($captchaData['images'] as $image): ?>
                                    <img src="<?= $image ?>" class="captcha-image" data-filename="<?= basename($image) ?>">
                                <?php endforeach; ?>
                            </div>
                            <div id="captchaError" class="captcha-error"></div>
                        </div>
                    </div>

                    <input type="hidden" id="captchaVerified" name="captcha_verified" value="0" required>
                    <button type="submit" id="signInButton" class="btn btn-primary btn-block disabled">Sign In</button>
                </form>
                <div class="text-center mt-3">
                    <p>No account? <a href="signup.php" class="btn btn-link">Sign Up</a></p>
                </div>
                <div class="text-center mt-2">
                    <p>Forgot your password? <a href="reset_password.php" class="btn btn-link">Reset Password</a></p>
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
    // JavaScript for form submission and CAPTCHA verification
    document.getElementById('signInButton').addEventListener('click', function(event) {
        event.preventDefault();

        const selectedImages = Array.from(document.querySelectorAll('.captcha-image.selected'))
            .map(img => img.getAttribute('data-filename'));

        const formData = new FormData();
        formData.append('selected_images', JSON.stringify(selectedImages));

        fetch('verify_captcha.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('captchaVerified').value = '1'; // Set captcha_verified to 1
                document.getElementById('captchaError').innerHTML = '';
                document.querySelector('form').submit(); // Submit the form
            } else {
                document.getElementById('captchaError').innerHTML = 'PLEASE SELECT CORRECT CAPTCHA IMAGES.';
            }
        });
    });

    // Toggle selection on image click
    document.querySelectorAll('.captcha-image').forEach(image => {
        image.addEventListener('click', function () {
            this.classList.toggle('selected');

            // Enable or disable the submit button based on CAPTCHA selection
            const selectedImages = document.querySelectorAll('.captcha-image.selected');
            const selectedFilenames = Array.from(selectedImages).map(img => img.getAttribute('data-filename'));

            // Check if exactly the required images are selected
            const requiredImages = ['1.jpeg', '2.jpeg', '3.jpeg'];
            const allRequiredSelected = requiredImages.every(image => selectedFilenames.includes(image)) && selectedFilenames.length === 3;

            if (allRequiredSelected) {
                document.getElementById('signInButton').classList.remove('disabled');
            } else {
                document.getElementById('signInButton').classList.add('disabled');
            }
        });
    });

    // Initially disable sign-in button until CAPTCHA is verified
    document.getElementById('signInButton').classList.add('disabled');
</script>"; ?>
</body>

</html>