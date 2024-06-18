<?php
// Include the user model
require '../controllers/signup_user.php';
require '../controllers/helpers/utils.php';

// Initialize variables
$showPassword = isset($_POST['show_password']);
$passwordInputType = $showPassword ? 'text' : 'password';

// Function to determine the input type for the password field
function getPasswordInputType($showPassword)
{
    return $showPassword ? 'text' : 'password';
}
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update the showPassword flag and the password input type
    $showPassword = isset($_POST['show_password']);
    $passwordInputType = getPasswordInputType($showPassword);

    // Handle form submission
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $security_question = $_POST['security_question'] ?? '';
    $security_answer = $_POST['security_answer'] ?? '';

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

    if (empty($security_question)) {
        $errors['security_question'] = 'Please select a security question.';
    }

    if (empty($security_answer)) {
        $errors['security_answer'] = 'Please provide an answer to the security question.';
    }

    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Create a new instance of UserModel
        $userModel = new UserModel();
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Check if user creation is successful
        $userCreated = $userModel->createUser($username, $email, $hashedPassword, $ipAddress, $security_question, $security_answer);

        if ($userCreated) {
            // Redirect to the sign-in page
            header('Location: signin.php');
            exit;
        } else {
            // Handle username already exists error
            $errors['username'] = 'Username already exists, Please choose another username or Sign In';
        }
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
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                            id="username" name="username" required minlength="4"
                            value="<?= htmlspecialchars($username ?? '') ?>">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                        <?php endif; ?>
                    </div>
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
                                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password"
                                name="password" required minlength="8">
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
                        <input type="password"
                            class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                            id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="security_question">Security Question</label>
                        <select class="form-control <?= isset($errors['security_question']) ? 'is-invalid' : '' ?>"
                            id="security_question" name="security_question" required>
                            <option value="">Select a security question</option>
                            <option value="favorite_teacher" <?= isset($security_question) && $security_question == 'favorite_teacher' ? 'selected' : '' ?>>Who is your favorite Teacher?</option>
                            <option value="favorite_subject" <?= isset($security_question) && $security_question == 'favorite_subject' ? 'selected' : '' ?>>What is your favorite subject?</option>
                            <option value="favorite_sport" <?= isset($security_question) && $security_question == 'favorite_sport' ? 'selected' : '' ?>>What is your favorite sport?</option>
                            <option value="favorite_singer" <?= isset($security_question) && $security_question == 'favorite_singer' ? 'selected' : '' ?>>Who is your favorite singer?</option>
                        </select>
                        <?php if (isset($errors['security_question'])): ?>
                            <div class="invalid-feedback"><?= $errors['security_question'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="security_answer">Security Answer</label>
                        <input type="text" class="form-control <?= isset($errors['security_answer']) ? 'is-invalid' : '' ?>"
                            id="security_answer" name="security_answer" required value="<?= htmlspecialchars($security_answer ?? '') ?>">
                        <?php if (isset($errors['security_answer'])): ?>
                            <div class="invalid-feedback"><?= $errors['security_answer'] ?></div>
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
