<?php
// Include necessary files with correct paths
include '../../../controllers/helpers/connect_to_database.php';

// Function to retrieve user details by ID
function getUserById($userId)
{
    $conn = connect_to_database();
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// Fetch user details based on ID from URL parameter
$user = [];
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $user = getUserById($userId);
}

// Redirect if user ID is not valid or not provided
if (empty($user)) {
    header("Location: users.php"); // Adjust the path if necessary
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="../../../bootstrap/bootstrap.min.css" rel="stylesheet">
    <script src=""></script>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Edit User</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="update_user.php">
                            <div class="form-group">
                                <label for="edit_username"><strong>Edit Username:</strong></label>
                                <input type="text" class="form-control" id="edit_username" name="edit_username"
                                    value="" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_email"><strong>Edit Email Address:</strong></label>
                                <input type="email" class="form-control" id="edit_email" name="edit_email"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Update</button>
                                <a href="../admin_dashboard.php" class="btn btn-secondary btn-block mt-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

