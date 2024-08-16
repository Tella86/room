<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['primary_color'], $data['secondary_color'])) {
    $admin_id = $_SESSION['admin_id'];
    $primary_color = $data['primary_color'];
    $secondary_color = $data['secondary_color'];

    $stmt = $pdo->prepare("INSERT INTO settings (admin_id, primary_color, secondary_color)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE primary_color = VALUES(primary_color), secondary_color = VALUES(secondary_color)");
    if ($stmt->execute([$admin_id, $primary_color, $secondary_color])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save theme colors']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
