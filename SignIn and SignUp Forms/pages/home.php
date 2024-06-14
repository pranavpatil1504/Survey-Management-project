<?php
include '../models/utils/connect_to_database.php';

if (isset($_GET['session_data'])) {
    $session_data = $_GET['session_data'];

    // Decode session data
    $decoded_data = json_decode(base64_decode($session_data), true);

    // Extract session token, expiration time, and username
    $session_token = $decoded_data['session_token'];
    $expiration_time = $decoded_data['expiration_time'];
    $username = $decoded_data['username'];
    // Check if the username exists in the users table
    $db = connect_to_database();
    $check_user_sql = "SELECT * FROM users WHERE username='$username'";
    $user_result = $db->query($check_user_sql);

    // If username exists, check session data in user_session_token table
    if ($user_result->num_rows > 0) {
        // Check if session token, expiration time, and username exist in user_session_token table
        $check_session_sql = "SELECT * FROM user_session_token WHERE username='$username' AND session_token='$session_token' AND expiration_time='$expiration_time'";
        $check_session_result = $db->query($check_session_sql);

        if ($check_session_result->num_rows > 0) {

            if(time() > $expiration_time ){
                header("Location: signin.php");
            }
            // Session token, expiration time, and username exist, set session variables
            session_start();
            $_SESSION['session_token'] = $session_token;
            $_SESSION['expiration_time'] = $expiration_time;
            $_SESSION['username'] = $username;

            // Welcome message for the user
            $welcome_message = "Welcome, $username!";
        } else {
            // Session data not found in database, handle accordingly
            echo "Session data not found.";
            header("Location: signin.php"); // Redirect to signin page
            exit();
        }
    } else {
        // Username not found in users table, handle accordingly
        echo "Username not found.";
        header("Location: signin.php"); // Redirect to signin page
        exit();
    }
} else {
    // Session data not provided, handle accordingly
    echo "Session data not provided.";
    header("Location: signin.php"); // Redirect to signin page
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/bootstrap-icons.min.css" rel="stylesheet">
    <script src="../dependencies/jquery.slim.min.js"></script>
    <script src="../dependencies/popper.min.js"></script>
    <script src="../bootstrap/bootstrap.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">barc.gov.in</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pricing</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-primary text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Welcome to BARC</h1>
            <p class="lead"><?php echo "Welcome, ".$username. "!!" ?? 'Your journey to success starts here.'; ?></p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <img src="../assets/1.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary">Find Out More!</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <img src="../assets/2.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary">Find Out More!</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <img src="../assets/3.jpeg" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-primary">Find Out More!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center">Our Services</h2>
            <div class="row mt-4">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-cogs fa-2x mb-3 text-primary"></i>
                            <h5 class="card-title">Service One</h5>
                            <p class="card-text">Detailed description of service one.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-lightbulb fa-2x mb-3 text-primary"></i>
                            <h5 class="card-title">Service Two</h5>
                            <p class="card-text">Detailed description of service two.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-rocket fa-2x mb-3 text-primary"></i>
                            <h5 class="card-title">Service Three</h5>
                            <p class="card-text">Detailed description of service three.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center">Testimonials</h2>
            <div id="carouselExampleIndicators" class="carousel slide mt-4" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="carousel-caption d-none d-md-block">
                            <p>"This is the best service I have ever used!" - John Doe</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-caption d-none d-md-block">
                            <p>"Exceptional quality and fantastic support." - Jane Smith</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-caption d-none d-md-block">
                            <p>"Highly recommend to everyone!" - Mark Wilson</p>
                        </div>
                    </div>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center">Contact Us</h2>
            <div class="row mt-4">
                <div class="col-lg-8 mx-auto">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $name = $_POST['name'] ?? '';
                        $email = $_POST['email'] ?? '';
                        $message = $_POST['message'] ?? '';
                        
                        $errors = [];

                        if (strlen($name) < 3) {
                            $errors['name'] = 'Name must be at least 3 characters long.';
                        }

                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors['email'] = 'Please enter a valid email address.';
                        }

                        if (strlen($message) < 10) {
                            $errors['message'] = 'Message must be at least 10 characters long.';
                        }

                        if (empty($errors)) {
                            // Process the form data (e.g., send email, save to database)
                            echo '<div class="alert alert-success">Your message has been sent successfully.</div>';
                        } else {
                            foreach ($errors as $error) {
                                echo '<div class="alert alert-danger">' . $error . '</div>';
                            }
                        }
                    }
                    ?>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4"><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start">
        <div class="container p-4">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Footer Content</h5>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vel suscipit risus, a varius velit.
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Links</h5>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <a href="#!" class="text-dark">Link 1</a>
                        </li>
                        <li>
                            <a href="#!" class="text-dark">Link 2</a>
                        </li>
                        <li>
                            <a href="#!" class="text-dark">Link 3</a>
                        </li>
                        <li>
                            <a href="#!" class="text-dark">Link 4</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Contact</h5>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <a href="#!" class="text-dark"><i class="fas fa-envelope"></i> info@example.com</a>
                        </li>
                        <li>
                            <a href="#!" class="text-dark"><i class="fas fa-phone"></i> + 01 234 567 88</a>
                        </li>
                        <li>
                            <a href="#!" class="text-dark"><i class="fas fa-print"></i> + 01 234 567 89</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            © 2024 Copyright:
            <a class="text-dark" href="#">barc.gov.in</a>
        </div>
    </footer>
</body>
</html>
