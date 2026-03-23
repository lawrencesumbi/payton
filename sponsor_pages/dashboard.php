<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sponsor_id = $_SESSION['user_id'];

/* ==========================================
   1. FETCH TOTAL STATS
   ========================================== */
// Total amount ever allocated by this sponsor
$stmt = $conn->prepare("SELECT SUM(budget_amount) FROM budget WHERE sponsor_id = ?");
$stmt->execute([$sponsor_id]);
$total_allocated = (float)$stmt->fetchColumn();

// Count of unique spenders managed
$stmt = $conn->prepare("SELECT COUNT(id) FROM sponsor_spender WHERE sponsor_id = ?");
$stmt->execute([$sponsor_id]);
$total_spenders = (int)$stmt->fetchColumn();

// Count of active budgets
$stmt = $conn->prepare("SELECT COUNT(id) FROM budget WHERE sponsor_id = ? AND status = 'active'");
$stmt->execute([$sponsor_id]);
$active_budgets = (int)$stmt->fetchColumn();

/* ==========================================
   2. FETCH CHART DATA (Allocation per Spender)
   ========================================== */
$stmt = $conn->prepare("
    SELECT u.fullname, SUM(b.budget_amount) as total 
    FROM budget b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.sponsor_id = ? 
    GROUP BY u.id
");
$stmt->execute([$sponsor_id]);
$chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$amounts = [];
foreach($chart_data as $row) {
    $labels[] = $row['fullname'];
    $amounts[] = $row['total'];
}

/* ==========================================
   3. FETCH ALL BUDGETS FOR THE TABLE
   ========================================== */
$stmt = $conn->prepare("
    SELECT b.*, u.fullname 
    FROM budget b
    JOIN users u ON b.user_id = u.id
    WHERE b.sponsor_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$sponsor_id]);
$all_budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sponsor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root { --primary: #7f308f; --bg: #f8fafc; }
        html, body {
    height: 100vh;
    margin: 0;
    padding: 0;
    overflow: hidden; /* This kills the main page scrollbar */
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
        
        .stat-card {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            padding: 10px; transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-shape {
            width: 48px; height: 48px; background: #f3e8ff; color: var(--primary);
            border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .chart-container {
            background: #fff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; height: 100%;
        }
        .status-badge { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; }
        
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Sponsor Overview</h4>
        
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card d-flex align-items-center">
                <div class="icon-shape me-3"><i class="bi bi-bank"></i></div>
                <div>
                    <p class="text-muted small mb-0">Total Capital Allocated</p>
                    <h3 class="fw-bold mb-0">₱<?= number_format($total_allocated, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card d-flex align-items-center">
                <div class="icon-shape me-3" style="background:#e0f2fe; color:#0369a1;"><i class="bi bi-people"></i></div>
                <div>
                    <p class="text-muted small mb-0">Managed Spenders</p>
                    <h3 class="fw-bold mb-0"><?= $total_spenders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card d-flex align-items-center">
                <div class="icon-shape me-3" style="background:#dcfce7; color:#15803d;"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <p class="text-muted small mb-0">Active Allowances</p>
                    <h3 class="fw-bold mb-0"><?= $active_budgets ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="chart-container">
                <h6 class="fw-bold mb-4">Budget Distribution</h6>
                <canvas id="allocationChart"></canvas>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="chart-container shadow-sm">
                <h6 class="fw-bold mb-4">Manage Allowances</h6>
                <div class="table-responsive" style="height: 100%; overflow-y: auto;">
                    <table class="table align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Spender</th>
                                <th>Budget Name</th>
                                <th>Amount</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_budgets as $b): ?>
                            <tr>
                                <td><span class="fw-semibold"><?= htmlspecialchars($b['fullname']) ?></span></td>
                                <td><?= htmlspecialchars($b['budget_name']) ?></td>
                                <td class="fw-bold">₱<?= number_format($b['budget_amount'], 2) ?></td>
                                <td class="small text-muted"><?= date("M d, Y", strtotime($b['end_date'])) ?></td>
                                <td>
                                    <span class="status-badge <?= ($b['status'] == 'active') ? 'status-active' : 'status-inactive' ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </td>
                                
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('allocationChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($amounts) ?>,
                backgroundColor: ['#7f308f', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            },
            cutout: '70%'
        }
    });
</script>
</body>
</html>