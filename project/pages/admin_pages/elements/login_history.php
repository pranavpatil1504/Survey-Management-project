<?php
// Include the function to connect to the database
include '../../controllers/helpers/connect_to_database.php';

// Function to retrieve login history from database
function getLoginHistory() {
    $conn = connect_to_database();
    $sql = "SELECT * FROM user_login_history";
    $result = $conn->query($sql);
    
    $loginHistory = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $loginHistory[] = $row;
        }
    }
    $conn->close();
    return $loginHistory;
}

// Get login history data
$loginHistory = getLoginHistory();
?>
<br>

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
            <?php foreach ($loginHistory as $history): ?>
            <tr>
                <td><?php echo $history['history_id']; ?></td>
                <td><?php echo $history['user_id']; ?></td>
                <td><?php echo $history['login_timestamp']; ?></td>
                <td><?php echo $history['ip_address']; ?></td>
                <td><?php echo $history['username']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
