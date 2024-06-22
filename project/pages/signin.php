<?php
session_start();
include '../controllers/login_process.php';
include 'generate_captcha.php'; // Include the CAPTCHA generation script

// Initialize errors array
$errors = [];

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CAPTCHA
    if (!isset($_SESSION['captcha_verified']) || !$_SESSION['captcha_verified']) {
        $errors['captcha'] = 'CAPTCHA verification is required.';
    }

    // Validate employee ID
    if (empty($_POST['employee_id'])) {
        $errors['employee_id'] = 'Employee ID is required.';
    } elseif (!is_numeric($_POST['employee_id'])) {
        $errors['employee_id'] = 'Employee ID must be a numeric value.';
    } elseif ($_POST['employee_id'] < 0 || $_POST['employee_id'] > 1000) {
        $errors['employee_id'] = 'Employee ID must be between 0 and 1000.';
    } else {
        $employee_id = htmlspecialchars($_POST['employee_id']);
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
        $login_result = handle_login($employee_id, $password); // Example function call
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
    <link href="../bootstrap/main.css" rel="stylesheet">
</head>

<body>
    <div class="logo-container">
        <img src="../assets/logo.png">
    </div>
    <div class="image-container2">
        <img src="../assets/signin.png">
    </div>
    <div class="signin-container">
        <div class="row justify-content-center">
            <div class="col-md-4" style="margin-left: 45%; margin-top: -6.5%">
                <h1 class="text-center mt-2" style="color: #365E32; font-family: Verdana, Geneva, Tahoma, sans-serif; font-size: 40px; font-weight: bold"><strong>Sign In</strong></h1>
                <form method="post" action="signin.php" novalidate>
                    <div class="form-group">
                        <label for="employee_id" style="color: #31363F; font-family: monospace">Employee ID:</label>
                        <div class="input-group">
                            <input type="number" style="border-color: #365E32; border-radius: 10px 0px 0px 10px"
                                class="form-control <?= isset($errors['employee_id']) ? 'is-invalid' : '' ?>"
                                id="employee_id" name="employee_id" required min="0" max="1000"
                                value="<?= htmlspecialchars($employee_id ?? '') ?>">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="background-color: #365E32; border-color: #365E32; border-radius: 0px 10px 10px 0px">
                                    <i class="fas fa-id-badge" style="color: white"></i>
                                </span>
                            </div>
                        </div>
                        <?php if (isset($errors['employee_id'])): ?>
                            <div class="invalid-feedback"><?= $errors['employee_id'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password" style="color: #31363F; font-family: monospace">Password:</label>
                        <div class="input-group">
                            <input type="password" style="border-color: #365E32; border-radius: 10px 0px 0px 10px"
                                class="form-control <?= isset($errors['password']) || isset($errors['login']) ? 'is-invalid' : '' ?>"
                                id="password" name="password" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="background-color: #365E32; border-color: #365E32; border-radius: 0px 10px 10px 0px">
                                    <i class="fas fa-lock" style="color: white"></i>
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
                    <div class="card mt-3" style="border-color: #365E32; border-radius: 10px">
                        <div class="card-body" style="color: #365E32; border-radius: 0px 10px 10px 0px; font-family: monospace">
                            <h5 class="card-title">CAPTCHA</h5>
                            <p class="card-text" style="color: #31363F; font-weight: bold">Select all images with <?= $captchaData['category'] ?>.</p>
                            <div class="captcha-grid">
                                <?php foreach ($captchaData['images'] as $image): ?>
                                    <img src="<?= $image ?>" class="captcha-image" data-filename="<?= basename($image) ?>">
                                <?php endforeach; ?>
                            </div>
                            <div id="captchaError" class="captcha-error"></div>
                        </div>
                    </div>

                    <input type="hidden" id="captchaVerified" name="captcha_verified" value="0" required>
                    <button type="button" id="signInButton" class="btn btn-primary btn-block disabled" style="margin-top: 3%; background-color: #365E32; border: none; font-weight: bolder; border-radius: 10px">Sign In</button>
                </form>
                <div class="text-center mt-3">
                    <p>No account? <a href="signup.php" class="btn btn-link">Sign Up</a></p>
                </div>
                <div class="text-center mt-2">
                    <p>Forgot your password? <a href="reset_password.php" class="btn btn-link">Reset Password</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script src="../bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../bootstrap/cdnall.min.js"></script>
    <?php echo "<script>
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