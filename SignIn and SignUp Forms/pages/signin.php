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

// Function to generate CAPTCHA challenge
function generateCaptcha()
{
    // Define categories and their respective image directories
    $categories = [
        'traffic lights' => 'images/traffic_lights',
        'roads' => 'images/roads',
    ];

    // Randomly select a category
    $category = array_rand($categories);
    $directory = $categories[$category];

    // Initialize arrays for correct and selected images
    $correctImages = [];
    $selectedImages = [];

    // Based on the selected category, prepare the CAPTCHA challenge
    if ($category === 'traffic lights') {
        // Define correct images for traffic lights category
        $correctImages = [
            'images/traffic_lights/1.jpeg',
            'images/traffic_lights/2.jpeg',
            'images/traffic_lights/3.jpeg',
        ];

        // Shuffle the correct images
        shuffle($correctImages);

        // Get all images from the selected directory
        $allImages = glob("$directory/*.jpeg");

        // Shuffle all images
        shuffle($allImages);

        // Select 5 images from the shuffled list (including some incorrect ones)
        $selectedImages = array_slice($allImages, 0, 5);
    } else {
        // Get all images from the selected directory
        $images = glob("$directory/*.jpeg");

        // Randomly select 9 images
        shuffle($images);
        $selectedImages = array_slice($images, 0, 9);
    }

    // Store the correct images and category in session variables for validation
    $_SESSION['captcha_correct'] = $correctImages;
    $_SESSION['captcha_category'] = $category;

    return ['category' => $category, 'images' => $selectedImages];
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

                    <input type="hidden" id="captchaVerified" name="captcha_verified" value="0">
                    <button type="submit" id="signInButton" class="btn btn-primary btn-block disabled">Sign In</button>
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

        // Sign-in button logic
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
                    document.getElementById('captchaVerified').value = '1';
                    document.getElementById('captchaError').innerHTML = '';
                    document.querySelector('form').submit();
                } else {
                    document.getElementById('captchaError').innerHTML = 'CAPTCHA verification failed. Please try again.';
                }
            });
        });

        // Toggle selection on image click
        document.querySelectorAll('.captcha-image').forEach(image => {
            image.addEventListener('click', function () {
                this.classList.toggle('selected');
            });
        });

        // Enable or disable the submit button based on CAPTCHA verification
        document.querySelectorAll('.captcha-image').forEach(image => {
            image.addEventListener('click', function () {
                const selectedImages = document.querySelectorAll('.captcha-image.selected');
                if (selectedImages.length > 0) {
                    document.getElementById('signInButton').classList.remove('disabled');
                } else {
                    document.getElementById('signInButton').classList.add('disabled');
                }
            });
        });
    </script>"; ?>
</body>

</html>
