<?php
// Admin Scheduler Oversight

// Handle delete scheduled payment
if (isset($_POST['delete_schedule'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM scheduled_payments WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>alert('Scheduled payment deleted successfully');</script>";
}

// Fetch all scheduled payments
$searchTerm = $_GET['search'] ?? '';

$query = "
    SELECT sp.id, sp.amount, sp.due_date, sp.payment_name, sp.due_status_id, ds.due_status_name as status, u.fullname as user_name 
    FROM scheduled_payments sp 
    JOIN users u ON sp.user_id = u.id 
    LEFT JOIN due_status ds ON sp.due_status_id = ds.id
    WHERE 1=1
";

if (!empty($searchTerm)) {
    $query .= " AND (u.fullname LIKE ? OR sp.payment_name LIKE ? OR ds.due_status_name LIKE ?)";
}

$query .= " ORDER BY sp.due_date ASC";

$stmt = $conn->prepare($query);
$params = [];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard];
}

$stmt->execute($params);
$scheduled_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="scheduler-oversight">
    <header class="page-header">
        <div class="header-left">
            <span class="badge-accent">Scheduler Oversight</span>
            <h1>Payment Schedules</h1>
            <p>Monitor and manage all scheduled payment obligations across the platform.</p>
        </div>
        <div class="header-right">
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>
    </header>

    <div class="glass-card table-container">
        <div class="table-header">
            <h3>Scheduled Payments</h3>
            <span class="record-count"><?php echo count($scheduled_payments); ?> records</span>
        </div>
        
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Due Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($scheduled_payments as $sp): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo strtoupper(substr($sp['user_name'], 0, 1)); ?></div>
                                <span><?php echo htmlspecialchars($sp['user_name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($sp['due_date'])); ?></td>
                        <td><?php echo htmlspecialchars($sp['payment_name'] ?? 'N/A'); ?></td>
                        <td class="amount-cell">₱<?php echo number_format($sp['amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($sp['status'] ?? 'unknown'); ?>">
                                <?php echo htmlspecialchars($sp['status'] ?? 'Unknown'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this scheduled payment?')">
                                    <input type="hidden" name="id" value="<?php echo $sp['id']; ?>">
                                    <button type="submit" name="delete_schedule" class="btn-danger">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(empty($scheduled_payments)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <h4>No scheduled payments found</h4>
            <p>All payment schedules are currently up to date.</p>
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
    --danger: #dc2626;
    --success: #10b981;
    --warning: #f59e0b;
}

[data-theme="dark"] {
    --bg-main: #0f111a;
    --card-bg: #191c24;
    --text-dark: #f8fafc;
    --text-muted: #94a3b8;
    --border: #2a2e39;
}

.scheduler-oversight { 
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

/* Amount Cell */
.amount-cell { font-weight: 600; color: var(--text-dark); }

/* Status Badges */
.status-badge { 
    padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 600; 
    text-transform: uppercase; letter-spacing: 0.05em;
}
.status-paid { background: #ecfdf5; color: var(--success); }
.status-unpaid { background: #fef3c7; color: var(--warning); }
.status-overdue { background: #fef2f2; color: var(--danger); }
.status-unknown { background: #f3f4f6; color: #6b7280; }

/* Action Buttons */
.action-buttons { display: flex; gap: 8px; }
.btn-danger {
    background: var(--danger); color: white; border: none; 
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
}
.btn-danger:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Empty State */
.empty-state { 
    text-align: center; padding: 64px 24px; color: var(--text-muted);
}
.empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h4 { font-size: 18px; margin: 0 0 8px 0; color: var(--text-dark); }
.empty-state p { margin: 0; font-size: 14px; }

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .scheduler-oversight { padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .table-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .modern-table thead th, .modern-table tbody td { padding: 12px 16px; }
    .user-cell { flex-direction: column; align-items: flex-start; gap: 4px; }
    .action-buttons { flex-direction: column; }
}
</style>
