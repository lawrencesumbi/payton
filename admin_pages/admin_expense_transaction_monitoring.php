<?php
// Admin Expense Transaction Monitoring

// Fetch all expenses with details
$searchTerm = $_GET['search'] ?? '';

$query = "
    SELECT e.id, e.amount, e.description, e.created_at as date, e.receipt_upload, u.fullname as user_name, c.category_name 
    FROM expenses e 
    JOIN users u ON e.user_id = u.id 
    JOIN category c ON e.category_id = c.id 
    WHERE 1=1
";

if (!empty($searchTerm)) {
    $query .= " AND (u.fullname LIKE ? OR e.description LIKE ? OR c.category_name LIKE ?)";
}

$query .= " ORDER BY e.created_at DESC";

$stmt = $conn->prepare($query);
$params = [];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard];
}

$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total expenses
$total_expenses = array_sum(array_column($expenses, 'amount'));
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="expense-monitoring">
    <header class="page-header">
        <div class="header-left">
            <span class="badge-accent">Transaction Monitoring</span>
            <h1>Expense Transactions</h1>
            <p>Track and analyze all expense transactions across the platform.</p>
        </div>
        <div class="header-right">
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>
    </header>

    <div class="stats-overview">
        <div class="glass-card stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Transactions</p>
                <h3 class="stat-value"><?php echo number_format(count($expenses)); ?></h3>
            </div>
        </div>
        
        <div class="glass-card stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-peso-sign"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total Amount</p>
                <h3 class="stat-value">₱<?php echo number_format($total_expenses, 2); ?></h3>
            </div>
        </div>
        
        <div class="glass-card stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <p class="stat-label">Average per Transaction</p>
                <h3 class="stat-value">₱<?php echo count($expenses) > 0 ? number_format($total_expenses / count($expenses), 2) : '0.00'; ?></h3>
            </div>
        </div>
    </div>

    <div class="glass-card table-container">
        <div class="table-header">
            <h3>All Expense Transactions</h3>
            <span class="record-count"><?php echo count($expenses); ?> transactions</span>
        </div>
        
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($expenses as $expense): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo strtoupper(substr($expense['user_name'], 0, 1)); ?></div>
                                <span><?php echo htmlspecialchars($expense['user_name']); ?></span>
                            </div>
                        </td>
                        <td class="amount-cell">₱<?php echo number_format($expense['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td>
                            <span class="category-badge"><?php echo htmlspecialchars($expense['category_name']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($expense['date'])); ?></td>
                        <td>
                            <?php if ($expense['receipt_upload']): ?>
                            <a href="<?php echo htmlspecialchars($expense['receipt_upload']); ?>" target="_blank" class="btn-link">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                            <?php else: ?>
                            <span class="no-receipt">No receipt</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(empty($expenses)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-receipt"></i>
            <h4>No expense transactions found</h4>
            <p>The platform currently has no recorded expenses.</p>
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

[data-theme="dark"] .stat-icon {
    background: #3730a3;
    color: #a855f7;
}

.expense-monitoring { 
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

/* Stats Overview */
.stats-overview { 
    display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
    gap: 24px; margin-bottom: 32px; 
}
.glass-card { 
    background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); overflow: hidden;
}
.stat-card { 
    padding: 24px; display: flex; align-items: center; gap: 16px;
}
.stat-icon { 
    width: 48px; height: 48px; background: #e0e7ff; color: #a855f7; 
    border-radius: 12px; display: grid; place-items: center; font-size: 20px;
}
.stat-content { flex: 1; }
.stat-label { color: var(--text-muted); font-size: 14px; margin: 0 0 4px 0; font-weight: 500; }
.stat-value { font-size: 24px; margin: 0; font-weight: 800; color: var(--text-dark); }

/* Table Container */
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

/* Category Badge */
.category-badge { 
    background: var(--bg-main); color: var(--text-dark); padding: 4px 12px; 
    border-radius: 100px; font-size: 12px; font-weight: 600;
}

/* Receipt Links */
.btn-link { 
    color: var(--primary); text-decoration: none; font-weight: 600; 
    display: inline-flex; align-items: center; gap: 6px; font-size: 13px;
    transition: all 0.2s;
}
.btn-link:hover { color: #4f46e5; text-decoration: underline; }
.no-receipt { color: var(--text-muted); font-size: 13px; font-style: italic; }

/* Empty State */
.empty-state { 
    text-align: center; padding: 64px 24px; color: var(--text-muted);
}
.empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h4 { font-size: 18px; margin: 0 0 8px 0; color: var(--text-dark); }
.empty-state p { margin: 0; font-size: 14px; }

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .expense-monitoring { padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .stats-overview { grid-template-columns: 1fr; }
    .stat-card { padding: 20px; }
    .table-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .modern-table thead th, .modern-table tbody td { padding: 12px 16px; }
    .user-cell { flex-direction: column; align-items: flex-start; gap: 4px; }
}
</style>
