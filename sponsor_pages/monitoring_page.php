<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sponsor_id = $_SESSION['user_id'];
$selected_spender = $_GET['spender_id'] ?? null;
$selected_allowance = $_GET['allowance_id'] ?? null;

/* ==========================================
   FETCH SPENDERS
   ========================================== */
$stmt = $conn->prepare("
    SELECT u.id, u.fullname 
    FROM users u
    INNER JOIN sponsor_spender ss ON u.id = ss.spender_id
    WHERE ss.sponsor_id = ?
");
$stmt->execute([$sponsor_id]);
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
    // Get Expenses
    $stmt = $conn->prepare("
        SELECT e.*, c.category_name, pm.payment_method_name
        FROM expenses e
        LEFT JOIN category c ON e.category_id = c.id
        LEFT JOIN payment_method pm ON e.payment_method_id = pm.id
        WHERE e.user_id = ? AND e.budget_id = ?
        ORDER BY e.expense_date DESC
    ");
    $stmt->execute([$selected_spender, $selected_allowance]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expenses as $ex) {
        $total_spent += $ex['amount'];
    }

    // Get Budget Info
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
        :root {
            --primary: #7f308f;
            --bg-body: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: #334155;
            
        }

        .main-wrapper {
            width: 100%;
            margin: 0 auto;
        }

        /* Top Bar Layout */
        .top-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            align-items: stretch;
            flex-wrap: wrap;
        }

        /* FLEX SELECTION FORM */
        .selection-container {
            flex: 2; /* Takes up more space */
            min-width: 400px;
        }

        .selection-form {
            display: flex;
            gap: 15px;
            height: 100%;
        }

        .select-box {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        /* Stats Cards Layout */
        .stats-container {
            flex: 3;
            display: flex;
            gap: 15px;
        }

        .stat-card {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            border-left: 5px solid var(--primary);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }

        /* Table Styling */
        .activity-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table thead {
            background-color: #f1f5f9;
        }

        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            padding: 15px 20px;
            border: none;
        }

        .badge-category {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .top-row { flex-direction: column; }
            .selection-form { flex-direction: column; }
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
            <div class="p-4 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Transactions</h6>
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
                                    <td class="ps-4 fw-semibold"><?= date("M d, Y", strtotime($expense['expense_date'])) ?></td>
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
                            <tr><td colspan="6" class="text-center py-5 text-muted">No expenses recorded for this allowance.</td></tr>
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