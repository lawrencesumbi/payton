<?php
require_once "db.php";

$sponsor_id = $_SESSION['user_id'];

// Handle Mark All as Read
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ?");
    $stmt->execute([$sponsor_id]);
    echo "<script>window.location.href='?page=notifications';</script>";
    exit;
}

// Fetch all notifications
$stmt = $conn->prepare("SELECT id, message, status, created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$sponsor_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="notif-container" style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-weight: 800;">Notifications</h2>
        <?php if(count($notifications) > 0): ?>
            <a href="?page=notifications&action=mark_all_read" style="color: var(--accent-purple); font-size: 14px; font-weight: 600; text-decoration: none;">Mark all as read</a>
        <?php endif; ?>
    </div>

    <?php if(count($notifications) > 0): ?>
        <?php foreach($notifications as $n): ?>
            <div style="background: var(--bg-card); padding: 20px; border-radius: 12px; margin-bottom: 10px; border: 1px solid var(--border-color); display: flex; gap: 15px; position: relative; <?php echo $n['status'] == 'unread' ? 'border-left: 4px solid var(--accent-purple);' : ''; ?>">
                <div style="background: var(--sidebar-active); color: var(--accent-purple); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <p style="font-size: 14px; font-weight: 500; color: var(--text-main); margin-bottom: 4px;"><?php echo htmlspecialchars($n['message']); ?></p>
                    <span style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-clock"></i> <?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fa-solid fa-bell-slash" style="font-size: 40px; opacity: 0.2; margin-bottom: 15px;"></i>
            <p>No notifications yet.</p>
        </div>
    <?php endif; ?>
</div>