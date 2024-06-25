<?php
// Include the admin session validation 
include '../../controllers/admin_controller/admin_auth/admin_session_check.php';
include '../../controllers/helpers/previous_page.php';
require_once '../../controllers/helpers/connect_to_database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!validate_admin_session($_SESSION['ADMIN_TOKEN'], $_SESSION['ADMIN_NAME'])){
    redirect_to_previous_page();
}

function getSurveyOptions($question_id) {
    $conn = connect_to_database(); // Ensure this function is available
    $sql = "SELECT * FROM survey_options WHERE question_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
    }
    $stmt->close();
    $conn->close();
    return $options;
}

$survey_questions = getSurveyQuestions();

function getSurveyQuestions() {
    $conn = connect_to_database(); // Ensure this function is available
    $sql = "SELECT * FROM survey_questions";
    $result = $conn->query($sql);

    $questions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
    }
    $conn->close();
    return $questions;
}
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
        /* Custom CSS for active sidebar item */
        .sidebar .nav-link.active {
            background-color: #343a40; /* Dark background color */
            font-weight: bold;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><b>Admin Dashboard</b>
            <?php echo $_SESSION['ADMIN_NAME']; ?>
            </a>
        </div>
    </nav>
    <!-- End Navigation Bar -->

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-secondary sidebar d-flex flex-column" style="min-height: 98vh;">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <br>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>"
                                href="?page=dashboard" style="color: white; text-decoration: none;">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : ''; ?>"
                                href="?page=users" style="color: white; text-decoration: none;">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'login_history') ? 'active' : ''; ?>"
                                href="?page=login_history" style="color: white; text-decoration: none;">Login
                                History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'session_tokens') ? 'active' : ''; ?>"
                                href="?page=session_tokens" style="color: white; text-decoration: none;">Session
                                Tokens</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey') ? 'active' : ''; ?>"
                                href="?page=survey" style="color: white; text-decoration: none;">Survey Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_results') ? 'active' : ''; ?>"
                                href="?page=survey_results" style="color: white; text-decoration: none;">Survey Results</a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- End Sidebar -->

            <!-- Main Content Area -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <br>
                <div class="container">
                    <form method="GET" action="elements/search.php">
                        <div class="form-group">
                            <label for="search">Search:</label>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="Enter username, IP address, or timestamp">
                        </div>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>
                </div>
                <hr>
                <?php
                // Dynamically load content based on the 'page' parameter or display search results
                if (isset($_GET['search'])) {
                    // Escape the search term for security
                    $searchTerm = htmlspecialchars($_GET['search']);
                    include_once 'elements/search.php'; // Include search functionality
                }
                // Display content based on the 'page' parameter
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

                switch ($page) {
                    case 'dashboard':
                        echo '<br><h2>Welcome</h2>';
                        // Load dashboard content here
                        break;
                    case 'users':
                        echo '<br><h2>Users</h2>';
                        include_once 'elements/users.php'; // Example: include users.php for user list
                        break;
                    case 'login_history':
                        echo '<br><h2>Login History</h2>';
                        include_once 'elements/login_history.php'; // Example: include login_history.php for login history
                        break;
                    case 'session_tokens':
                        echo '<br><h2>Session Tokens</h2>';
                        include_once 'elements/session_tokens.php'; // Example: include session_tokens.php for session tokens
                        break;
                    case 'survey':
                        echo '<br><h2>Survey Management</h2>';
                        include_once 'elements/survey.php'; // Include survey management page
                        break;
                    case 'survey_results':
                        echo '<br><h2>Survey Results</h2>';
                        include_once 'elements/admin_survey_result.php'; // Include survey results page
                        break;
                    default:
                        echo '<br><h2>Dashboard</h2>';
                        // Load default content here
                        break;
                }
                ?>
            </main>
            <!-- End Main Content Area -->
        </div>
    </div>
</body>
</html>