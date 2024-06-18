<?php
session_start();
require_once '../admin_controller/admin_auth/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_name = $_POST['admin_name'];
    $password = $_POST['password'];

    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE admin_name = ?");
    $stmt->bind_param("s", $admin_name);
    $stmt->execute();
    $stmt->store_result();

    $stmt->bind_result($admin_id, $hashed_password);

    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            $session_token = bin2hex(random_bytes(32));

            $update_stmt = $conn->prepare("UPDATE admins SET session_token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $session_token, $admin_id);
            $update_stmt->execute();

            if ($update_stmt->affected_rows > 0) {
                $_SESSION['session_token'] = $session_token;
                header("Location: ../../pages/admin_pages/admin_dashboard.php?page=users");
                exit;
            } else {
                header("Location: ../../pages/admin_pages/admin_login.php?error=update_failed");
                exit;
            }
        } else {
            header("Location: ../../pages/admin_pages/admin_login.php?error=invalid_credentials");
            exit;
        }
    } else {
        header("Location: ../../pages/admin_pages/admin_login.php?error=invalid_admin_name");
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../pages/admin_pages/admin_login.php");
    exit;
}
?>
