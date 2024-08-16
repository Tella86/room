<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        echo '<script type="text/javascript">
                window.open("../../room/admin_chat.php", "_blank");
                window.location.href = "../hms/admin/dashboard.php"; // Redirect to some other page if needed
              </script>';
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
