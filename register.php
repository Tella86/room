<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        echo "Username and password cannot be empty.";
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, photo) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $hashedPassword, $photo])) {
            echo "Registration successful. You can now login.";
            header('Location: index.html');
            exit;
        } else {
            echo "Error: Could not register user.";
        }
    } catch (PDOException $e) {
        // Log the error message (for developers)
        error_log($e->getMessage());

        // Display a generic error message (for users)
        echo "Error: Could not register user. Please try again later.";
    }
}

