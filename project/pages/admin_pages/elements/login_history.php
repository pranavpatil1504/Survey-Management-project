<?php
// Include the function to connect to the database
require_once '../../controllers/helpers/connect_to_database.php';

// Function to retrieve login history from database
function getLoginHistory()
{
    $conn = connect_to_database();
    $sql = "SELECT * FROM user_login_history";
    $result = $conn->query($sql);

    $loginHistory = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $loginHistory[] = $row;
        }
    }
    $conn->close();
    return $loginHistory;
}

// Get login history data
$loginHistory = getLoginHistory();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History</title>
    <link rel="stylesheet" href="../../bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../dependencies/jquery.dataTables.min.css">
</head>

<body>
    <div class="container">
        <h1><strong>Login History</strong></h1>
        <hr>
        </hr>
        <div class="table table-responsive table-bordered" style="border: none">
            <table id="loginHistoryTable" class="table table-striped">
                <thead style="background-color: #31363F; color: white">
                    <tr>
                        <th style="padding: 12px">History ID</th>
                        <th style="padding: 12px">User ID</th>
                        <th style="padding: 12px">Login Timestamp</th>
                        <th style="padding: 12px">IP Address</th>
                        <th style="padding: 12px">Username</th>
                    </tr>
                </thead>
                <tbody style="background-color: #ECEFF1">
                    <?php foreach ($loginHistory as $history): ?>
                        <tr>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $history['history_id']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $history['user_id']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $history['login_timestamp']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $history['ip_address']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $history['username']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../dependencies/jquery.slim.min.js"></script>
    <script src="../../dependencies/popper.min.js"></script>
    <script src="../../bootstrap/bootstrap.min.js"></script>
    <script src="../../dependencies/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#loginHistoryTable').DataTable({
                "paging": true, // Enable paging
                "lengthChange": true, // Enable number of records per page dropdown
                "searching": true, // Enable search box
                "ordering": true, // Enable sorting
                "info": true, // Show 'Showing x to x of x entries' information
                "autoWidth": false, // Disable auto width calculation
                "responsive": true // Enable responsive design
            });
        });
    </script>
</body>

</html>