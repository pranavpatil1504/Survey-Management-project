<?php
ob_start(); // Start output buffering

// Include the admin session validation 
include '../../controllers/admin_controller/admin_auth/admin_session_check.php';
include '../../controllers/helpers/previous_page.php';
require_once '../../controllers/helpers/connect_to_database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['ADMIN_NAME']) || empty($_SESSION['ADMIN_TOKEN'])) {
    echo "hi";
    redirect_to_previous_page();
}
if (!validate_admin_session($_SESSION['ADMIN_TOKEN'], $_SESSION['ADMIN_NAME'])) {
    redirect_to_previous_page();
}

function getSurveyOptions($question_id)
{
    $conn = connect_to_database();
    $sql = "SELECT * FROM survey_options WHERE question_id = ?";
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
    $conn = connect_to_database();
    $sql = "SELECT * FROM survey_questions ORDER BY question_id DESC"; // Assuming 'question_id' is auto-incremented
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
    <script src="../../dependencies/jquery.slim.min.js"></script>
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <script src="../../bootstrap/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom CSS for active sidebar item */
        .sidebar .nav-link.active {
            background-color: #343a40;
            /* Dark background color */
            font-weight: bold;
            border-radius: 10px;
        }

        /* Sidebar hide/show for mobile */
        @media (max-width: 767.98px) {
            .sidebar {
                display: none;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: #343a40;
                z-index: 1;
                padding-top: 60px;
            }

            .sidebar.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }
        }

        @media (min-width: 768px) {
            .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php"><b>Admin Panel</b></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidebarToggle" aria-controls="sidebarToggle" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <!-- End Navigation Bar -->

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-secondary sidebar collapse" id="sidebarToggle" style="min-height: 110vh">
                <br>
                <b style="font-family: monospace; font-size: 20px; color: white"><?php echo $_SESSION['ADMIN_NAME']; ?></b>
                <hr style="border: 0.5px solid white">
                </hr>
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
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
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey') ? 'active' : ''; ?>" href="?page=survey" style="color: white; text-decoration: none;">Survey Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_results') ? 'active' : ''; ?>" href="?page=survey_results" style="color: white; text-decoration: none;">Survey Results</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_reports') ? 'active' : ''; ?>" href="?page=survey_reports" style="color: white; text-decoration: none;">Survey Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'report_by_user') ? 'active' : ''; ?>" href="?page=report_by_user" style="color: white; text-decoration: none;">User Report</a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- End Sidebar -->

            <!-- Main Content Area -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 main-content">
                <br>
                <hr>
                <?php
                if (isset($_GET['search'])) {
                    // Escape the search term for security
                    $searchTerm = htmlspecialchars($_GET['search']);
                    include_once 'elements/search.php'; // Include search functionality
                }
                // Display content based on the 'page' parameter
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

                switch ($page) {
                    case 'dashboard':
                        include_once 'elements/dashboard_default.php';
                        break;
                    case 'users':
                        include_once 'elements/users.php'; // Example: include users.php for user list
                        break;
                    case 'login_history':
                        include_once 'elements/login_history.php'; // Example: include login_history.php for login history
                        break;
                    case 'session_tokens':
                        include_once 'elements/session_tokens.php'; // Example: include session_tokens.php for session tokens
                        break;
                    case 'survey':
                        include_once 'elements/survey.php'; // Include survey management page
                        break;
                    case 'survey_results':
                        include_once 'elements/admin_survey_result.php'; // Include survey results page
                        break;
                    case 'survey_reports':
                        include_once 'elements/survey_reports.php';
                        break;
                    case 'report_by_user':
                        include_once 'elements/report_by_user.php';
                        break;
                    default:
                        include_once 'elements/dashboard_default.php';
                        // Load default content here
                        break;
                }
                ?>
            </main>
            <!-- End Main Content Area -->
        </div>
    </div>

    <script>
        document.querySelector('.navbar-toggler').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>

</html>

<?php ob_end_flush(); // End output buffering and flush the output ?>
