<?php
// Admin Activity Logs

// Fetch all logs
$searchTerm = $_GET['search'] ?? '';

$query = "
    SELECT l.id, l.action, l.created_at, u.fullname as user_name 
    FROM logs l 
    JOIN users u ON l.user_id = u.id 
    WHERE 1=1
";

if (!empty($searchTerm)) {
    $query .= " AND (u.fullname LIKE ? OR l.action LIKE ?)";
}

$query .= " ORDER BY l.created_at DESC";

$stmt = $conn->prepare($query);
$params = [];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard];
}

$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="activity-logs">
    <header class="page-header">
        <div class="header-left">
            <span class="badge-accent">Activity Monitoring</span>
            <h1>System Activity Logs</h1>
            <p>Track and monitor all user activities and system events.</p>
        </div>
        <div class="header-right">
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>
    </header>

    <div class="glass-card table-container">
        <div class="table-header">
            <h3>Recent Activities</h3>
            <span class="record-count"><?php echo count($logs); ?> activities</span>
        </div>
        
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Activity</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($logs as $log): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo strtoupper(substr($log['user_name'], 0, 1)); ?></div>
                                <span><?php echo htmlspecialchars($log['user_name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(empty($logs)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-list-ul"></i>
            <h4>No activity logs found</h4>
            <p>The system has no recorded activities yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
:root {
    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --primary: #6366f1;
    --text-dark: #0f172a;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --radius-lg: 24px;
    --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.04);
}

[data-theme="dark"] {
    --bg-main: #0f111a;
    --card-bg: #191c24;
    --text-dark: #f8fafc;
    --text-muted: #94a3b8;
    --border: #2a2e39;
}

.activity-logs { 
    font-family: 'Inter', sans-serif;
    background: var(--bg-main);
    padding: 32px;
    color: var(--text-dark);
    min-height: 100vh;
}

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.badge-accent { 
    background: #e0e7ff; color: var(--primary); 
    padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
}
.page-header h1 { font-size: 32px; margin: 8px 0; font-weight: 800; letter-spacing: -0.03em; }
.page-header p { color: var(--text-muted); margin: 0; font-size: 15px; }

.btn-refresh {
    background: var(--card-bg); border: 1px solid var(--border);
    padding: 12px 20px; border-radius: 14px; font-weight: 600; cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;
}
.btn-refresh:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Table Container */
.glass-card { 
    background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); overflow: hidden;
}

.table-container { margin-bottom: 32px; }
.table-header { 
    padding: 24px; border-bottom: 1px solid var(--border); 
    display: flex; justify-content: space-between; align-items: center;
}
.table-header h3 { font-size: 17px; margin: 0; font-weight: 700; }
.record-count { font-size: 13px; color: var(--text-muted); font-weight: 500; }

.table-wrapper { overflow-x: auto; }
.modern-table { 
    width: 100%; border-collapse: collapse; 
}
.modern-table thead th { 
    background: var(--card-bg); color: var(--text-dark); font-weight: 600; font-size: 14px;
    padding: 16px 24px; text-align: left; border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 10;
}
.modern-table tbody td { 
    padding: 16px 24px; border-bottom: 1px solid var(--border); 
    vertical-align: middle;
}
.modern-table tbody tr:hover { background: var(--bg-main); }

/* User Cell */
.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar { 
    width: 32px; height: 32px; background: var(--bg-main); border-radius: 8px;
    display: grid; place-items: center; font-weight: 700; font-size: 12px; color: var(--primary);
    border: 1px solid var(--border);
}

/* Empty State */
.empty-state { 
    text-align: center; padding: 64px 24px; color: var(--text-muted);
}
.empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h4 { font-size: 18px; margin: 0 0 8px 0; color: var(--text-dark); }
.empty-state p { margin: 0; font-size: 14px; }

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .activity-logs { padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .table-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .modern-table thead th, .modern-table tbody td { padding: 12px 16px; }
    .user-cell { flex-direction: column; align-items: flex-start; gap: 4px; }
}
</style>
