<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include '../db.php';

// Fetch all rooms
$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all messages
$messages = $pdo->query("SELECT m.id, u.username, r.name AS room_name, m.message, m.audio_url FROM messages m
                        JOIN users u ON m.user_id = u.id
                        JOIN rooms r ON m.room_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <h1>Admin Panel</h1>

    <h2>Manage Rooms</h2>
    <ul id="rooms-list">
        <?php foreach ($rooms as $room): ?>
            <li>
                <?= htmlspecialchars($room['name']) ?>
                <button onclick="deleteRoom(<?= $room['id'] ?>)">Delete</button>
            </li>
        <?php endforeach; ?>
    </ul>
    <form id="create-room-form">
        <input type="text" id="room-name" placeholder="Room Name" required>
        <button type="submit">Create Room</button>
    </form>

    <h2>Manage Messages</h2>
    <ul id="messages-list">
        <?php foreach ($messages as $message): ?>
            <li>
                <strong><?= htmlspecialchars($message['username']) ?> (<?= htmlspecialchars($message['room_name']) ?>):</strong>
                <?= htmlspecialchars($message['message']) ?>
                <?php if ($message['audio_url']): ?>
                    <audio controls src="<?= htmlspecialchars($message['audio_url']) ?>"></audio>
                <?php endif; ?>
                <button onclick="deleteMessage(<?= $message['id'] ?>)">Delete</button>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        document.getElementById('create-room-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const roomName = document.getElementById('room-name').value;

            fetch('admin_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'create_room', name: roomName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        function deleteRoom(roomId) {
            fetch('admin_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_room', room_id: roomId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function deleteMessage(messageId) {
            fetch('admin_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete_message', message_id: messageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>
