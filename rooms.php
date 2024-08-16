<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'];

    $stmt = $pdo->prepare("INSERT INTO rooms (name) VALUES (?)");
    if ($stmt->execute([$name])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create room']);
    }
    exit;
} else {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY name ASC");
    $rooms = $stmt->fetchAll();
    echo json_encode($rooms);
}
?>
