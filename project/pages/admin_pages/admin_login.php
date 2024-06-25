<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign-In</title>
    <!-- Bootstrap CSS -->
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #000; color: #fff; height: 100vh; display: flex; align-items: center; justify-content: center; ">
    <div class="container" >
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card" style="background-color: #333; border: none;">
                <div class="card-header text-center">
                        Admin Sign-In
                    </div>
                    <div class="card-body">
                        <form action="../../controllers/admin_controller/admin_login_process.php" method="POST">
                            <div class="form-group">
                                <label for="admin_name">Admin Name</label>
                                <input type="text" class="form-control" id="admin_name" name="admin_name" style="background-color: #222; border: 1px solid #555; color: #fff;" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" style="background-color: #222; border: 1px solid #555; color: #fff;" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" style="background-color: #555; border: none;">Sign In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
