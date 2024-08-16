<?php
session_start();

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

include 'db.php';

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
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="dashboard.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/howler"></script>
    <style>
    :root {
        --primary-color: <?=htmlspecialchars($primary_color) ?>;
        --secondary-color: <?=htmlspecialchars($secondary_color) ?>;
    }

    .navbar,
    .sidebar {
        background-color: var(--primary-color);
    }

    .nav-link,
    .sidebar-sticky .nav-item .nav-link {
        color: var(--secondary-color);
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
    }

    .container-fluid {
        padding: 0;
    }

    .sidebar {
        height: 100vh;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar .nav-link {
        color: #333;
    }

    .sidebar .nav-link.active {
        background-color: #0056b3;
        color: #fff;
    }

    .admin-photo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: block;
        margin: 20px auto;
    }

    .chat-box {
        height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 20px;
        background: #fff;
        border-radius: 5px;
    }

    .message-form {
        display: flex;
        gap: 10px;
    }

    .message-form input.form-control {
        flex: 1;
    }

    .message-form button.btn {
        flex-shrink: 0;
    }

    .login-container {
        max-width: 400px;
        margin: 50px auto;
        padding: 20px;
        background: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        text-align: center;
    }

    .login-container h1 {
        margin-bottom: 20px;
        color: #0056b3;
    }

    .login-container form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-group-item audio {
        margin-top: 5px;
    }
    </style>
</head>

<body data-user-id="<?= $_SESSION['admin_id'] ?>">
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar"  style="background-color: coral;">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_chat.php">
                            <i class="material-icons"></i> 
                            <i class="bi bi-bar-chart"></i>
                                Dashboard
                            </a>
                        </li>
                        <!-- <ul class="nav flex-column"> -->
                        <li class="nav-item">
                            <a class="nav-link" href="../hms/admin/dashboard.php" target="_blank">
                                <!-- <i class="fas fa-home"></i> -->
                                <i class="bi bi-house-door"></i>
                                Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="chat-link">
                                <i class="fas fa-comments"></i>
                                Chat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="panel-link">
                                <i class="fas fa-tachometer-alt"></i>
                                Panel
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="settings-link">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2" style="color: coral">Dashboard</h1>&nbsp;&nbsp;<h9 style="color: #007bff;">Welcome,
                        <?= htmlspecialchars($admin['username']) ?></h9>
                    <div><?php if (!empty($admin['photo'])): ?>
                        <img src="<?= htmlspecialchars($admin['photo']) ?>" alt="Admin Photo" class="admin-photo">
                        <?php endif; ?>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <div id="chat-section" style="display: none;">
                    <label for="room-select">Select Room:</label>
                    <select id="room-select" class="form-control">
                        <option value="">..select room...</option>
                        <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room['id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="chat-box" id="chat-box">
                        <!-- Messages will be displayed here -->
                    </div>
                    <form id="message-form" class="message-form">
                        <input type="text" id="message" placeholder="Type a message" required class="form-control">
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i></button>
                        <button id="record-button" class="btn btn-danger"><i class="fas fa-microphone"></i>
                            Recording</button>
                    </form>
                </div>

                <div id="panel-section" style="display: none;">
                    <h2>Manage Rooms</h2>
                    <ul id="rooms-list" class="list-group">
                        <?php foreach ($rooms as $room): ?>
                        <li class="list-group-item">
                            <?= htmlspecialchars($room['name']) ?>
                            <button class="btn btn-danger btn-sm"
                                onclick="deleteRoom(<?= $room['id'] ?>)">Delete</button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <form id="create-room-form" class="message-form">
                        <input type="text" id="room-name" placeholder="Room Name" required class="form-control">
                        <button type="submit" class="btn btn-primary mt-2">Create Room</button>
                    </form>

                    <h2>Manage Messages</h2>
                    <ul id="messages-list" class="list-group">
                        <?php
                        $messages = $pdo->query("SELECT m.id, u.username, r.name AS room_name, m.message, m.audio_url FROM messages m
                                                 JOIN users u ON m.user_id = u.id
                                                 JOIN rooms r ON m.room_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($messages as $message): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($message['username']) ?>
                                (<?= htmlspecialchars($message['room_name']) ?>):</strong>
                            <?= htmlspecialchars($message['message']) ?>
                            <?php if ($message['audio_url']): ?>
                            <audio controls src="<?= htmlspecialchars($message['audio_url']) ?>" class="mt-2"></audio>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm mt-2"
                                onclick="deleteMessage(<?= $message['id'] ?>)">Delete</button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div id="settings-section" style="display: none;">
                    <h2>Theme Settings</h2>
                    <form id="theme-form">
                        <div class="form-group">
                            <label for="primary-color">Primary Color:</label>
                            <input type="color" id="primary-color" name="primary_color"
                                value="<?= htmlspecialchars($primary_color) ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="secondary-color">Secondary Color:</label>
                            <input type="color" id="secondary-color" name="secondary_color"
                                value="<?= htmlspecialchars($secondary_color) ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Theme</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
    document.getElementById('chat-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('chat-section').style.display = 'block';
        document.getElementById('panel-section').style.display = 'none';
        document.getElementById('settings-section').style.display = 'none';
    });

    document.getElementById('panel-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('panel-section').style.display = 'block';
        document.getElementById('chat-section').style.display = 'none';
        document.getElementById('settings-section').style.display = 'none';
    });

    document.getElementById('settings-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('settings-section').style.display = 'block';
        document.getElementById('chat-section').style.display = 'none';
        document.getElementById('panel-section').style.display = 'none';
    });

    document.getElementById('theme-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const primaryColor = document.getElementById('primary-color').value;
        const secondaryColor = document.getElementById('secondary-color').value;

        fetch('save_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    primary_color: primaryColor,
                    secondary_color: secondaryColor
                })
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

    document.getElementById('create-room-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const roomName = document.getElementById('room-name').value;

        fetch('admin_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create_room',
                    name: roomName
                })
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
                body: JSON.stringify({
                    action: 'delete_room',
                    room_id: roomId
                })
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
                body: JSON.stringify({
                    action: 'delete_message',
                    message_id: messageId
                })
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
    <script src="adminchat.js"></script>
</body>

</html>