<?php
require 'db.php';

// 1. Ensure Session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$searchTerm = $_GET['search'] ?? '';

/* =====================================================
    FETCH ALL INACTIVE BUDGETS
===================================================== */
$budgetStmt = $conn->prepare("
    SELECT id, budget_name, budget_amount, start_date, end_date 
    FROM budget 
    WHERE user_id = :user_id AND status = 'Inactive'
    ORDER BY end_date DESC
");
$budgetStmt->execute(['user_id' => $user_id]);
$inactiveBudgets = $budgetStmt->fetchAll(PDO::FETCH_ASSOC);

// Filter by search term if provided
if (!empty($searchTerm)) {
    $inactiveBudgets = array_filter($inactiveBudgets, function($budget) use ($searchTerm) {
        return stripos($budget['budget_name'], $searchTerm) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Archive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-body: #f8fafc; 
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --accent-purple: #7c3aed;
            --accent-purple-light: #f5f0ff;
            --shadow: rgba(0, 0, 0, 0.1);
            --table-header-bg: #f8fafc;
            --badge-bg: #f1f5f9;
            --badge-text: #475569;
        }

        [data-theme="dark"] {
            --bg-body: #12141a;
            --bg-card: #191c24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #2a2e39;
            --border-light: #374151;
            --accent-purple: #a855f7;
            --accent-purple-light: #373250;
            --shadow: rgba(0,0,0,0.2);
            --table-header-bg: #21252e;
            --badge-bg: #2a2e39;
            --badge-text: #cbd5e1;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); transition: background 0.3s ease;}
        
        /* --- Force Hide Scrollbar but allow scrolling --- */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            -ms-overflow-style: none;  
            scrollbar-width: none;  
        }

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
            color: var(--accent-purple);
            background: var(--accent-purple-light);
            padding: 12px;
            border-radius: 12px;
        }

        /* Budget Section Card */
        .budget-group {
            background: var(--bg-card);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            margin-bottom: 40px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px var(--shadow);
            transition: background 0.3s ease;
        }

        .budget-summary-bar {
            background: var(--bg-card);
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .budget-info h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }

        .budget-info span {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .budget-stats {
            display: flex;
            gap: 20px;
        }

        .stat-pill {
            background: var(--bg-body);
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .stat-label { font-size: 10px; text-transform: uppercase; color: var(--text-muted); font-weight: 700; }
        .stat-val { font-size: 14px; font-weight: 800; color: var(--accent-purple); }

        /* Table Styling */
        .table-container { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            padding: 12px 25px; 
            background: var(--table-header-bg); 
            font-size: 12px; 
            text-transform: uppercase; 
            color: var(--text-muted); 
            letter-spacing: 0.05em;
        }
        td { padding: 15px 25px; border-bottom: 1px solid var(--border-light); font-size: 14px; color: var(--text-main); }
        tr:last-child td { border-bottom: none; }
        
        .category-badge {
            background: var(--badge-bg);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--badge-text);
        }

        .no-data {
            text-align: center;
            padding: 60px;
            color: var(--text-muted);
        }
        .budget-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap; /* Allows pills to drop to next line on mobile */
        }

        .stat-pill {
            min-width: 100px; /* Ensures pills have a consistent look */
            padding: 8px 12px;
            background: var(--bg-body);
            border-radius: 10px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s;
        }

        .stat-pill:hover {
            transform: translateY(-2px); /* Subtle lift effect */
        }
    </style>
</head>
<body>

    <div class="archive-header">
        <i class="fa-solid fa-box-archive"></i>
        <div>
            <h1 style="font-size: 24px; font-weight: 900; color: var(--text-main);">Expense Archive</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Review your past spending performance.</p>
        </div>
    </div>

    <?php if (empty($inactiveBudgets)): ?>
        <div class="budget-group no-data">
            <i class="fa-solid fa-folder-open" style="font-size: 40px; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No inactive budgets found in your history.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($inactiveBudgets as $budget): 
        // 1. Fetch expenses
        $expStmt = $conn->prepare("SELECT e.*, c.category_name FROM expenses e LEFT JOIN category c ON e.category_id = c.id WHERE e.budget_id = ? ORDER BY e.expense_date DESC");
        $expStmt->execute([$budget['id']]); 
        $expenses = $expStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Calculations
        $totalSpent = array_sum(array_column($expenses, 'amount'));
        $remaining = (float)$budget['budget_amount'] - $totalSpent;

        // 3. Status Logic - MAKE SURE THESE NAMES MATCH THE HTML BELOW
        $isOverspent = $remaining < 0;
        $remainingColor = $isOverspent ? '#ef4444' : '#10b981'; 
        $remainingLabel = $isOverspent ? 'Overspent' : 'Remaining';
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
                    <div class="stat-val">₱<?= number_format($budget['budget_amount'], 2) ?></div>
                </div>
                <div class="stat-pill">
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-val" style="color: var(--text-main);">₱<?= number_format($totalSpent, 2) ?></div>
                </div>
                <div class="stat-pill" style="border-color: <?= $remainingColor ?>44; background: <?= $remainingColor ?>05;">
                    <div class="stat-label" style="color: <?= $remainingColor ?>;"><?= $remainingLabel ?></div>
                    <div class="stat-val" style="color: <?= $remainingColor ?>;">
                        ₱<?= number_format(abs($remaining), 2) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No.</th> <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No expenses recorded for this period.</td></tr>
                    <?php else: ?>
                        <?php 
                            $num = 1; // Initialize counter
                            foreach ($expenses as $e): 
                        ?>
                        <tr>
                            <td style="color: var(--text-muted);"><?= $num++ ?>.</td> <td><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
                            <td style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($e['description']) ?></td>
                            <td><span class="category-badge"><?= htmlspecialchars($e['category_name'] ?? 'Uncategorized') ?></span></td>
                            <td style="font-weight: 800; color: var(--text-main);">₱<?= number_format($e['amount'], 2) ?></td>
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