<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

function respond($success, $data = []) {
    echo json_encode(array_merge(['success' => $success], $data));
    exit;
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    respond(false, ['error' => 'User ID or Admin ID not set in session']);
}

$user_id = $_SESSION['user_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['audio'])) {
        handleAudioUpload($pdo, $user_id, $admin_id);
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        handleJSONInput($pdo, $input, $user_id, $admin_id);
    }
} else {
    if (!isset($_GET['room_id'])) {
        respond(false, ['error' => 'Room ID not specified']);
    }
    $room_id = $_GET['room_id'];
    fetchMessages($pdo, $room_id);
}

function handleAudioUpload($pdo, $user_id, $admin_id) {
    $room_id = $_POST['room_id'];
    $uploadDir = 'admin/uploads/';
    $uploadFile = $uploadDir . basename($_FILES['audio']['name']);

    if (move_uploaded_file($_FILES['audio']['tmp_name'], $uploadFile)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, admin_id, room_id, audio_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $admin_id, $room_id, $uploadFile])) {
            $username = $user_id ? getUsername($pdo, $user_id) : getAdminName($pdo, $admin_id);
            $photo = $user_id ? getPhoto($pdo, $user_id) : getAdminPhoto($pdo, $admin_id);
            respond(true, ['username' => $username, 'audio_url' => $uploadFile, 'message_id' => $pdo->lastInsertId(), 'photo' => $photo]);
        } else {
            respond(false, ['error' => 'Failed to insert audio message']);
        }
    } else {
        respond(false, ['error' => 'Failed to upload audio file']);
    }
}

function handleJSONInput($pdo, $input, $user_id, $admin_id) {
    if (!isset($input['action'])) {
        respond(false, ['error' => 'Action not specified']);
    }

    $action = $input['action'];
    switch ($action) {
        case 'send':
            handleSendMessage($pdo, $input, $user_id, $admin_id);
            break;
        case 'delete':
            handleDeleteMessage($pdo, $input, $user_id, $admin_id);
            break;
        default:
            respond(false, ['error' => 'Invalid action']);
    }
}

function handleSendMessage($pdo, $input, $user_id, $admin_id) {
    if (!isset($input['message']) || !isset($input['room_id'])) {
        respond(false, ['error' => 'Message or room_id not specified']);
    }

    $message = $input['message'];
    $room_id = $input['room_id'];

    $stmt = $pdo->prepare("INSERT INTO messages (user_id, admin_id, room_id, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $admin_id, $room_id, $message])) {
        $photo = $user_id ? getPhoto($pdo, $user_id) : getAdminPhoto($pdo, $admin_id);
        $username = $user_id ? getUsername($pdo, $user_id) : getAdminName($pdo, $admin_id);
        respond(true, ['photo' => $photo, 'username' => $username, 'message_id' => $pdo->lastInsertId()]);
    } else {
        respond(false, ['error' => 'Failed to insert message']);
    }
}

function handleDeleteMessage($pdo, $input, $user_id, $admin_id) {
    if (!isset($input['message_id'])) {
        respond(false, ['error' => 'Message ID not specified']);
    }

    $message_id = $input['message_id'];

    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND (user_id = ? OR admin_id = ?)");
    if ($stmt->execute([$message_id, $user_id, $admin_id])) {
        respond(true);
    } else {
        respond(false, ['error' => 'Failed to delete message']);
    }
}

function fetchMessages($pdo, $room_id) {
    $stmt = $pdo->prepare("SELECT users.username, messages.message, messages.audio_url, messages.id, COALESCE(users.photo, admins.photo) AS photo
                           FROM messages 
                           LEFT JOIN users ON messages.user_id = users.id 
                           LEFT JOIN admins ON messages.admin_id = admins.id
                           WHERE messages.room_id = ? 
                           ORDER BY messages.created_at ASC");
    $stmt->execute([$room_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond(true, ['messages' => $messages]);
}

function getPhoto($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getAdminPhoto($pdo, $admin_id) {
    $stmt = $pdo->prepare("SELECT photo FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    return $stmt->fetchColumn();
}

function getUsername($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getAdminName($pdo, $admin_id) {
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    return $stmt->fetchColumn();
}
?>
