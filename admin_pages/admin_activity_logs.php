<?php
// Admin Activity Logs

// Fetch all logs
$stmt = $conn->prepare("
    SELECT l.id, l.action, l.created_at, u.fullname as user_name 
    FROM logs l 
    JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC
");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="activity-logs">
    <h2>Activity Logs</h2>
    
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
            <tr>
                <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                <td><?php echo htmlspecialchars($log['action']); ?></td>
                <td><?php echo $log['created_at']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.activity-logs { padding: 20px; }
table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
th { background: var(--bg-sidebar); font-weight: 600; }
</style>
