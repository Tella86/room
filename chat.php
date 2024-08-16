<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

include 'db.php';

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$roomsStmt = $pdo->query("SELECT * FROM rooms ORDER BY name ASC");
$rooms = $roomsStmt->fetchAll();

$messageCountStmt = $pdo->query("SELECT COUNT(*) as message_count FROM messages");
$messageCount = $messageCountStmt->fetch()['message_count'];

$userCountStmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
$userCount = $userCountStmt->fetch()['user_count'];

$recentMessagesStmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
$recentMessages = $recentMessagesStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body data-user-id="<?php echo $_SESSION['user_id']; ?>">
    <div class="container mt-5">
        <h1 class="text-center">Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total Messages</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $messageCount; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $userCount; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Recent Messages</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($recentMessages as $message): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($message['message']); ?> <span class="text-muted">by <?php echo htmlspecialchars($user['username']); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <label for="room-select">Select Room:</label>
                <select id="room-select" class="form-control">
                    <option value="">...Select room...</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <form id="create-room-form" class="form-inline">
                    <input type="text" id="new-room-name" class="form-control mr-2" placeholder="New Room Name" required>
                    <button type="submit" class="btn btn-primary">Create Room</button>
                </form>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="chat-box" id="chat-box">
                    <!-- Messages will be displayed here -->
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <form id="message-form" class="form-inline">
                    <input type="text" id="message" class="form-control mr-2" placeholder="Type a message" required>
                    <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i></button>
                    <button id="record-button" class="btn btn-danger ml-2"><i class="fas fa-microphone"></i> Record</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
