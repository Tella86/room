<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

// Check if user_id or admin_id are set in session
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'User ID or Admin ID not set in session']);
    exit;
}

// Initialize user_id and admin_id to avoid undefined array key warning
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

// Fetch the username from the database if user_id is set
if ($user_id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found.']);
        exit;
    }

    $username = $user['username'];
}

// Fetch the username from the database if admin_id is set
if ($admin_id) {
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

    if (!$admin) {
        echo json_encode(['success' => false, 'error' => 'Admin not found.']);
        exit;
    }

    $username = $admin['username'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = $_POST['room_id'];
    $audio = $_FILES['audio'];

    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = uniqid() . '.wav';
    $uploadFile = $uploadDir . basename($fileName);

    if (move_uploaded_file($audio['tmp_name'], $uploadFile)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, admin_id, room_id, audio_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $admin_id, $room_id, $uploadFile])) {
            echo json_encode(['success' => true, 'username' => $username, 'audio_url' => $uploadFile]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save audio message.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload audio file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
