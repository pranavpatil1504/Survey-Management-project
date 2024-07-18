<?php
ob_start(); // Start output buffering

require_once '../../controllers/helpers/connect_to_database.php';
// require_once '../../controllers/admin_controller/admin_auth/admin_session_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


try {
    if (!isset($_SESSION['ADMIN_NAME'])) {
        header("Location: ../../pages/signin.php");
    }
    if ($_SESSION['ADMIN_NAME'] == null) {
        header("Location: ../../pages/signin.php");
    }
    if ($_SESSION['ADMIN_TOKEN'] == null) {
        header("Location: ../../pages/signin.php");
    }
    if (!isset($_SESSION['ADMIN_TOKEN'])) {
        header("Location: ../../pages/signin.php");
    }
    // if(validate_admin_session($_SESSION['ADMIN_NAME'],$_SESSION['ADMIN_TOKEN'])==false){
    //     header("Location: ../../pages/signin.php");
    // }
} catch (Exception $e) {
    header("Location: ../../pages/signin.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    logout(); // Call the logout function
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

function getSurveyQuestions()
{
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

function logout()
{
    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: ../../pages/signin.php");
    exit;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
                position: fixed;
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
                            <!-- Logout Button -->
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="ml-auto">
                    <button class="btn btn-light btn-md col-xl-20 col-xs-15 col-md-20 col-lg-20 " type="submit"
                        name="logout"
                        style="border: none; border-radius: 5px; font-weight: bold; background-color:#CCD3CA; padding: 7px;">
                        Logout
                    </button>
                </form>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidebarToggle"
                aria-controls="sidebarToggle" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    <!-- End Navigation Bar -->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-secondary sidebar collapse" id="sidebarToggle"
                style="min-height: 110vh">
                <br>
                <b style="font-family: monospace; font-size: 20px; color: white">
                    <i class="fas fa-user-gear"></i>
                    <?php echo $_SESSION['ADMIN_NAME']; ?></b>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="ml-auto">
                </form>
                <hr style="border: 0.5px solid white">
                </hr>
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>"
                                href="?page=dashboard" style="color: white; text-decoration: none;">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_users') ? 'active' : ''; ?>"
                                href="?page=survey_users" style="color: white; text-decoration: none;">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'login_history') ? 'active' : ''; ?>"
                                href="?page=login_history" style="color: white; text-decoration: none;">
                                <i class="fas fa-history"></i> Login History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'session_tokens') ? 'active' : ''; ?>"
                                href="?page=session_tokens" style="color: white; text-decoration: none;">
                                <i class="fas fa-key"></i> Session Tokens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_mgmt') ? 'active' : ''; ?>"
                                href="?page=survey_mgmt" style="color: white; text-decoration: none;">
                                <i class="fas fa-poll"></i> Survey Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'compl_ques_res') ? 'active' : ''; ?>"
                                href="?page=compl_ques_res" style="color: white; text-decoration: none;">
                                <i class="fas fa-question-circle"></i> Library Membership Responses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_results') ? 'active' : ''; ?>"
                                href="?page=survey_results" style="color: white; text-decoration: none;">
                                <i class="fas fa-chart-bar"></i> Survey Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'users_other_responses') ? 'active' : ''; ?>"
                                href="?page=users_other_responses" style="color: white; text-decoration: none;">
                                <i class="fas fa-comments"></i> Other Responses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_reports') ? 'active' : ''; ?>"
                                href="?page=survey_reports" style="color: white; text-decoration: none;">
                                <i class="fas fa-file-alt"></i> Survey Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'personal_details') ? 'active' : ''; ?>"
                                href="?page=personal_details" style="color: white; text-decoration: none;">
                                <i class="fas fa-book"></i> Personal Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'survey_user_reports') ? 'active' : ''; ?>"
                                href="?page=survey_user_reports" style="color: white; text-decoration: none;">
                                <i class="fas fa-user"></i> User Report
                            </a>
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
                    case 'survey_users':
                        include_once 'elements/survey_users.php'; // Example: include users.php for user list
                        break;
                    case 'login_history':
                        include_once 'elements/login_history.php'; // Example: include login_history.php for login history
                        break;
                    case 'session_tokens':
                        include_once 'elements/session_tokens.php'; // Example: include session_tokens.php for session tokens
                        break;
                    case 'survey_mgmt':
                        include_once 'elements/survey_mgmt.php'; // Include survey management page
                        break;
                    case 'compl_ques_res':
                        include_once 'elements/compl_ques_res.php';
                        break;
                    case 'survey_results':
                        include_once 'elements/survey_result.php'; // Include survey results page
                        break;
                    case 'survey_reports':
                        include_once 'elements/survey_reports.php';
                        break;
                    case 'users_other_responses':
                        include_once 'elements/users_other_responses.php';
                        break;
                    case 'personal_details':
                        include_once 'elements/personal_details.php';
                        break;
                    case 'survey_user_reports':
                        include_once 'elements/survey_user_reports.php';
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
        document.querySelector('.navbar-toggler').addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>

</html>

<?php ob_end_flush(); // End output buffering and flush the output ?>