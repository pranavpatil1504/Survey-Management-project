<?php
session_start();
include 'reset_password_process.php';
$showPassword = isset($_POST['show_password']);
$passwordInputType = $showPassword ? 'text' : 'password';
function getPasswordInputType($showPassword)
{
    return $showPassword ? 'text' : 'password';
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $security_question = htmlspecialchars($_POST['security_question']);
    $security_answer = htmlspecialchars($_POST['security_answer']);
    $showPassword = isset($_POST['password']);
    $passwordInputType = getPasswordInputType($showPassword);

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }

    if (empty($new_password) || strlen($new_password) < 8) {
        $errors['new_password'] = 'New password must be at least 8 characters.';
    }

    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($security_question)) {
        $errors['security_question'] = 'Security question is required.';
    }

    if (empty($security_answer)) {
        $errors['security_answer'] = 'Security answer is required.';
    }

    if (empty($errors)) {
        $result = handle_password_reset($username, $new_password, $security_question, $security_answer);

        if ($result['success']) {
            $_SESSION['success'] = 'Password has been updated successfully.';
            header('Location: signin.php');
            exit();
        } else {
            $errors['general'] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/cdnall.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mt-5">Reset Password</h2>
                <form method="post" action="reset_password.php" novalidate>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                            id="username" name="username" required value="<?= htmlspecialchars($username ?? '') ?>">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-group">
                            <input type="<?= $passwordInputType ?>"
                                class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                                id="password" name="password" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" id="togglePassword">
                                    <i class="fas fa-eye-slash" id="hidePassword"></i>
                                </span>
                            </div>
                        </div>
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
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
                            <option value="favorite_teacher" <?= isset($security_question) && $security_question == 'favorite_teacher' ? 'selected' : '' ?>>Who is your favorite
                                Teacher?</option>
                            <option value="favorite_subject" <?= isset($security_question) && $security_question == 'favorite_subject' ? 'selected' : '' ?>>What is your favorite
                                subject?</option>
                            <option value="favorite_sport" <?= isset($security_question) && $security_question == 'favorite_sport' ? 'selected' : '' ?>>What is your favorite sport?
                            </option>
                            <option value="favorite_singer" <?= isset($security_question) && $security_question == 'favorite_singer' ? 'selected' : '' ?>>Who is your favorite singer?
                            </option>
                        </select>
                        <?php if (isset($errors['security_question'])): ?>
                            <div class="invalid-feedback"><?= $errors['security_question'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="security_answer">Security Answer</label>
                        <input type="text"
                            class="form-control <?= isset($errors['security_answer']) ? 'is-invalid' : '' ?>"
                            id="security_answer" name="security_answer" required
                            value="<?= htmlspecialchars($security_answer ?? '') ?>">
                        <?php if (isset($errors['security_answer'])): ?>
                            <div class="invalid-feedback"><?= $errors['security_answer'] ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger"><?= $errors['general'] ?></div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
                <div class="text-center mt-3">
                    <p>Remember your password? <a href="signin.php" class="btn btn-link">Sign In</a></p>
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
        }); </script>"; ?>
</body>

</html>