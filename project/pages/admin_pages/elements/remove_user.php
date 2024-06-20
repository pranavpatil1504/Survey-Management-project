<?php
session_start();
require_once '../../../controllers/admin_controller/admin_auth/admin_session_check.php';

include '../../../controllers/helpers/connect_to_database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $userId = $_POST['id'];

    $conn = connect_to_database();
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: ../../admin_pages/admin_dashboard.php?page=users");
    exit;
} else {
    header("Location: ../../admin_pages/admin_dashboard.php?page=users");
    exit;
}

?>
