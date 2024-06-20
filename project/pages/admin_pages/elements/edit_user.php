<?php
require_once '../../../controllers/admin_controller/admin_auth/admin_session_check.php';

include '../../../controllers/helpers/connect_to_database.php';

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

$user = [];
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $user = getUserById($userId);
}

if (empty($user)) {
    header("Location: users.php");
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
                                <input type="text" class="form-control" id="edit_username" name="edit_username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_employee_id"><strong>Edit Employee ID:</strong></label>
                                <input type="number" class="form-control" id="edit_employee_id" name="edit_employee_id" value="<?php echo htmlspecialchars($user['employee_id']); ?>" required>
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
