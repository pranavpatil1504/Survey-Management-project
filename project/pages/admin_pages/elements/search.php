<?php
// Include the function to connect to the database
include '../../../controllers/helpers/connect_to_database.php';

// Function to search across all tables
function searchAllTables($searchTerm) {
    $conn = connect_to_database();

    // Prepare SQL queries for each table using parameterized queries to prevent SQL injection
    $sqlUsers = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
    $sqlLoginHistory = "SELECT * FROM user_login_history WHERE username LIKE ? OR ip_address LIKE ? OR login_timestamp LIKE ?";
    $sqlSessionTokens = "SELECT * FROM user_session_token WHERE username LIKE ?";

    $stmtUsers = $conn->prepare($sqlUsers);
    $searchTermLike = '%' . $searchTerm . '%';
    $stmtUsers->bind_param("ss", $searchTermLike, $searchTermLike);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();

    $stmtLoginHistory = $conn->prepare($sqlLoginHistory);
    $stmtLoginHistory->bind_param("sss", $searchTermLike, $searchTermLike, $searchTermLike);
    $stmtLoginHistory->execute();
    $resultLoginHistory = $stmtLoginHistory->get_result();

    $stmtSessionTokens = $conn->prepare($sqlSessionTokens);
    $stmtSessionTokens->bind_param("s", $searchTermLike);
    $stmtSessionTokens->execute();
    $resultSessionTokens = $stmtSessionTokens->get_result();

    // Fetch results
    $users = [];
    $loginHistory = [];
    $sessionTokens = [];

    if ($resultUsers->num_rows > 0) {
        while ($row = $resultUsers->fetch_assoc()) {
            $users[] = $row;
        }
    }

    if ($resultLoginHistory->num_rows > 0) {
        while ($row = $resultLoginHistory->fetch_assoc()) {
            $loginHistory[] = $row;
        }
    }

    if ($resultSessionTokens->num_rows > 0) {
        while ($row = $resultSessionTokens->fetch_assoc()) {
            $sessionTokens[] = $row;
        }
    }

    $stmtUsers->close();
    $stmtLoginHistory->close();
    $stmtSessionTokens->close();
    $conn->close();

    return [
        'users' => $users,
        'loginHistory' => $loginHistory,
        'sessionTokens' => $sessionTokens
    ];
}

// Check if search term is provided
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchResults = searchAllTables($searchTerm);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="../../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <style>
        .table {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><b>Admin Dashboard</b></a>
            <a class="navbar-brand" href="javascript:history.back()"><b>Previous Page</b></a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mt-4">Search Results for '<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>'</h2>
        <hr>
        <!-- Users Table -->
        <?php if (!empty($searchResults['users'])): ?>
        <h3>Users</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registration IP</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults['users'] as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($user['registration_ip'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($user['registration_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Login History Table -->
        <?php if (!empty($searchResults['loginHistory'])): ?>
        <h3>Login History</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>History ID</th>
                        <th>User ID</th>
                        <th>Login Timestamp</th>
                        <th>IP Address</th>
                        <th>Username</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults['loginHistory'] as $history): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($history['history_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($history['user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($history['login_timestamp'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($history['ip_address'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($history['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Session Tokens Table -->
        <?php if (!empty($searchResults['sessionTokens'])): ?>
        <h3>Session Tokens</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Session Token ID</th>
                        <th>Username</th>
                        <th>Session Token</th>
                        <th>Expiration Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults['sessionTokens'] as $token): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($token['user_session_token_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($token['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($token['session_token'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($token['expiration_time'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php } // End if $_GET['search'] ?>
