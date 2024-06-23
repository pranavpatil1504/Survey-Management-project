<?php
// Include the function to connect to the database
include '../../controllers/helpers/connect_to_database.php';
require_once '../../controllers/admin_controller/admin_auth/admin_session_check.php';

// Function to retrieve users data from database
function getUsers()
{
    $conn = connect_to_database();
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    $conn->close();
    return $users;
}

// Check if the admin is logged in and has permission


// Handle form submission to delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
        $userId = $_POST['id'];
        $conn = connect_to_database();
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $stmt->close();
        $conn->close();

        // Redirect back to users.php?page=users after deletion
        header("Location: ?page=users");
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
    <title>Users Management</title>
    <link href="../../dependencies/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../bootstrap/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <br>
    <div class="container">
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Employee ID</th>
                        <th>Registration IP</th>
                        <th>Security Question</th>
                        <th>Security Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['employee_id']; ?></td>
                            <td><?php echo $user['registration_ip']; ?></td>
                            <td><?php echo $user['security_question']; ?></td>
                            <td><?php echo $user['security_answer']; ?></td>
                            <td>
                                <a href="elements/edit_user.php?id=<?php echo $user['id']; ?>"
                                    class="btn btn-primary btn-sm">Edit</a>

                                <form method="POST" action="?page=users" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script src="../../dependencies/jquery.min.js"></script>
        <script src="../../dependencies/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#usersTable').DataTable({
                    "paging": true, // Enable paging
                    "pageLength": 3, // Show only 3 entries per page
                    "lengthChange": true, // Disable number of records per page dropdown
                    "searching": false, // Enable search box
                    "ordering": true, // Enable sorting
                    "info": true, // Show 'Showing x to x of x entries' information
                    "autoWidth": false, // Disable auto width calculation
                    "responsive": true // Enable responsive design
                });
            });
        </script>
</body>

</html>