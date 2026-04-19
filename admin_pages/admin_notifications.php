<?php
$admin_id = $_SESSION['user_id'];

// Handle Mark All as Read
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
    $stmt->execute([$admin_id]);
    echo "<script>window.location.href='?page=notifications';</script>";
    exit;
}

// Handle Single Notification Deletion
if (isset($_POST['delete_notification'])) {
    $del_id = $_POST['delete_notif_id'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $admin_id]);
    echo "<script>window.location.href='?page=notifications';</script>";
    exit;
}

// --- 2. HANDLE ACCEPT INVITE ---
if(isset($_POST['accept_invite'])){
    $notification_id = $_POST['notification_id'];

    // Select type and parent_id specifically
    $stmt = $conn->prepare("SELECT parent_id, type FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $admin_id]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if it's actually an invite
    if($notif && $notif['type'] === 'invite' && !empty($notif['parent_id'])){
        $sponsor_id = $notif['parent_id'];

        // Link them (Use IGNORE or a check to prevent duplicates)
        $stmt = $conn->prepare("INSERT IGNORE INTO sponsor_spender (sponsor_id, spender_id) VALUES (?, ?)");
        $stmt->execute([$sponsor_id, $admin_id]);

        // Get Admin's name for the alert
        $stmtName = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
        $stmtName->execute([$admin_id]);
        $admin = $stmtName->fetch(PDO::FETCH_ASSOC);
        $admin_name = $admin['fullname'] ?? 'An admin user';

        // Notify the Sponsor
        $accept_message = "{$admin_name} has accepted your invitation and is now linked to your account.";
        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, type, message, status, parent_id) VALUES (?, 'info', ?, 'unread', ?)");
        $stmtNotif->execute([$sponsor_id, $accept_message, $admin_id]);

        // Mark as read
        $stmt = $conn->prepare("UPDATE notifications SET status='read' WHERE id=?");
        $stmt->execute([$notification_id]);
        
        echo "<script>window.location.href='?page=notifications';</script>";
        exit;
    }
}

// Fetch all notifications
$searchTerm = $_GET['search'] ?? '';

$query = "SELECT id, message, status, created_at, type, parent_id FROM notifications WHERE user_id=?";

if (!empty($searchTerm)) {
    $query .= " AND message LIKE ?";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$params = [$admin_id];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params[] = $searchWildcard;
}

$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #f5f3ff;
            --unread-bg: #f0f7ff;
            --unread-border: #3b82f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --bg-body: #f9fafb;
            --bg-card: #ffffff;
            --border: #e5e7eb;
            --shadow: rgba(0,0,0,0.05);
            --danger: #ef4444;
        }

        [data-theme="dark"] {
            --bg-body: #0f111a;
            --bg-card: #191c24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #2a2e39;
            --unread-bg: #1e293b;
            --unread-border: #3b82f6;
            --shadow: rgba(0,0,0,0.2);
        }

        .notif-container { max-width: 800px; margin: 0 auto; padding: 0 20px; height: 75vh; overflow-y: auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .mark-read { font-size: 0.9rem; color: var(--primary); text-decoration: none; font-weight: 600; }
        .mark-read:hover { text-decoration: underline; }
        .notification-card { background: var(--bg-card); padding: 20px; border-radius: 16px; margin-bottom: 12px; border: 1px solid var(--border); display: flex; gap: 16px; position: relative; transition: background 0.3s ease; box-shadow: 0 2px 4px var(--shadow); }
        .notification-card.unread { background: var(--unread-bg); border-color: var(--unread-border); }
        .icon-box { width: 44px; height: 44px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .content-box { flex: 1; }
        .message { margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; }
        .time { margin: 0; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
        .action-row { margin-top: 15px; }
        .btn { padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-ghost { background: #e5e7eb; color: #4b5563; margin-left: 8px; }
        .btn-ghost:hover { background: #d1d5db; }
        .empty-state { text-align: center; padding: 50px; color: var(--text-muted); }
        .empty-state i { font-size: 40px; opacity: 0.2; margin-bottom: 15px; }
        .delete-btn { position: absolute; top: 10px; right: 10px; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 14px; }
        .delete-btn:hover { color: var(--danger); }
    </style>
</head>
<body>

<div class="notif-container">
    <div class="page-header">
        <h1>Notifications</h1>
        
        <?php 
        $hasUnread = array_filter($notifications, fn($n) => $n['status'] == 'unread');
        if($hasUnread): ?>
            <a href="?page=notifications&action=mark_all_read" class="mark-read">
                Mark all as read
            </a>
        <?php endif; ?>
    </div>

    <?php if(count($notifications) > 0): ?>
        <?php foreach($notifications as $n): ?>
            <div class="notification-card <?php echo $n['status'] == 'unread' ? 'unread' : ''; ?>">
                <div class="icon-box">
                    <i class="fa-solid <?php echo ($n['type'] === 'invite') ? 'fa-user-plus' : 'fa-bell'; ?>"></i>
                </div>
                
                <div class="content-box">
                    <p class="message"><?php echo htmlspecialchars($n['message']); ?></p>
                    <div class="time">
                        <i class="fa-regular fa-clock"></i>
                        <?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?>
                    </div>

                    <?php if($n['type'] === 'invite' && $n['status'] === 'unread'): ?>
                        <div class="action-row">
                            <form method="POST">
                                <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" name="accept_invite" class="btn btn-primary">Accept Invite</button>
                                <button type="button" class="btn btn-ghost" onclick="this.form.style.display='none'">Decline</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="post" style="display: inline;">
                    <input type="hidden" name="delete_notif_id" value="<?php echo $n['id']; ?>">
                    <button type="submit" name="delete_notification" class="delete-btn" onclick="return confirm('Are you sure you want to delete this notification?');">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-bell-slash"></i>
            <p>No notifications yet.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
