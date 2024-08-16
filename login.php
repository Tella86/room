<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        echo '<script type="text/javascript">
        window.open("chat.php", "_blank");
        window.location.href = "../hms/dashboard.php"; // Redirect to some other page if needed
      </script>';
exit;
} else {
$error = "Invalid username or password";
}
}
?>
