<?php
session_start();
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action'])) {
    switch ($data['action']) {
        case 'create_room':
            $name = $data['name'];
            $stmt = $pdo->prepare("INSERT INTO rooms (name) VALUES (?)");
            if ($stmt->execute([$name])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create room']);
            }
            break;

        case 'delete_room':
            $room_id = $data['room_id'];
            $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            if ($stmt->execute([$room_id])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete room']);
            }
            break;

        case 'delete_message':
            $message_id = $data['message_id'];
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            if ($stmt->execute([$message_id])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete message']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
}
?>
