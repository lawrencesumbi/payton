<?php
// Set timezone at the very top to ensure time calculations match the database immediately
date_default_timezone_set('Asia/Manila');

include 'db.php';
include 'log_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Fetch logs with fullname
$stmt = $conn->prepare("
    SELECT logs.*, users.fullname, users.role
    FROM logs
    JOIN users ON logs.user_id = users.id
    WHERE logs.user_id = ?
    ORDER BY logs.created_at DESC
");
$stmt->execute([$current_user_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function timeAgo($datetime) {
    $log_time = strtotime($datetime);
    $current_time = time();
    $diff = $current_time - $log_time;

    // Fix for negative seconds or very recent actions
    if ($diff < 60) {
        return "Just now";
    }
    if ($diff < 3600) {
        return floor($diff / 60) . " min ago";
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . " hr ago";
    }
    return date("M d, Y h:i A", $log_time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Activity Logs</title>
<style>
body {font-family:'Segoe UI',sans-serif; background:#f1f5f9; margin:0;}
.container {max-width:900px; margin:30px auto;}
h2 {margin-bottom:20px; padding: 0 10px;}
.log-card {background:#fff; border-radius:12px; padding:15px 20px; margin-bottom:12px; display:flex; justify-content:space-between; align-items: center; box-shadow:0 2px 8px rgba(0,0,0,0.05);}
.left {display:flex; flex-direction:column; gap: 4px;}
.right {text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;}
.fullname {font-weight:600; color:#0f172a;}
.action {color:#475569; font-size:14px;}
.time {font-size:13px; color:#94a3b8;}
.badge {padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; text-transform: uppercase;}

/* Badge Colors */
.login {background:#e0f2fe; color:#0369a1;}
.add {background:#dcfce7; color:#166534;}
.edit {background:#eff6ff; color:#1e40af;} /* Updated to match update/edit logic */
.delete {background:#fee2e2; color:#991b1b;}
.default {background:#f1f5f9; color:#475569;}
</style>
</head>
<body>
<div class="container">
<h2>📊 Activity Logs</h2>

<?php if(!empty($logs)): ?>
    <?php foreach($logs as $row): 
        $actionText = strtolower($row['action']);
        $class = "default";
        
        // Categorize the action for the badge
        if(strpos($actionText,'add')!==false) $class='add';
        elseif(strpos($actionText,'edit')!==false || strpos($actionText,'update')!==false) $class='edit';
        elseif(strpos($actionText,'delete')!==false) $class='delete';
        elseif(strpos($actionText,'logged in')!==false) $class='login';
    ?>
    <div class="log-card">
        <div class="left">
            <div class="fullname">
                <?= htmlspecialchars($row['fullname']); ?> 
                <span style="font-weight: 400; opacity: 0.6; font-size: 0.9em;">(<?= htmlspecialchars($row['role']); ?>)</span>
            </div>
            <div class="action"><?= htmlspecialchars($row['action']); ?></div>
        </div>
        <div class="right">
            <div class="time"><?= timeAgo($row['created_at']); ?></div>
            <div class="badge <?= $class ?>"><?= ($class === 'login') ? 'ACCESS' : $class; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #64748b;">
        <p>No activity history found.</p>
    </div>
<?php endif; ?>
</div>
</body>
</html>