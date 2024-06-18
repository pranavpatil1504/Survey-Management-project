<?php
require_once '../../controllers/admin_controller/admin_auth/admin_session_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <script src="../../bootstrap/bootstrap.min.js"></script>
    <style>
        .sidebar .nav-link.active {
            background-color: #343a40;
            font-weight: bold;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href=""><strong>Admin Dashboard</strong></a>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-secondary sidebar d-flex flex-column" style="min-height: 98vh;">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <br>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>" href="?page=dashboard" style="color: white; text-decoration: none;">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : ''; ?>" href="?page=users" style="color: white; text-decoration: none;">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'login_history') ? 'active' : ''; ?>" href="?page=login_history" style="color: white; text-decoration: none;">Login History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'session_tokens') ? 'active' : ''; ?>" href="?page=session_tokens" style="color: white; text-decoration: none;">Session Tokens</a>
                        </li>
                    </ul>
                </div>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <br>
                <div class="container">
                    <form method="GET" action="elements/search.php">
                        <div class="form-group">
                            <label for="search">Search:</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Enter username, IP address, or timestamp">
                        </div>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>
                </div>
                <hr>
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                switch ($page) {
                    case 'dashboard':
                        echo '<br><h2>Welcome</h2>';
                        break;
                    case 'users':
                        echo '<br><h2>Users</h2>';
                        include_once 'elements/users.php';
                        break;
                    case 'login_history':
                        echo '<br><h2>Login History</h2>';
                        include_once 'elements/login_history.php';
                        break;
                    case 'session_tokens':
                        echo '<br><h2>Session Tokens</h2>';
                        include_once 'elements/session_tokens.php';
                        break;
                    default:
                        echo '<br><h2>Dashboard</h2>';
                        break;
                }
                ?>
            </main>
        </div>
    </div>
</body>
</html>
