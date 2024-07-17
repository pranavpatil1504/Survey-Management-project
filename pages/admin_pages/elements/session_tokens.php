<?php
// Include the function to connect to the database

require_once '../../controllers/helpers/connect_to_database.php';
include_once '../../controllers/helpers/redirect_to_custom_error.php';
// Function to retrieve session tokens from the database
function get_session_tokens()
{
    try{
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
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect server");
    }

}

// Fetch session tokens
$session_tokens = get_session_tokens();
?>
<div class="container">
    <h1><b>Sessions Tokens</b></h1>
    <hr>
    </hr>
    <div class="table table-responsive table-bordered" style="border: none">
        <table class="table table-striped">
            <thead style="background-color: #31363F; color: white">
                <tr>
                    <th style="padding: 12px">Username</th>
                    <th style="padding: 12px">Session Token</th>
                    <th style="padding: 12px">Expiration Time</th>
                </tr>
            </thead>
            <tbody style="background-color: #ECEFF1">
                <?php foreach ($session_tokens as $token): ?>
                    <tr>
                        <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                            <?php echo $token['username']; ?>
                        </td>
                        <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                            <?php echo $token['session_token']; ?>
                        </td>
                        <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                            <?php echo $token['expiration_time']; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>