<?php

require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$spender_id = $_SESSION['user_id'];

// Handle Accept Invite
if(isset($_POST['accept_invite'])){
    $notification_id = $_POST['notification_id'];

    // Fetch the notification from DB
    $stmt = $conn->prepare("SELECT parent_id FROM notifications WHERE id = ? AND user_id = ? AND type='invite'");
    $stmt->execute([$notification_id, $spender_id]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    if($notif && !empty($notif['parent_id'])){
        $sponsor_id = $notif['parent_id'];

        // Insert into sponsor_spender table
        $stmt = $conn->prepare("INSERT INTO sponsor_spender (sponsor_id, spender_id) VALUES (?, ?)");
        $stmt->execute([$sponsor_id, $spender_id]);

        // Mark notification as read
        $stmt = $conn->prepare("UPDATE notifications SET status='read' WHERE id=?");
        $stmt->execute([$notification_id]);
    }
}

// Fetch all notifications for this spender
$stmt = $conn->prepare("SELECT id, message, status, created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$spender_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications</title>
<style>
body { font-family: Arial; background:#f5f3ff; color:#1f2937; }
.container { max-width:800px; margin:30px auto; padding:20px; }
.notification-card { background:white; padding:15px; border-radius:12px; margin-bottom:10px; border-left:4px solid #7c3aed; }
.notification-card.unread { background:#e0d7ff; }
.notification-card p { margin:0; }
.time { font-size:0.8rem; color:#666; margin-top:5px; }
.btn-primary { background:#7c3aed; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; }
</style>
</head>
<body>
<div class="container">
<h1>Notifications</h1>

<?php if(count($notifications) > 0): ?>
    <?php foreach($notifications as $n): ?>
        <div class="notification-card <?php echo $n['status']=='unread'?'unread':''; ?>">
            <p><?php echo htmlspecialchars($n['message']); ?></p>
            <div class="time"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></div>
            <?php if($n['status']=='unread'): ?>
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                    <button type="submit" name="accept_invite" class="btn-primary">Accept Invite</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No notifications yet.</p>
<?php endif; ?>
</div>
</body>
</html>