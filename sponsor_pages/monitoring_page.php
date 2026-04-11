<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sponsor_id = $_SESSION['user_id'];
$selected_spender = $_GET['spender_id'] ?? null;
$selected_allowance = $_GET['allowance_id'] ?? null;

// Get search term from URL
$searchTerm = $_GET['search'] ?? '';

/* ==========================================
   FETCH SPENDERS
   ========================================== */
$query = "
    SELECT u.id, u.fullname 
    FROM users u
    INNER JOIN sponsor_spender ss ON u.id = ss.spender_id
    WHERE ss.sponsor_id = ?
";

if (!empty($searchTerm)) {
    $query .= " AND u.fullname LIKE ?";
}

$stmt = $conn->prepare($query);
$params = [$sponsor_id];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params[] = $searchWildcard;
}

$stmt->execute($params);
$spenders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================
   FETCH ALLOWANCES FOR SELECTED SPENDER
   ========================================== */
$allowances = [];
if ($selected_spender) {
    $stmtA = $conn->prepare("
        SELECT id, budget_name, budget_amount
        FROM budget
        WHERE user_id = ? AND sponsor_id = ?
        ORDER BY id DESC
    ");
    $stmtA->execute([$selected_spender, $sponsor_id]);
    $allowances = $stmtA->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================
   FETCH EXPENSES & TOTALS
   ========================================== */
$expenses = [];
$total_spent = 0;
$total_budget = 0;
$allowance_left = 0;

if ($selected_spender && $selected_allowance) {
    $query = "
        SELECT e.*, c.category_name, pm.payment_method_name
        FROM expenses e
        LEFT JOIN category c ON e.category_id = c.id
        LEFT JOIN payment_method pm ON e.payment_method_id = pm.id
        WHERE e.user_id = ? AND e.budget_id = ?
    ";
    
    if (!empty($searchTerm)) {
        $query .= " AND (e.description LIKE ? OR c.category_name LIKE ?)";
    }
    
    $query .= " ORDER BY e.expense_date DESC";
    
    $stmt = $conn->prepare($query);
    $params = [$selected_spender, $selected_allowance];
    
    if (!empty($searchTerm)) {
        $searchWildcard = "%{$searchTerm}%";
        $params[] = $searchWildcard;
        $params[] = $searchWildcard;
    }
    
    $stmt->execute($params);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expenses as $ex) {
        $total_spent += $ex['amount'];
    }

    $stmtB = $conn->prepare("SELECT budget_amount FROM budget WHERE id = ?");
    $stmtB->execute([$selected_allowance]);
    $total_budget = (float) $stmtB->fetchColumn();
    $allowance_left = max(0, $total_budget - $total_spent);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Monitoring | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
<style>
    /* 1. ROOT & THEME VARIABLES */
    :root {
        background-color: transparent !important;
        --primary: #7f308f;
        --card-bg: #ffffff;
        --text-main: #334155;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --header-bg: #f1f5f9;
        --input-bg: #ffffff;
    }

    [data-theme="dark"] {
        --card-bg: #191c24; 
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #2a2e39;
        --header-bg: #242833;
        --input-bg: #242833;
    }

    /* 2. GLOBAL RESET & BASE STYLES */
    html, body, .main-wrapper, .content-wrapper, #main-content {
        background-color: transparent !important;
        background: transparent !important;
    }   

    body {
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
        margin: 0;
        padding: 0;
        transition: all 0.3s ease;
    }

    html::-webkit-scrollbar, 
    body::-webkit-scrollbar {
        display: none;
        width: 0 !important;
    }

    .main-wrapper {
        width: 100%;
    }

    /* 3. LAYOUT CONTAINERS */
    .top-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        align-items: stretch;
        flex-wrap: wrap;
    }

    .selection-container { flex: 2; min-width: 400px; }
    .selection-form { display: flex; gap: 15px; height: 100%; }
    .stats-container { flex: 3; display: flex; gap: 15px; }

    /* 4. CARD COMPONENTS (STAT, SELECT, ACTIVITY) */
    .activity-card, .stat-card, .select-box {
        background-color: var(--card-bg) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .select-box, .stat-card {
        flex: 1;
        padding: 20px;
    }

    .stat-card {
        border-left: 5px solid var(--primary) !important;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: var(--text-main);
    }

    /* 5. TABLE STYLES & DARK MODE FIXES */
    .table-scroll-container {
        max-height: 400px;
        overflow-y: auto;
        position: relative;
    }

    .table { 
        color: var(--text-main); 
        border-color: var(--border-color); 
    }

    /* Sticky Header */
    .table-scroll-container thead th {
        position: sticky;
        top: 0;
        background-color: var(--header-bg) !important;
        color: var(--text-muted);
        z-index: 10;
        box-shadow: inset 0 -1px 0 var(--border-color);
    }

    [data-theme="dark"] {
        background-color: #12141b !important;
    }

    [data-theme="dark"] .table,
    [data-theme="dark"] .table tbody tr,
    [data-theme="dark"] .table td {
        background-color: transparent !important;
        color: #cbd5e1 !important;
        border-bottom-color: var(--border-color) !important;
    }

    [data-theme="dark"] .table td.fw-semibold,
    [data-theme="dark"] .fw-bold {
        color: #f8fafc !important;
    }

    [data-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd),
    [data-theme="dark"] .table-hover>tbody>tr:hover {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }

    /* 6. INPUTS & SELECT DROPDOWNS - Now Theme-Aware */
    .form-select {
        background-color: var(--card-bg) !important; /* Uses white in light, dark in dark */
        border-color: var(--border-color) !important;
        color: var(--text-main) !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%237f308f' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }

    /* This is the part that was staying dark */
    .form-select option {
        background-color: var(--card-bg) !important; /* Follows the theme variable */
        color: var(--text-main) !important;
    }

    /* Focus state that adapts */
    .form-select:focus {
        background-color: var(--card-bg) !important;
        border-color: var(--primary) !important;
        color: var(--text-main) !important;
        box-shadow: 0 0 0 0.25rem rgba(127, 48, 143, 0.25);
    }

    /* 7. UTILITIES & ICONS */
    .badge-category {
        background-color: var(--header-bg);
        color: var(--text-muted);
        font-weight: 600;
    }

    .text-muted, .small { 
        color: var(--text-muted) !important; 
    }

    .bi-wallet2, .bi-list-ul {
        color: var(--primary) !important;
    }

    /* 8. CUSTOM SCROLLBAR */
    .table-scroll-container::-webkit-scrollbar { width: 8px; }
    .table-scroll-container::-webkit-scrollbar-track { background: var(--header-bg); }
    .table-scroll-container::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    /* 9. RESPONSIVE DESIGN */
    @media (max-width: 992px) {
        .top-row, .selection-form { flex-direction: column; }
        .stats-container { flex-direction: row; flex-wrap: wrap; }
        .stat-card { min-width: 150px; }
    }
</style>
</head>
<body>

<div class="main-wrapper">
    <div class="top-row">
        <div class="selection-container">
            <form method="GET" action="sponsor.php" class="selection-form">
                <input type="hidden" name="page" value="monitoring_page">
                
                <div class="select-box">
                    <label class="stat-label">Select Spender</label>
                    <select name="spender_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Choose Account --</option>
                        <?php foreach($spenders as $spender): ?>
                            <option value="<?= $spender['id'] ?>" <?= ($selected_spender == $spender['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($spender['fullname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if($selected_spender): ?>
                <div class="select-box">
                    <label class="stat-label">Select Allowance</label>
                    <select name="allowance_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Choose Allowance --</option>
                        <?php foreach($allowances as $allow): ?>
                            <option value="<?= $allow['id'] ?>" <?= ($selected_allowance == $allow['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($allow['budget_name']) ?> (₱<?= number_format($allow['budget_amount'],2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <span class="stat-label">Total Allowance</span>
                <h5 class="stat-value">₱<?= number_format($total_budget, 2) ?></h5>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Spent</span>
                <h5 class="stat-value text-danger">₱<?= number_format($total_spent, 2) ?></h5>
            </div>
            <div class="stat-card" style="border-left-color: #10b981;">
                <span class="stat-label">Allowance Left</span>
                <h5 class="stat-value text-success">₱<?= number_format($allowance_left, 2) ?></h5>
            </div>
        </div>
    </div>

    <?php if($selected_spender && $selected_allowance): ?>
        <div class="activity-card">
            <div class="p-4 border-bottom" style="border-color: var(--border-color) !important;">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Transactions</h6>
            </div>
            <div class="table-responsive table-scroll-container">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">No.</th> <th>Date</th>
                            <th>Category</th>
                            <th>Payment Method</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($expenses)): ?>
                            <?php 
                                $count = 1; // Initialize the counter
                                foreach($expenses as $expense): 
                            ?>
                                <tr>
                                    <td class="ps-4 text-muted" style="font-size: 0.85rem;"><?= $count++ ?>.</td>
                                    
                                    <td class="fw-semibold"><?= date("M d, Y", strtotime($expense['expense_date'])) ?></td>
                                    <td><span class="badge badge-category rounded-pill p-2"><?= htmlspecialchars($expense['category_name'] ?? 'General') ?></span></td>
                                    <td><i class="bi bi-wallet2 me-1"></i><?= htmlspecialchars($expense['payment_method_name'] ?? 'Cash') ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($expense['description']) ?></td>
                                    <td class="text-end fw-bold">₱<?= number_format($expense['amount'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if($expense['receipt_upload']): ?>
                                            <a href="uploads/<?= $expense['receipt_upload'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted opacity-50">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No expenses recorded for this allowance.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="activity-card p-5 text-center">
            <i class="bi bi-search text-muted opacity-25" style="font-size: 4rem;"></i>
            <h5 class="mt-3 fw-bold">Ready to Monitor</h5>
            <p class="text-muted">Please select both a Spender and an Allowance to view detailed logs.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>