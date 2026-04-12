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
    /* ===== 1. THEME VARIABLES ===== */
    :root { 
        --primary: #6f42c1; 
        --bg-body: #f8fafc; 
        --card-bg: #ffffff;
        --text-main: #1a202c;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius: 20px; /* Restoring your soft edges */
    }

    [data-theme="dark"] {
        --bg-body: #0f111a;
        --card-bg: #191c24;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #2a2e39;
    }

    /* ===== 2. GLOBAL RESET ===== */
    body { 
        background-color: var(--bg-body) !important; 
        color: var(--text-main);
        font-family: 'Inter', sans-serif;
        transition: 0.3s ease;
    }

    /* ===== 3. CONTAINER & CARD FIXES ===== */
  /* ===== GLOBAL CARD STYLING ===== */
.stat-card, .chart-container {
    background: var(--card-bg) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: var(--radius) !important;
    padding: 24px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    height: 100%;
}

/* Specifically for Stat Cards - Ensuring Desktop spacing */
.stat-card {
    display: flex;
    align-items: center; /* Vertical center */
    justify-content: flex-start;
}

/* Fix the "too close" issue on Desktop icons */
.stat-card .icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    margin-right: 20px !important; /* Spacing for Desktop */
    flex-shrink: 0;
}
    /* ===== 4. TABLE VISIBILITY FIXES ===== */
    /* This target prevents the "white background" issue from your screenshots */
    [data-theme="dark"] .table {
        --bs-table-bg: transparent !important;
        --bs-table-color: var(--text-main) !important;
        background-color: transparent !important;
        margin-bottom: 0;
    }

    /* Fix for the white strips in archive.PNG */
    [data-theme="dark"] .table tr, 
    [data-theme="dark"] .table td {
        background-color: transparent !important; 
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-color) !important;
        padding: 16px 12px !important; /* Vertical spacing for table rows */
    }

    /* Header styling that stays dark */
    [data-theme="dark"] thead th,
    [data-theme="dark"] .table-light th {
        background-color: var(--bg-body) !important;
        color: var(--text-muted) !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border-color) !important;
    }

    /* ===== 5. INPUTS & LABELS (Fix for newAllowance.PNG) ===== */
    [data-theme="dark"] .modal-content,
    [data-theme="dark"] .modal-card {
        background-color: var(--card-bg) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 24px !important;
    }

    [data-theme="dark"] .form-label, 
    [data-theme="dark"] label {
        color: var(--text-main) !important; /* Making labels bright enough to read */
        font-weight: 600;
    }

    /* ===== 6. SCROLLBAR HIDE ===== */
    html, body { scrollbar-width: none; -ms-overflow-style: none; }
    html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; }
    /* --- Fix for Stat Card Text Visibility --- */
[data-theme="dark"] .stat-card h3 {
    color: #ffffff !important; /* Forces the large numbers (₱11,900.00) to white */
}

[data-theme="dark"] .stat-card .text-muted {
    color: #94a3b8 !important; /* Ensures "Total Capital Allocated" is readable */
}

[data-theme="dark"] .stat-card p {
    color: #cbd5e1 !important;
}

/* --- Ensuring Icon Shapes remain visible but subtle --- */
[data-theme="dark"] .icon-shape {
    background: rgba(111, 66, 193, 0.2) !important; /* Subtle purple glow for icons */
    color: #a855f7 !important;
}

/* --- Success/Info Icon variants (Managed Spenders & Active Allowances) --- */
[data-theme="dark"] .icon-shape[style*="background:#e0f2fe"] {
    background: rgba(3, 105, 161, 0.2) !important;
    color: #38bdf8 !important;
}

[data-theme="dark"] .icon-shape[style*="background:#dcfce7"] {
    background: rgba(21, 128, 61, 0.2) !important;
    color: #4ade80 !important;
}

/* Container to limit the height and enable scrolling */
.table-scroll-container {
    max-height: 350px; /* Adjust this height as needed */
    overflow-y: auto;
    scrollbar-width: none; /* Clean look for Firefox */
    position: relative;
}

/* Custom scrollbar for Chrome/Edge/Safari */
.table-scroll-container::-webkit-scrollbar {
    width: 6px;
}
.table-scroll-container::-webkit-scrollbar-thumb {
    background-color: var(--border-color);
    border-radius: 10px;
}

/* Make the header stick to the top while scrolling */
.table-scroll-container thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--card-bg) !important;
    border-bottom: 2px solid var(--border-color) !important;
}


/* Optimized for 720 x 1612 Stat Cards */
@media (max-width: 768px) {
    /* Force the "Total Capital" card to stay solo */
    .row.g-4.mb-4 > .col-md-4:first-child {
        width: 100% !important;
        margin-bottom: 15px;
    }

    /* Force "Managed Spenders" and "Active Allowances" to share the row */
    .row.g-4.mb-4 > .col-md-4:not(:first-child) {
        width: 50% !important;
        float: left;
    }

    /* Make the shared cards more "square-like" and adjust padding */
    .stat-card {
        padding: 15px !important;
        flex-direction: column; /* Stack icon on top of text for the small squares */
        justify-content: center;
        text-align: center;
        min-height: 140px; /* Ensures a square appearance */
    }

    /* Reset the first card (Solo) to stay horizontal for readability */
    .row.g-4.mb-4 > .col-md-4:first-child .stat-card {
        flex-direction: row;
        text-align: left;
        min-height: auto;
    }

    /* Adjust icon spacing for the square cards */
    .stat-card .icon-shape {
        margin-right: 0 !important;
        margin-bottom: 10px;
    }

    .row.g-4.mb-4 > .col-md-4:first-child .icon-shape {
        margin-bottom: 0;
        margin-right: 15px !important;
    }

    /* Shrink numbers slightly so they don't break on narrow 720px screens */
    .stat-card h3 {
        font-size: 1.25rem !important;
    }
}

/* ===== SLIDESHOW / TABLE RESPONSIVE LOGIC ===== */
/* --- Desktop Reset / Defaults --- */
.table-responsive-wrapper {
    width: 100%;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.table-responsive-wrapper::-webkit-scrollbar { display: none; }

/* --- Mobile Only: 720 x 1612 Optimization --- */
@media (max-width: 768px) {
    /* 1. Hide the table header on mobile only */
    .table thead { 
        display: none !important; 
    }

    /* 2. Turn the container into a horizontal slider */
    .table-responsive-wrapper {
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: none;
        display: block;
        max-height: none !important;
    }
    .table-responsive-wrapper::-webkit-scrollbar { display: none; }

    /* 3. Force Table and Body into Flex mode */
    .table { 
        display: block !important; 
        border: none !important;
        margin: 0 !important;
    }

    .table tbody {
        display: flex !important;
        flex-direction: row !important; /* Forces side-by-side cards */
    }

    /* 4. Each Row becomes a full-width Slide Card */
    .table tr {
        display: block !important;
        min-width: 100% !important; 
        scroll-snap-align: center;
        background: var(--card-bg);
        border: 1px solid var(--border-color) !important;
        border-radius: 15px;
        padding: 10px;
        margin-right: 15px; /* Gap between slides */
    }

    /* 5. Cells stack vertically inside the card */
    .table td {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center;
        padding: 12px 10px !important;
        border-bottom: 1px solid var(--border-color) !important;
        width: 100% !important;
    }
    
    .table td:last-child { 
        border-bottom: none !important; 
    }

    /* 6. Inject Labels on the left */
    .table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--text-muted);
        font-size: 12px;
        text-align: left;
    }

    /* --- Visual Tweaks for Controls --- */
    .slideshow-controls {
        display: flex !important;
        align-items: center;
        gap: 8px;
    }
}

/* --- Desktop Specific Override --- */
@media (min-width: 769px) {
    .slideshow-controls {
        display: none !important;
    }
    .table-responsive-wrapper {
        max-height: 400px; /* Limitahan ang gitas-on sa desktop */
        overflow-y: auto;  /* Vertical scroll mugawas kung daghan data */
        display: block !important;
    }
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: var(--card-bg) !important;
    }
    /* Ensure table behaves exactly like a standard table on desktop */
    .table {
        display: table !important;
        width: 100%;
    }
    .table tbody {
        display: table-row-group !important;
    }
    .table tr {
        display: table-row !important;
    }
    .table td {
        display: table-cell !important;
    }
}
</style>
</head>
<body>

<div class="container-fluid">
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
            <div class="icon-shape" style="background:#e0f2fe; color:#0369a1;"><i class="bi bi-people"></i></div>
            <div>
                <p class="text-muted small mb-0">Managed Spenders</p>
                <h3 class="fw-bold mb-0"><?= $total_spenders ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card d-flex align-items-center">
            <div class="icon-shape" style="background:#dcfce7; color:#15803d;"><i class="bi bi-check2-circle"></i></div>
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
                <h6 class="fw-bold mb-2">Budget Distribution</h6>
                <canvas id="allocationChart"></canvas>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="chart-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0">Manage Allowances</h6>
                </div>

                <div class="table-scroll-container">
                    <table class="table align-middle">
                        <thead>
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

    const tableWrapper = document.querySelector('.table-responsive-wrapper');
const ssCounter = document.getElementById('ss-counter');
const totalSlides = document.querySelectorAll('.slide-card').length;

function moveSlide(dir) {
    const width = tableWrapper.offsetWidth;
    tableWrapper.scrollBy({ left: dir * width, behavior: 'smooth' });
}

// Update counter when user swipes manually
tableWrapper.addEventListener('scroll', () => {
    const index = Math.round(tableWrapper.scrollLeft / tableWrapper.offsetWidth);
    if(ssCounter) ssCounter.innerText = `${index + 1} / ${totalSlides}`;
});
</script>
</body>
</html>