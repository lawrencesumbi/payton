<?php
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$spender_id = $_SESSION['user_id']; 

// --- 1. HANDLE MARK ALL AS READ ---
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
    $stmt->execute([$spender_id]);
    echo "<script>window.location.href='?page=notifications';</script>";
    exit;
}

// Handle Single Notification Deletion
if (isset($_POST['delete_notification'])) {
    $del_id = $_POST['delete_notif_id'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $id]);
    echo "<script>window.location.href='?page=notifications';</script>";
    exit;
}


// --- 2. HANDLE ACCEPT INVITE ---
if(isset($_POST['accept_invite'])){
    $notification_id = $_POST['notification_id'];

    // Select type and parent_id specifically
    $stmt = $conn->prepare("SELECT parent_id, type FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $spender_id]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if it's actually an invite
    if($notif && $notif['type'] === 'invite' && !empty($notif['parent_id'])){
        $sponsor_id = $notif['parent_id'];

        // Link them (Use IGNORE or a check to prevent duplicates)
        $stmt = $conn->prepare("INSERT IGNORE INTO sponsor_spender (sponsor_id, spender_id) VALUES (?, ?)");
        $stmt->execute([$sponsor_id, $spender_id]);

        // Get Spender's name for the alert
        $stmtName = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
        $stmtName->execute([$spender_id]);
        $spender = $stmtName->fetch(PDO::FETCH_ASSOC);
        $spender_name = $spender['fullname'] ?? 'A user';

        // Notify the Sponsor
        $accept_message = "{$spender_name} has accepted your invitation and is now linked to your account.";
        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, type, message, status, parent_id) VALUES (?, 'accept_alert', ?, 'unread', ?)");
        $stmtNotif->execute([$sponsor_id, $accept_message, $spender_id]);

        // Mark as read
        $stmt = $conn->prepare("UPDATE notifications SET status='read' WHERE id=?");
        $stmt->execute([$notification_id]);
        
        echo "<script>window.location.href='?page=notifications';</script>";
        exit;
    }
}

// --- 3. FETCH NOTIFICATIONS (Updated to include type and parent_id) ---
$searchTerm = $_GET['search'] ?? '';

$query = "SELECT id, message, status, created_at, type, parent_id FROM notifications WHERE user_id=?";

if (!empty($searchTerm)) {
    $query .= " AND message LIKE ?";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$params = [$spender_id];

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
    <title>Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* [Keeping your existing CSS untouched for Dark Mode compatibility] */
        :root {
            --primary: #7c3aed; --primary-light: #f5f3ff; --unread-bg: #f0f7ff;
            --unread-border: #3b82f6; --text-main: #111827; --text-muted: #6b7280;
            --bg-body: #f9fafb; --bg-card: #ffffff; --border: #e5e7eb;
            --shadow: rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --bg-body: #12141a;
            --bg-card: #191c24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #2a2e39;
            --unread-bg: #1e293b;
            --unread-border: #3b82f6;
            --shadow: rgba(0,0,0,0.2);
        }

        body { background: var(--bg-body); color: var(--text-main); transition: background 0.3s ease; }
        /* ... existing styles ... */
        .notif-container { max-width: 800px; margin: 15px auto; padding: 0 20px; height: 75vh; overflow-y: auto; }
        .notification-card { background: var(--bg-card); padding: 20px; border-radius: 16px; margin-bottom: 12px; border: 1px solid var(--border); display: flex; gap: 16px; position: relative; transition: background 0.3s ease; box-shadow: 0 2px 4px var(--shadow); }
        .page-header {display: flex;justify-content: space-between;align-items: center;margin-bottom: 20px;}
        .mark-read {font-size: 0.9rem;color: var(--accent-purple);text-decoration: none; font-weight: 600;}
        .mark-read:hover {text-decoration: underline;}
        .notification-card.unread { background: var(--unread-bg); border-color: #dbeafe; }
        .icon-box { width: 44px; height: 44px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .btn { padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-ghost { background: #e5e7eb; color: #4b5563; margin-left: 8px; }
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
                        <div class="action-row" style="margin-top:15px;">
                            <form method="POST">
                                <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" name="accept_invite" class="btn btn-primary">Accept Invite</button>
                                <button type="button" class="btn btn-ghost" onclick="this.form.style.display='none'">Decline</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-envelope-open"></i>
            <p>All caught up!</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>