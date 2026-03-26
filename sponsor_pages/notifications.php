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

// Handle Single Notification Deletion
if (isset($_POST['delete_notification'])) {
    $del_id = $_POST['delete_notif_id'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $id]);
    echo "<script>window.location.href='?page=notifications';</script>";
    exit;
}

// Fetch all notifications
$stmt = $conn->prepare("SELECT id, message, status, created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$sponsor_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<style>
    /* Scoped to container to avoid interfering with dashboard layout */
        .notif-container { 
    max-width: 700px; 
    margin: 20px auto; 
    padding: 0 20px;
    
    /* --- ADD THESE LINES --- */
    height: 75vh; /* Limits height to 80% of the screen height */
    overflow-y: auto; /* Adds a scrollbar only when content overflows */
    padding-right: 10px; /* Space for the scrollbar */
}

/* Optional: Make the scrollbar look cleaner and modern */
.notif-container::-webkit-scrollbar {
    width: 6px;
}
.notif-container::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.notif-container::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}
</style>
    

<div class="notif-container">
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

</body>
</html>
