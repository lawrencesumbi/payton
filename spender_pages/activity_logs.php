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

// Get search term from URL
$searchTerm = $_GET['search'] ?? '';

// Build query with search filter
$query = "
    SELECT logs.*, users.fullname, users.role
    FROM logs
    JOIN users ON logs.user_id = users.id
    WHERE logs.user_id = ?
";

if (!empty($searchTerm)) {
    $query .= " AND (logs.action LIKE ? OR logs.action LIKE ? OR logs.action LIKE ?)";
}

$query .= " ORDER BY logs.created_at DESC";

$stmt = $conn->prepare($query);
$params = [$current_user_id];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

$stmt->execute($params);
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
/* ===== THEME VARIABLES ===== */
:root {
    --bg-body: #f1f5f9;
    --bg-card: #ffffff;
    --text-main: #0f172a;
    --text-muted: #475569;
    --text-light: #94a3b8;
    --border-shadow: rgba(0,0,0,0.05);
    --accent-purple-light: #f5f0ff;
}

[data-theme="dark"] {
    --bg-body: #12141a;
    --bg-card: #191c24;
    --text-main: #f8fafc;
    --text-muted: #94a3b8;
    --text-light: #64748b;
    --border-shadow: rgba(0,0,0,0.2);
    --accent-purple-light: #373250;
}

html::-webkit-scrollbar, 
body::-webkit-scrollbar {
    display: none;
    width: 0 !important;
}

.header {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header i {
            font-size: 24px;
            color: var(--accent-purple);
            background: var(--accent-purple-light);
            padding: 12px;
            border-radius: 12px;
        }


body {font-family:'Segoe UI',sans-serif; background: var(--bg-body); margin:0; color: var(--text-main); transition: background 0.3s ease;}
.container {width: 100%;}
h2 {margin-bottom:20px; padding: 0 10px;}
.log-card {background: var(--bg-card); border-radius:12px; padding:15px 20px; margin-bottom:12px; display:flex; justify-content:space-between; align-items: center; box-shadow:0 2px 8px var(--border-shadow); transition: background 0.3s ease;}
.left {display:flex; flex-direction:column; gap: 4px;}
.right {text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;}
.fullname {font-weight:600; color: var(--text-main);}
.action {color: var(--text-muted); font-size:14px;}
.time {font-size:13px; color: var(--text-light);}
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
    <div class="header">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <div>
            <h1 style="font-size: 24px; font-weight: 900; color: var(--text-main);">Activity Logs</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Monitor your activity.</p>
        </div>
    </div>

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
            <div class="action"><?= htmlspecialchars($row['action']); ?></div>
        </div>
        <div class="right">
            <div class="time"><?= timeAgo($row['created_at']); ?></div>
            <div class="badge <?= $class ?>"><?= ($class === 'login') ? 'ACCESS' : $class; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: var(--text-light);">
        <p>No activity history found.</p>
    </div>
<?php endif; ?>
</div>
</body>
</html>