<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sponsor_id = $_SESSION['user_id'];
$selected_spender = $_GET['spender_id'] ?? null;

// FETCH SPENDERS
$stmt = $conn->prepare("
    SELECT u.id, u.fullname 
    FROM users u 
    INNER JOIN sponsor_spender ss ON u.id = ss.spender_id 
    WHERE ss.sponsor_id = ?
");
$stmt->execute([$sponsor_id]);
$spenders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// FETCH EXPENSES & TOTALS
$expenses = [];
$total_spent = 0;
if ($selected_spender) {
    $stmt = $conn->prepare("
        SELECT e.*, c.category_name, pm.payment_method_name 
        FROM expenses e 
        LEFT JOIN category c ON e.category_id = c.id 
        LEFT JOIN payment_method pm ON e.payment_method_id = pm.id 
        WHERE e.user_id = ? 
        ORDER BY e.expense_date DESC
    ");
    $stmt->execute([$selected_spender]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($expenses as $ex) { $total_spent += $ex['amount']; }
}

$total_budget = 0;
$allowance_left = 0;

if ($selected_spender) {
    // Total Budget / Allowance assigned by this sponsor
    $stmtB = $conn->prepare("
        SELECT SUM(budget_amount) AS total_budget
        FROM budget
        WHERE user_id = ? AND sponsor_id = ?
    ");
    $stmtB->execute([$selected_spender, $sponsor_id]);
    $total_budget = (float) $stmtB->fetchColumn();

    // Allowance Left = Total Budget - Total Spent
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
        /* Global & Layout */
        body {width:100%;  background-color: #f8f9fa; color: #334155; margin: 0;}
        .main { width: 100%; margin: 0 auto; }
        .select{padding: 20px; border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: #fff; height: 100%; }
        .above { width: 100%; display: flex; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
        .left { width: 50%; }
        .right { width: 50%; display: flex; gap: 15px; padding: 10px; }
        .right-left, .right-mid, .right-right { width: 50%; }

        /* Dashboard Cards */
        .dashboard-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: #fff; height: 100%; }
        .stat-card-blue { border-left: 4px solid #890dfd; padding: 1rem; transition: transform 0.2s; }
        .stat-card-green { border-left: 4px solid #890dfd; padding: 1rem; transition: transform 0.2s; }
        .stat-card-blue:hover, .stat-card-green:hover { transform: translateY(-3px); }

        /* Table Design */
        .table thead { background-color: #f1f5f9; }
        .table thead th { font-weight: 600; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; color: #64748b; border: none; padding: 15px 10px; }
        .badge-category { background-color: #e2e8f0; color: #475569; font-weight: 500; padding: 0.5em 0.8em; }
        
        /* Select & Inputs */
        .form-select-lg { border-radius: 10px; font-size: 0.95rem; border: 1px solid #e2e8f0; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        
        /* Empty States & Icons */
        .empty-state-container { text-align: center; padding: 3rem 0; }
        .icon-large { font-size: 3.5rem; color: #cbd5e1; }
        .receipt-btn { font-weight: bold; border: 1px solid #dee2e6; }

        /* Responsive */
        @media (max-width: 992px) {
            .left, .right { width: 100%; }
            .above { gap: 20px; }
        }
    </style>
</head>
<body>

<div class="main">
    <div class="above">
        <div class="left">
            
            <form method="GET" action="sponsor.php">
                <input type="hidden" name="page" value="monitoring_page">
                <div class="select">
                    <label class="form-label small fw-bold text-muted">SELECT SPENDER</label>
                    <select name="spender_id" class="form-select form-select-lg" onchange="this.form.submit()">
                        <option value="">-- Choose Account --</option>
                        <?php foreach($spenders as $spender): ?>
                            <option value="<?= $spender['id'] ?>" <?= ($selected_spender == $spender['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($spender['fullname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

            <div class="right d-flex gap-3">

                    <!-- Total Allowance -->
                    <div class="flex-fill">
                        <div class="card dashboard-card stat-card-blue">
                            <small class="card-label">TOTAL ALLOWANCE</small>
                            <h5 class="card-value">₱<?= number_format($total_budget, 2) ?></h5>
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="flex-fill">
                        <div class="card dashboard-card stat-card-blue">
                            <small class="card-label">TOTAL SPENT</small>
                            <h5 class="card-value text-danger">- ₱<?= number_format($total_spent, 2) ?></h5>
                        </div>
                    </div>

                    <!-- Allowance Left -->
                    <div class="flex-fill">
                        <div class="card dashboard-card stat-card-green">
                            <small class="card-label">ALLOWANCE LEFT</small>
                            <h5 class="card-value text-success">₱<?= number_format($allowance_left, 2) ?></h5>
                        </div>
                    </div>
            </div>
        </div>
    <?php if($selected_spender): ?>
        <div class="card dashboard-card overflow-hidden border-0">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Recent Activity</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Category</th>
                            <th>Payment Method</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($expenses)): ?>
                            <?php foreach($expenses as $expense): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark"><?= date("M d, Y", strtotime($expense['expense_date'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-category rounded-pill"><?= htmlspecialchars($expense['category_name'] ?? 'General') ?></span>
                                    </td>
                                    <td>
                                        <div class="small text-muted"><i class="bi bi-wallet2 me-1"></i><?= htmlspecialchars($expense['payment_method_name'] ?? 'Cash') ?></div>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($expense['description']) ?></td>
                                    <td class="text-end fw-bold text-dark">
                                        ₱<?= number_format($expense['amount'], 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($expense['receipt_upload']): ?>
                                            <a href="uploads/<?= $expense['receipt_upload'] ?>" target="_blank" class="btn btn-sm btn-light receipt-btn text-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted opacity-50 small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open d-block mb-2 fs-2 opacity-25"></i>
                                    No records found for this user.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="card dashboard-card border-0">
            <div class="card-body empty-state-container">
                <i class="bi bi-search icon-large"></i>
                <h5 class="mt-3 fw-bold text-dark">Ready to Monitor</h5>
                <p class="text-muted">Select a spender from the dropdown menu to see their activity.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>