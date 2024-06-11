<?php include '../models/login_process.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mt-5">Sign In</h2>
                <form method="post" action="signin.php" novalidate>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required minlength="8">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
                <div class="text-center mt-3">
                    <p>No account? <a href="signup.php" class="btn btn-link">Sign Up</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
