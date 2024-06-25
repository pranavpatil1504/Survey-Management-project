<?php
// Include the function to connect to the database

require_once '../../controllers/helpers/connect_to_database.php';

// Function to retrieve session tokens from the database
function get_session_tokens() {
    $conn = connect_to_database();

    // Query to fetch session tokens
    $sql = "SELECT username, session_token, expiration_time FROM user_session_token";
    $result = $conn->query($sql);

    $session_tokens = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $session_tokens[] = $row;
        }
    }

    $conn->close();
    return $session_tokens;
}

// Fetch session tokens
$session_tokens = get_session_tokens();
?>
<br>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Session Token</th>
                <th>Expiration Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($session_tokens as $token): ?>
            <tr>
                <td><?php echo $token['username']; ?></td>
                <td><?php echo $token['session_token']; ?></td>
                <td><?php echo $token['expiration_time']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
