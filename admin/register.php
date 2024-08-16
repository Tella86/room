<?php
session_start();
include '../db.php';

// Ensure only logged-in admins can access this page
// if (!isset($_SESSION['admin'])) {
//     header('Location: admin_login.php');
//     exit;
// }

// $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Simple form validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Handle file upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uploadFile = $uploadDir . basename($_FILES['photo']['name']);
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                $error = "Failed to upload photo";
            }
        } else {
            $uploadFile = null;
        }

        if (empty($error)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert new admin into the database
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, photo) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $uploadFile])) {
                // Redirect to admin_login.php after successful registration
                header('Location: admin_login.php');
                exit;
            } else {
                $error = "Failed to register admin";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration</title>
</head>
<body>
    <h1>Admin Registration</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>
        
        <label for="photo">Photo:</label>
        <input type="file" id="photo" name="photo"><br>
        
        <input type="submit" value="Register">
    </form>
</body>
</html>
