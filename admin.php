<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include 'db.php';

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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-home"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-comments"></i>
                                Chat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Panel</h1>
                </div>

                <h2>Manage Rooms</h2>
                <ul id="rooms-list" class="list-group mb-3">
                    <?php foreach ($rooms as $room): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($room['name']) ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteRoom(<?= $room['id'] ?>)">Delete</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form id="create-room-form" class="mb-3">
                    <input type="text" id="room-name" placeholder="Room Name" required class="form-control mb-2">
                    <button type="submit" class="btn btn-primary
                    ">Create Room</button>
</form>
<h2>Manage Messages</h2>
            <ul id="messages-list" class="list-group">
                <?php foreach ($messages as $message): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($message['username']) ?> (<?= htmlspecialchars($message['room_name']) ?>):</strong>
                        <?= htmlspecialchars($message['message']) ?>
                        <?php if ($message['audio_url']): ?>
                            <audio controls src="<?= htmlspecialchars($message['audio_url']) ?>" class="mt-2"></audio>
                        <?php endif; ?>
                        <button class="btn btn-danger btn-sm mt-2" onclick="deleteMessage(<?= $message['id'] ?>)">Delete</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </main>
    </div>
</div>

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
