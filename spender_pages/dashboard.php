<?php
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =====================================================
   1. BUDGET & SPENDING LOGIC (From manage_expense)
===================================================== */
$budgetStmt = $conn->prepare("
    SELECT id, budget_amount, budget_name, start_date, end_date
    FROM budget
    WHERE user_id = :user_id AND status = 'Active'
      AND CURDATE() BETWEEN start_date AND end_date
    LIMIT 1
");
$budgetStmt->execute(['user_id' => $user_id]);
$activeBudget = $budgetStmt->fetch(PDO::FETCH_ASSOC);

$budgetId = $activeBudget['id'] ?? null;
$budgetAmount = floatval($activeBudget['budget_amount'] ?? 0);

$budgetExpenses = 0;
$categoryData = [];

if ($budgetId) {
    // Live spending calculation
    $spentStmt = $conn->prepare("SELECT SUM(amount) FROM expenses WHERE budget_id = ?");
    $spentStmt->execute([$budgetId]);
    $budgetExpenses = floatval($spentStmt->fetchColumn());

    // Chart Data
    $catStmt = $conn->prepare("
        SELECT c.category_name, SUM(e.amount) as total 
        FROM expenses e
        JOIN category c ON e.category_id = c.id
        WHERE e.budget_id = ?
        GROUP BY c.category_name
    ");
    $catStmt->execute([$budgetId]);
    $categoryData = $catStmt->fetchAll(PDO::FETCH_ASSOC);
}

$availableBalance = max(0, $budgetAmount - $budgetExpenses);

/* =====================================================
   2. TOTAL OWED (From expense_shares)
===================================================== */
$owedStmt = $conn->prepare("
    SELECT SUM(amount_owed) 
    FROM expense_shares 
    WHERE user_id = ? AND status = 'Unpaid'
");
$owedStmt->execute([$user_id]);
$totalOwed = floatval($owedStmt->fetchColumn());

/* =====================================================
   3. UPCOMING PAYMENTS (Future only)
===================================================== */
$upcomingStmt = $conn->prepare("
    SELECT payment_name, amount, due_date 
    FROM scheduled_payments 
    WHERE user_id = ? 
      AND paid_date IS NULL 
      AND due_date >= CURDATE()
    ORDER BY due_date ASC LIMIT 4
");
$upcomingStmt->execute([$user_id]);
$upcomingPayments = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   4. MONTHLY TRENDS & LINE GRAPH DATA
===================================================== */
// Fetch last 6 months of spending for the line graph
$monthlyStmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(expense_date, '%b') as month_label, 
        SUM(amount) as total 
    FROM expenses 
    WHERE user_id = ? 
      AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
    ORDER BY expense_date ASC
");
$monthlyStmt->execute([$user_id]);
$monthlyTrends = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

$monthLabels = array_column($monthlyTrends, 'month_label');
$monthTotals = array_column($monthlyTrends, 'total');

// Existing Stats logic
$thisMonth = date('Y-m-01');
$lastMonth = date('Y-m-01', strtotime('-1 month'));
$trendStmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN expense_date >= :tm THEN amount ELSE 0 END) as tm,
        SUM(CASE WHEN expense_date >= :lm AND expense_date < :tm THEN amount ELSE 0 END) as lm
    FROM expenses WHERE user_id = :uid
");
$trendStmt->execute(['tm' => $thisMonth, 'lm' => $lastMonth, 'uid' => $user_id]);
$trends = $trendStmt->fetch(PDO::FETCH_ASSOC);
$percChange = ($trends['lm'] > 0) ? (($trends['tm'] - $trends['lm']) / $trends['lm']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payton | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { 
            --primary: #7c3aed; 
            --primary-light: #f5f3ff;
            --text-main: #0f172a; 
            --text-muted: #64748b;
            --bg: #f8fafc; 
            --card-bg: #fff;
            --border-color: #eef1f6;
            --hover-bg: #f1f5f9;
            --shadow: rgba(0,0,0,0.02);
        }

        [data-theme="dark"] {
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --bg: #12141a;
            --card-bg: #191c24;
            --border-color: #2a2e39;
            --hover-bg: #242833;
            --shadow: rgba(0,0,0,0.2);
        }

        body { 
            background: var(--bg); 
            margin: 0; 
            color: var(--text-main); 
            font-family: 'Inter', sans-serif;
            transition: background 0.3s ease;
        }
                /* --- Force Hide Scrollbar but allow scrolling --- */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            /* Hide for IE, Edge and Firefox */
            -ms-overflow-style: none;  
            scrollbar-width: none;  
        }

        /* Hide for Chrome, Safari and Opera */
        html::-webkit-scrollbar, 
        body::-webkit-scrollbar {
            display: none;
            width: 0 !important;
            height: 0 !important;
        }

        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Stats Grid - uses auto-fit for better responsiveness */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
            margin-bottom: 20px; 
        }
        
        .stat-card {
            background: var(--card-bg); 
            padding: 15px; 
            border-radius: 24px; 
            border: 1px solid var(--border-color);
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 20px var(--shadow);
            transition: background 0.3s ease;
        }

        .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; margin: 0; }
        .stat-value { font-size: 24px; font-weight: 900; margin: 5px 0; }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        .purple-box { background: var(--primary-light); color: var(--primary); }
        .orange-box { background: #fff7ed; color: #f59e0b; }

        /* Content Layout */
        .dashboard-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
        }

        .panel { 
            background: var(--card-bg); 
            border-radius: 28px; 
            padding: 15px; 
            border: 1px solid var(--border-color); 
            box-shadow: 0 10px 30px var(--shadow);
            height: 420px; /* Ensures panels look balanced */
            display: flex;
            flex-direction: column;
            transition: background 0.3s ease;
        }

        .panel-header { font-weight: 800; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .panel-header::before { content: ""; width: 5px; height: 20px; background: var(--primary); border-radius: 10px; }

        /* Chart Container - Fixed height for stability */
        .chart-container {
            position: relative;
            flex-grow: 1;
            width: 100%;
            min-height: 0; /* Important for Chart.js inside flexbox */
        }

        /* Payment UI */
        .payment-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px; 
            background: var(--bg); 
            border-radius: 18px; 
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }
        .payment-row:hover { background: var(--hover-bg); transform: translateY(-2px); }
        .p-title { font-weight: 700; font-size: 14px; margin: 0; }
        .p-sub { font-size: 12px; color: var(--text-muted); margin: 3px 0 0; }
        .p-price { font-weight: 900; color: var(--primary); font-size: 15px; }

        .payment-list {
            /* Set the maximum height you want before it starts scrolling */
            max-height: 400px; 
            
            /* Enable vertical scrolling */
            overflow-y: auto;
            
            /* Optional: Smooth scrolling */
            scroll-behavior: smooth;
            
            /* Optional: Hide scrollbar for a cleaner look (Chrome/Safari) */
            &::-webkit-scrollbar {
                width: 6px;
            }
            &::-webkit-scrollbar-thumb {
                background: var(--border-color);
                border-radius: 10px;
            }
        }

        /* Ensure the panel doesn't stretch awkwardly */
        .panel {
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 1000px) { 
            .dashboard-container { grid-template-columns: 1fr; } 
            .panel { min-height: auto; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <header style="margin-bottom: 20px;">
        <h1 style="font-weight: 900; font-size: clamp(24px, 5vw, 32px); margin: 0;">Spender <span style="color: var(--primary);">Dashboard</span></h1>
        <p style="color: var(--text-muted); margin: 5px 0 0; font-weight: 500;">Overview of your financial obligations</p>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <p class="stat-label">Available Balance</p>
                <h3 class="stat-value">₱ <?= number_format($availableBalance, 2) ?></h3>
                <span style="font-size:11px; color:#22c55e; font-weight:700;">
                    <?= $activeBudget ? htmlspecialchars($activeBudget['budget_name']) : 'No active budget' ?>
                </span>
            </div>
            <div class="stat-icon purple-box"><i class="fa-solid fa-wallet"></i></div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Total People Owed</p>
                <h3 class="stat-value">₱ <?= number_format($totalOwed, 2) ?></h3>
                <span style="font-size:11px; color:#f59e0b; font-weight:700;">From Shared Expenses</span>
            </div>
            <div class="stat-icon orange-box"><i class="fa-solid fa-users-rays"></i></div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Monthly Spending</p>
                <h3 class="stat-value">₱ <?= number_format($trends['tm'], 2) ?></h3>
                <span style="font-size:11px; color:<?= $percChange > 0 ? '#ef4444' : '#22c55e' ?>; font-weight:700;">
                    <?= $percChange > 0 ? '↑' : '↓' ?> <?= abs(round($percChange, 1)) ?>% vs last month
                </span>
            </div>
            <div class="stat-icon purple-box"><i class="fa-solid fa-chart-line"></i></div>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="panel">
            <div class="panel-header">Spending Breakdown</div>
            <div class="chart-container">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">Spending Trend</div>
            <div class="chart-container">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">Upcoming Payments</div>
            <div class="payment-list" style="max-height: 350px; overflow-y: auto; padding-right: 5px;">
                <?php if (!empty($upcomingPayments)): ?>
                    <?php foreach ($upcomingPayments as $p): ?>
                        <div class="payment-row">
                            <div>
                                <p class="p-title"><?= htmlspecialchars($p['payment_name']) ?></p>
                                <p class="p-sub">Due: <?= date('M d, Y', strtotime($p['due_date'])) ?></p>
                            </div>
                            <div class="p-price">₱ <?= number_format($p['amount'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding: 30px 0;">
                        <i class="fa-regular fa-calendar-check" style="font-size: 30px; color: #cbd5e1; margin-bottom: 10px;"></i>
                        <p style="color: var(--text-muted); font-size: 14px;">No upcoming bills scheduled.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Polar Area Chart
    const ctx1 = document.getElementById('mainChart').getContext('2d');
    new Chart(ctx1, {
        type: 'polarArea',
        data: {
            labels: <?= json_encode(array_column($categoryData, 'category_name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($categoryData, 'total')) ?>,
                backgroundColor: ['rgba(124, 58, 237, 0.7)', 'rgba(59, 130, 246, 0.7)', 'rgba(245, 158, 11, 0.7)', 'rgba(16, 185, 129, 0.7)', 'rgba(239, 68, 68, 0.7)'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { r: { ticks: { display: false }, grid: { color: '#8b43ff' } } } }
    });

    // Line Chart (Monthly Trends)
    const ctx2 = document.getElementById('lineChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?= json_encode($monthLabels) ?>,
            datasets: [{
                label: 'Monthly Spending',
                data: <?= json_encode($monthTotals) ?>,
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#7c3aed',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
</script>
</body>
</html>