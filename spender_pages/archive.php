<?php
require 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =====================================================
   FETCH ALL INACTIVE BUDGETS & THEIR EXPENSES
===================================================== */
// We fetch the budgets first to create the sections
$budgetStmt = $conn->prepare("
    SELECT id, budget_name, budget_amount, start_date, end_date 
    FROM budget 
    WHERE user_id = :user_id AND status = 'Inactive'
    ORDER BY end_date DESC
");
$budgetStmt->execute(['user_id' => $user_id]);
$inactiveBudgets = $budgetStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Archive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; color: #1e293b;}
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
        .archive-header {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .archive-header i {
            font-size: 24px;
            color: #7c3aed;
            background: #f5f0ff;
            padding: 12px;
            border-radius: 12px;
        }

        /* Budget Section Card */
        .budget-group {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            margin-bottom: 40px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .budget-summary-bar {
            background: #ffffff;
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .budget-info h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .budget-info span {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .budget-stats {
            display: flex;
            gap: 20px;
        }

        .stat-pill {
            background: #f8fafc;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid #eef1f6;
            text-align: center;
        }

        .stat-label { font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700; }
        .stat-val { font-size: 14px; font-weight: 800; color: #7c3aed; }

        /* Table Styling */
        .table-container { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            padding: 12px 25px; 
            background: #f8fafc; 
            font-size: 12px; 
            text-transform: uppercase; 
            color: #64748b; 
            letter-spacing: 0.05em;
        }
        td { padding: 15px 25px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        tr:last-child td { border-bottom: none; }
        
        .category-badge {
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .no-data {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="archive-header">
        <i class="fa-solid fa-box-archive"></i>
        <div>
            <h1 style="font-size: 24px; font-weight: 900;">Expense Archive</h1>
            <p style="color: #64748b; font-size: 14px;">Review your past spending performance.</p>
        </div>
    </div>

    <?php if (empty($inactiveBudgets)): ?>
        <div class="budget-group no-data">
            <i class="fa-solid fa-folder-open" style="font-size: 40px; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No inactive budgets found in your history.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($inactiveBudgets as $budget): 
        // Fetch expenses for this specific inactive budget
        $expStmt = $conn->prepare("
            SELECT e.*, c.category_name 
            FROM expenses e
            LEFT JOIN category c ON e.category_id = c.id
            WHERE e.budget_id = ?
            ORDER BY e.expense_date DESC
        ");
        $expStmt->execute([$budget['id']]);
        $expenses = $expStmt->fetchAll(PDO::FETCH_ASSOC);

        $totalSpent = array_sum(array_column($expenses, 'amount'));
    ?>

    <div class="budget-group">
        <div class="budget-summary-bar">
            <div class="budget-info">
                <h2><?= htmlspecialchars($budget['budget_name']) ?></h2>
                <span><?= date('M d, Y', strtotime($budget['start_date'])) ?> — <?= date('M d, Y', strtotime($budget['end_date'])) ?></span>
            </div>
            <div class="budget-stats">
                <div class="stat-pill">
                    <div class="stat-label">Limit</div>
                    <div class="stat-val">$<?= number_format($budget['budget_amount'], 2) ?></div>
                </div>
                <div class="stat-pill">
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-val" style="color: #0f172a;">$<?= number_format($totalSpent, 2) ?></div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="4" style="text-align:center; color:#94a3b8;">No expenses recorded for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($e['description']) ?></td>
                            <td><span class="category-badge"><?= htmlspecialchars($e['category_name'] ?? 'Uncategorized') ?></span></td>
                            <td style="font-weight: 800; color: #1e293b;">$<?= number_format($e['amount'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endforeach; ?>

</body>
</html>