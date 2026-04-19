<?php
// Admin Dashboard - Overview of the entire system

/* ==========================================
   1. FETCH TOTAL STATS
   ========================================== */
// Total Users
$stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();
$total_users = $stmt->fetchColumn() ?: 0;

// Total Sponsors
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'sponsor'");
$stmt->execute();
$total_sponsors = $stmt->fetchColumn() ?: 0;

// Total Spenders
$stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'spender'");
$stmt->execute();
$total_spenders = $stmt->fetchColumn() ?: 0;

// Total Expenses
$stmt = $conn->prepare("SELECT SUM(amount) FROM expenses");
$stmt->execute();
$total_expenses = $stmt->fetchColumn() ?: 0;

// Total Budgets
$stmt = $conn->prepare("SELECT COUNT(*) FROM budget");
$stmt->execute();
$total_budgets = $stmt->fetchColumn() ?: 0;

// Active Budgets
$stmt = $conn->prepare("SELECT COUNT(*) FROM budget WHERE status = 'active'");
$stmt->execute();
$active_budgets = $stmt->fetchColumn() ?: 0;

/* ==========================================
   2. FETCH CHART DATA (Expenses by Category)
   ========================================== */
// Using LEFT JOIN to ensure we see categories, 
// and COALESCE to handle null sums as 0
$stmt = $conn->prepare("
    SELECT c.category_name, COALESCE(SUM(e.amount), 0) as total 
    FROM category c
    LEFT JOIN expenses e ON c.id = e.category_id 
    GROUP BY c.id, c.category_name
    HAVING total > 0
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
$searchTerm = $_GET['search'] ?? '';

$query = "
    SELECT l.action, l.created_at, u.fullname 
    FROM logs l 
    JOIN users u ON l.user_id = u.id 
    WHERE 1=1
";

if (!empty($searchTerm)) {
    $query .= " AND (u.fullname LIKE ? OR l.action LIKE ?)";
}

$query .= " ORDER BY l.created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$params = [];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard];
}

$stmt->execute($params);
$recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="dashboard-container">
    <header class="dashboard-header">
        <div class="header-left">
            <span class="badge-accent">System Overview</span>
            <h1>Admin Dashboard</h1>
            <p>Real-time platform metrics and system integrity monitoring.</p>
        </div>
        <div class="header-right">
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fa-solid fa-rotate"></i> Sync Data
            </button>
        </div>
    </header>

    <div class="stats-container">
        <div class="glass-card stat-item">
            <div class="stat-info">
                <p class="stat-label">Total Users</p>
                <h2 class="stat-value"><?php echo number_format($total_users); ?></h2>
            </div>
            <div class="stat-visual"><i class="fa-solid fa-users"></i></div>
        </div>

        <div class="glass-card stat-item">
            <div class="stat-info">
                <p class="stat-label">Role Distribution</p>
                <h2 class="stat-value"><?php echo $total_sponsors; ?> <small>/</small> <?php echo $total_spenders; ?></h2>
                <span class="trend-label">Sponsors vs Spenders</span>
            </div>
            <div class="stat-visual"><i class="fa-solid fa-user-tie"></i></div>
        </div>

        <div class="glass-card stat-item">
            <div class="stat-info">
                <p class="stat-label">Global Expenses</p>
                <h2 class="stat-value">₱<?php echo number_format($total_expenses, 2); ?></h2>
            </div>
            <div class="stat-visual"><i class="fa-solid fa-wallet"></i></div>
        </div>

        <div class="glass-card stat-item">
            <div class="stat-info">
                <p class="stat-label">Active Budgets</p>
                <h2 class="stat-value"><?php echo $active_budgets; ?> <span class="trend-up">/ <?php echo $total_budgets; ?></span></h2>
            </div>
            <div class="stat-visual"><i class="fa-solid fa-chart-pie"></i></div>
        </div>
    </div>

    <div class="main-grid">
        <section class="glass-card chart-section">
            <div class="card-header">
                <h3>Allocation by Category</h3>
                <span class="dot-menu">•••</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="expenseChart"></canvas>
                <div id="chart-fallback" class="hidden">No expense data found</div>
            </div>
        </section>

        <section class="glass-card activity-section">
            <div class="card-header">
                <h3>System Logs</h3>
                <a href="#" class="view-all">See All</a>
            </div>
            <div class="activity-list">
                <?php if(empty($recent_logs)): ?>
                    <p style="padding: 24px; color: var(--text-muted);">No recent activity found.</p>
                <?php else: ?>
                    <?php foreach($recent_logs as $log): ?>
                    <div class="activity-row">
                        <div class="avatar"><?php echo strtoupper(substr($log['fullname'], 0, 1)); ?></div>
                        <div class="activity-details">
                            <p class="activity-text"><strong><?php echo htmlspecialchars($log['fullname']); ?></strong> <?php echo htmlspecialchars($log['action']); ?></p>
                            <span class="activity-time"><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
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

[data-theme="dark"] .stat-visual {
    background: #3730a3;
    color: #a855f7;
}

.dashboard-container { 
    font-family: 'Inter', sans-serif;
    background: var(--bg-main);
    padding: 32px;
    color: var(--text-dark);
    min-height: 100vh;
}

/* Header */
.dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.badge-accent { 
    background: #e0e7ff; color: var(--primary); 
    padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
}
.dashboard-header h1 { font-size: 32px; margin: 8px 0; font-weight: 800; letter-spacing: -0.03em; }
.dashboard-header p { color: var(--text-muted); margin: 0; font-size: 15px; }

.btn-refresh {
    background: var(--card-bg); border: 1px solid var(--border);
    padding: 12px 20px; border-radius: 14px; font-weight: 600; cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;
}
.btn-refresh:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Stats Grid */
.stats-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
.glass-card { 
    background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); overflow: hidden;
}
.stat-item { padding: 28px; display: flex; justify-content: space-between; align-items: flex-start; }
.stat-label { color: var(--text-muted); font-size: 14px; font-weight: 500; margin: 0 0 8px 0; }
.stat-value { font-size: 28px; margin: 0; font-weight: 800; letter-spacing: -0.02em; }
.stat-value small { font-size: 18px; color: var(--text-muted); font-weight: 500; margin: 0 4px; }
.trend-up { font-size: 14px; color: var(--text-muted); font-weight: 500; }
.trend-label { font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px; }

.stat-visual { 
    width: 52px; height: 52px; border-radius: 16px; 
    display: grid; place-items: center; font-size: 20px; 
    background: #e0e7ff; color: #a855f7;
}

/* Main Grid Layout */
.main-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: start; }
.card-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.card-header h3 { font-size: 17px; margin: 0; font-weight: 700; }
.dot-menu { color: var(--border); cursor: pointer; font-size: 18px; }

/* Chart Styling */
.chart-section, .activity-section {
    height: 480px; /* Adjust this value to your preferred overall height */
    display: flex;
    flex-direction: column;
}

/* Ensure the wrapper and list take up the remaining space */
.chart-wrapper {
    flex: 1; 
}
#chart-fallback { color: var(--text-muted); font-size: 14px; }
.hidden { display: none; }

/* Activity List Styling */
.activity-list {
    flex: 1;
    overflow-y: auto; /* Enable scrolling */
    padding: 8px 0;
    
    /* Hide scrollbar for Chrome, Safari and Opera */
    -webkit-overflow-scrolling: touch;
}

.activity-list::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.activity-list {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}.activity-row { 
    flex-shrink: 0; /* Prevents rows from squishing to fit */
    display: flex; 
    align-items: center; 
    gap: 16px; 
    padding: 18px 24px;
    border-bottom: 1px solid var(--border); 
    transition: background 0.2s;
}
.activity-row:last-child { border-bottom: none; }
.activity-row:hover { background: var(--bg-main); }
.avatar { 
    width: 40px; height: 40px; background: var(--bg-main); border-radius: 12px;
    display: grid; place-items: center; font-weight: 700; font-size: 14px; color: var(--primary);
    border: 1px solid var(--border);
}
.activity-details { flex: 1; }
.activity-text { margin: 0; font-size: 14px; color: var(--text-dark); line-height: 1.4; }
.activity-text strong { font-weight: 600; }
.activity-time { font-size: 12px; color: var(--text-muted); margin-top: 4px; display: block; }
.view-all { font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 700; }

/* Mobile Responsiveness */
@media (max-width: 1200px) { .stats-container { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 900px) { .main-grid { grid-template-columns: 1fr; } }
@media (max-width: 600px) { 
    .dashboard-container { padding: 16px; }
    .stats-container { grid-template-columns: 1fr; }
    .dashboard-header { flex-direction: column; align-items: flex-start; gap: 16px; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = <?php echo json_encode($labels ?: []); ?>;
    const amounts = <?php echo json_encode($amounts ?: []); ?>;

    const chartCanvas = document.getElementById('expenseChart');
    const fallback = document.getElementById('chart-fallback');

    // Handle empty data state visually
    if (labels.length === 0) {
        chartCanvas.classList.add('hidden');
        fallback.classList.remove('hidden');
        return;
    }

    const ctx = chartCanvas.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: amounts,
                backgroundColor: [
                    '#6366f1', // Indigo
                    '#3b82f6', // Blue
                    '#10b981', // Emerald
                    '#f59e0b', // Amber
                    '#8b5cf6', // Violet
                    '#f43f5e'  // Rose
                ],
                hoverOffset: 20,
                borderWidth: 5,
                borderColor: '#ffffff',
                borderRadius: 2
            }]
        },
        options: {
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 25,
                        font: { family: "'Inter', sans-serif", size: 12, weight: '500' },
                        color: '#64748b'
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    bodyFont: { size: 14 },
                    callbacks: {
                        label: function(context) {
                            return ` $${context.parsed.toLocaleString()}`;
                        }
                    }
                }
            },
            cutout: '72%',
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
});
</script>