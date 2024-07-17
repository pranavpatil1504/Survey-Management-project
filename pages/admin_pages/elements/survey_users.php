<?php
// Include the function to connect to the database
require_once '../../controllers/helpers/connect_to_database.php';
require_once '../../controllers/admin_controller/admin_auth/admin_session_check.php';
require_once '../../controllers/helpers/redirect_to_custom_error.php';

// Function to retrieve users data from database
function getUsers()
{
    $conn = connect_to_database();
    try{
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);
    
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
    }catch(Exception $e){
        redirect_to_custom_error("Server Error","Unable to connect to server");
    }

    $conn->close();
    return $users;
}
// Handle form submission to delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete' && isset($_POST['id']) && isset($_POST['user_session_token_id'])) {
        $userId = $_POST['id'];
        $user_session_token_id = $_POST['user_session_token_id'];
        $conn = connect_to_database();
        try{
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            $sql_token = "DELETE FROM user_session_token WHERE user_session_token_id = ?";
            $stmt_token = $conn->prepare($sql_token);
            $stmt->bind_param("i", $user_session_token_id);
            $stmt->execute();
            $stmt->close();
        }catch(Exception $e){
            redirect_to_custom_error("Server Error","Unable to connect to server");
        }
        $conn->close();
        // Redirect back to users.php?page=users after deletion
        header("Location: ?page=survey_users");
        exit;
    }
}

// Get users data
$users = getUsers();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link href="../../dependencies/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1><b>Users</b></h1>
        <hr>
        <div class="table-responsive table-bordered" style="border: none">
            <table id="usersTable" class="table table-striped table-responsive">
                <thead style="background-color: #31363F; color: white">
                    <tr>
                        <th style="padding: 12px">ID</th>
                        <th style="padding: 12px">Username</th>
                        <th style="padding: 12px">Employee ID</th>
                        <th style="padding: 12px">Registration IP</th>
                        <th style="padding: 12px">Security Question</th>
                        <th style="padding: 12px">Security Answer</th>
                        <th style="padding: 12px">Actions</th>
                    </tr>
                </thead>
                <tbody style="background-color: #ECEFF1">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px"><?php echo $user['id']; ?>
                            </td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $user['username']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $user['employee_id']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $user['registration_ip']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $user['security_question']; ?></td>
                            <td style="background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <?php echo $user['security_answer']; ?></td>
                            <td style="display: flex; flex-direction: column; background-color: #FFFFFF; color: #31363F; padding: 10px">
                                <a href="elements/edit_user.php?id=<?php echo $user['id']; ?>"
                                    class="btn btn-primary btn-sm mb-1"
                                    style="background-color: #9ADE7B; border: none; color: black; font-weight: 500;">Edit</a>
                                <form method="POST" action="?page=survey_users">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        style="background-color: #FF000070; border: none; color: black; font-weight: 500;">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../../dependencies/jquery.min.js"></script>
    <script src="../../dependencies/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                "paging": true, // Enable paging
                "pageLength": 3, // Show only 3 entries per page
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
