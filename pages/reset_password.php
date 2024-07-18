<?php
session_start();
include '../controllers/reset_password_process.php';
include_once '../controllers/helpers/redirect_to_custom_error.php';
include_once '../controllers/helpers/sanitize_functions.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try{
        $username = htmlspecialchars($_POST['username']);
        $username = sanitize_string($username);
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $security_question = htmlspecialchars($_POST['security_question']);
        $security_answer = htmlspecialchars($_POST['security_answer']);
    
        // Validate inputs
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
    
        // Process password reset if no errors
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
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect server");
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
    <link rel="stylesheet" href="../bootstrap/cdnall.min.css">
    <link href="../bootstrap/main.css" rel="stylesheet">
</head>

<body style="background-color: #E0FBE2">
    <div class="logo-container">
        <img src="../assets/logo.png">
    </div>
    <div class="image-container3">
        <img src="../assets/reset_password.png">
    </div>
    <div class="signin-container">
        <div class="row justify-content-center">
            <div class="col-md-4" style="margin-left: 45%; margin-top: -5%">
                <h2 class="text-center mb-4"
                    style="color: #365E32; font-family: Verdana, Geneva, Tahoma, sans-serif; font-size: 40px; font-weight: bold">
                    <strong>Reset Password</strong>
                </h2>
                <form method="post" action="reset_password.php" novalidate>
                    <div class="form-group">
                        <label for="username" style="color: #31363F; font-family: monospace">Username:</label>
                        <div class="input-group">
                            <input style="border-color: #365E32; border-radius: 10px 0px 0px 10px" type="text"
                                class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username"
                                name="username" required value="<?= htmlspecialchars($username ?? '') ?>">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password"
                                    style="background-color: #365E32; border-color: #365E32; border-radius: 0px 10px 10px 0px">
                                    <i class="fas fa-user" style="color: white"></i>
                                </span>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?= $errors['username'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_password" style="color: #31363F; font-family: monospace">New Password:</label>
                        <div class="input-group">
                            <input type="password" style="border-color: #365E32; border-radius: 10px 0px 0px 10px"
                                class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                                id="password" name="password" required minlength="8">
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password"
                                    style="background-color: #365E32; border-color: #365E32; border-radius: 0px 10px 10px 0px">
                                    <i class="fas fa-lock" style="color: white"></i>
                                </span>
                            </div>
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <div class="input-group">
                            <input type="password" style="border-color: #365E32; border-radius: 10px 0px 0px 10px"
                                class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                id="confirm_password" name="confirm_password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password"
                                    style="background-color: #365E32; border-color: #365E32; border-radius: 0px 10px 10px 0px">
                                    <i class="fas fa-lock" style="color: #E7D37F"></i>
                                </span>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="security_question" style="color: #31363F; font-family: monospace">Security
                            Question:</label>
                        <select class="form-control <?= isset($errors['security_question']) ? 'is-invalid' : '' ?>"
                            id="security_question" name="security_question"
                            style="border-color: #365E32; border-radius: 10px" required>
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
                        <label for="security_answer" style="color: #31363F; font-family: monospace">Security
                            Answer:</label>
                        <input type="text" style="border-color: #365E32; border-radius: 10px"
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
                    <button type="submit" class="btn btn-primary btn-block"
                        style="background-color: #365E32; border: none; font-weight: bolder; border-radius: 10px">Reset
                        Password</button>
                </form>
                <div class="text-center mt-3">
                    <p>Remember your password? <a href="signin.php" class="btn btn-link">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="../bootstrap/cdnall.min.js"></script>
</body>

</html>