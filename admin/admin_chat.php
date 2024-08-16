<?php
session_start();

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

include '../db.php';

// Fetch admin details
$stmt = $pdo->prepare("SELECT username, photo FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Fetch all rooms
$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat</title>
    <script src="https://cdn.jsdelivr.net/npm/howler"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="../chat.css">
</head>
<body data-user-id="<?= $_SESSION['admin_id'] ?>">
    <div class="chat-container">
        <h1 style="color: #0056b3">Welcome: <?= htmlspecialchars($admin['username']) ?></h1>
        <?php if (!empty($admin['photo'])): ?>
            <img src="<?= htmlspecialchars($admin['photo']) ?>" alt="Admin Photo" style="width: 50px; height: 50px; border-radius: 50%;">
        <?php endif; ?>
        <label for="room-select">Select Room:</label>
        <select id="room-select">
            <option value="">..select room...</option>
            <?php foreach ($rooms as $room): ?>
                <option value="<?= $room['id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="chat-box" id="chat-box">
            <!-- Messages will be displayed here -->
        </div>
        <form id="message-form">
            <input type="text" id="message" placeholder="Type a message" required>
            <button type="submit"><i class="fas fa-paper-plane" style="color: green;"></i></button>
            <button id="record-button"><i class="fas fa-microphone" style="color: red;"></i> Recording</button>
            </form>
        </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="adminchat.js"></script>
</body>
</html>
