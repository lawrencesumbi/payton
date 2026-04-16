<?php
// Admin Dashboard - Overview of the entire system

/* ==========================================
   1. FETCH TOTAL STATS
   ========================================== */
// Total Users
$stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();
$total_users = $stmt->fetchColumn();

// Total Sponsors
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'sponsor'");
$stmt->execute();
$total_sponsors = $stmt->fetchColumn();

// Total Spenders
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'spender'");
$stmt->execute();
$total_spenders = $stmt->fetchColumn();

// Total Expenses
$stmt = $conn->prepare("SELECT SUM(amount) FROM expenses");
$stmt->execute();
$total_expenses = $stmt->fetchColumn();

// Total Budgets
$stmt = $conn->prepare("SELECT COUNT(*) FROM budget");
$stmt->execute();
$total_budgets = $stmt->fetchColumn();

// Active Budgets
$stmt = $conn->prepare("SELECT COUNT(*) FROM budget WHERE status = 'active'");
$stmt->execute();
$active_budgets = $stmt->fetchColumn();

/* ==========================================
   2. FETCH CHART DATA (Expenses by Category)
   ========================================== */
$stmt = $conn->prepare("
    SELECT c.category_name, SUM(e.amount) as total 
    FROM expenses e 
    JOIN category c ON e.category_id = c.id 
    GROUP BY c.id
");
$stmt->execute();
$chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$amounts = [];
foreach($chart_data as $row) {
    $labels[] = $row['category_name'];
    $amounts[] = $row['total'];
}

/* ==========================================
   3. RECENT ACTIVITIES (from logs)
   ========================================== */
$stmt = $conn->prepare("
    SELECT l.action, l.created_at, u.fullname 
    FROM logs l 
    JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard">
    <h2>Admin Dashboard</h2>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $total_users; ?></h3>
            <p>Total Users</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $total_sponsors; ?></h3>
            <p>Sponsors</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $total_spenders; ?></h3>
            <p>Spenders</p>
        </div>
        <div class="stat-card">
            <h3>$<?php echo number_format($total_expenses, 2); ?></h3>
            <p>Total Expenses</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $total_budgets; ?></h3>
            <p>Total Budgets</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $active_budgets; ?></h3>
            <p>Active Budgets</p>
        </div>
    </div>
    
    <!-- Chart -->
    <div class="chart-container">
        <h3>Expenses by Category</h3>
        <canvas id="expenseChart"></canvas>
    </div>
    
    <!-- Recent Activities -->
    <div class="recent-activities">
        <h3>Recent Activities</h3>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo $log['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            data: <?php echo json_encode($amounts); ?>,
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
            ]
        }]
    }
});
</script>

<style>
.dashboard { padding: 20px; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
.stat-card { background: var(--bg-card); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); text-align: center; }
.stat-card h3 { font-size: 2em; margin: 0; color: var(--accent-purple); }
.stat-card p { margin: 5px 0 0; color: var(--text-muted); }
.chart-container { background: var(--bg-card); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 40px; }
.recent-activities { background: var(--bg-card); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); }
.recent-activities table { width: 100%; border-collapse: collapse; }
.recent-activities th, .recent-activities td { padding: 10px; text-align: left; border-bottom: 1px solid var(--border-color); }
</style>
