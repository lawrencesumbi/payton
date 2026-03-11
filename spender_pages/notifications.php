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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <style>
        :root {
            --primary: #7c3aed;
            --primary-light: #f5f3ff;
            --unread-bg: #f0f7ff;
            --unread-border: #3b82f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --bg-body: #f9fafb;
            --white: #ffffff;
            --border: #e5e7eb;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); }

        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }

        /* --- HEADER SECTION --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .page-header h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
        .mark-read { 
            font-size: 0.85rem; color: var(--primary); font-weight: 600; 
            text-decoration: none; cursor: pointer;
        }

        /* --- NOTIFICATION CARD --- */
        .notification-card {
            background: var(--white);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
            display: flex;
            gap: 16px;
            transition: all 0.2s ease;
            position: relative;
        }

        /* Unread State: Subtle blue tint and a dot */
        .notification-card.unread {
            background: var(--unread-bg);
            border-color: #dbeafe;
        }
        .notification-card.unread::after {
            content: "";
            position: absolute;
            top: 20px;
            right: 20px;
            width: 8px;
            height: 8px;
            background: var(--unread-border);
            border-radius: 50%;
        }

        .icon-box {
            width: 44px;
            height: 44px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }

        .content-box { flex: 1; }
        .message { font-size: 0.95rem; font-weight: 500; margin-bottom: 4px; color: #374151; }
        .time { font-size: 0.8rem; color: var(--text-muted); font-weight: 400; }

        /* --- ACTIONS --- */
        .action-row { margin-top: 14px; display: flex; gap: 8px; }
        
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #6d28d9; transform: translateY(-1px); }
        
        .btn-ghost { background: #e5e7eb; color: #4b5563; }
        .btn-ghost:hover { background: #d1d5db; }

        /* --- EMPTY STATE --- */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 16px; opacity: 0.3; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>Notifications</h1>
        <?php if(count($notifications) > 0): ?>
            <a href="#" class="mark-read">Mark all as read</a>
        <?php endif; ?>
    </div>

    <?php if(count($notifications) > 0): ?>
        <?php foreach($notifications as $n): ?>
            <div class="notification-card <?php echo $n['status'] == 'unread' ? 'unread' : ''; ?>">
                <div class="icon-box">
                    <i class="fa-solid <?php echo strpos($n['message'], 'invite') !== false ? 'fa-user-plus' : 'fa-bell'; ?>"></i>
                </div>
                
                <div class="content-box">
                    <p class="message"><?php echo htmlspecialchars($n['message']); ?></p>
                    <div class="time">
                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                        <?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?>
                    </div>

                    <?php if($n['status'] == 'unread' && strpos($n['message'], 'invite') !== false): ?>
                        <div class="action-row">
                            <form method="POST">
                                <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                <button type="submit" name="accept_invite" class="btn btn-primary">Accept Invite</button>
                                <button type="button" class="btn btn-ghost">Decline</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-envelope-open"></i>
            <p>All caught up! No new notifications.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>