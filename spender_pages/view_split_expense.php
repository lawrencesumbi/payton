<?php
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];

// Fetch all expenses that have splits
$stmt = $conn->prepare("
    SELECT e.id, e.description, e.amount, e.expense_date
    FROM expenses e
    WHERE e.user_id = ? AND EXISTS (
        SELECT 1 FROM expense_shares es WHERE es.expense_id = e.id
    )
    ORDER BY e.expense_date DESC
");
$stmt->execute([$user_id]);
$all_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$expense_id = $_GET['expense_id'] ?? ($all_expenses[0]['id'] ?? null);

if(!$expense_id){
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
            <h3>No split expenses found.</h3>
            <p>Go back and create one!</p>
         </div>");
}

// Fetch selected expense details
$stmt = $conn->prepare("
    SELECT e.description, e.amount, e.expense_date, c.category_name, pm.payment_method_name
    FROM expenses e
    JOIN category c ON c.id = e.category_id
    LEFT JOIN payment_method pm ON pm.id = e.payment_method_id
    WHERE e.id = ? AND e.user_id = ?
");
$stmt->execute([$expense_id, $user_id]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch participants
$stmt = $conn->prepare("
    SELECT u.fullname, es.amount_owed, es.status
    FROM expense_shares es
    JOIN users u ON u.id = es.user_id
    WHERE es.expense_id = ?
");
$stmt->execute([$expense_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Breakdown</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --bg: #f8fafc;
            --card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --border: #e2e8f0;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg); 
            color: var(--text-main); 
            margin: 0; 
        }

        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }

        /* Selector Styling */
        .header-section { margin-bottom: 24px; }
        .selector {
            width: 100%; padding: 14px; border-radius: 12px;
            border: 2px solid var(--border); background: var(--card);
            font-weight: 600; font-family: inherit; cursor: pointer;
        }

        /* Split Layout Container */
        .split-container {
            display: flex;
            background: var(--card);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            overflow: hidden;
            min-height: 450px;
        }

        /* Left Column: Overview */
        .left-col {
            flex: 1;
            padding: 40px;
            background: linear-gradient(145deg, #6366f1, #4f46e5);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left-col h1 { margin: 0 0 10px 0; font-size: 2rem; font-weight: 800; }
        .left-col .tag { 
            background: rgba(255,255,255,0.2); 
            padding: 4px 12px; border-radius: 8px; 
            font-size: 0.8rem; font-weight: 600; text-transform: uppercase;
            display: inline-block; margin-bottom: 20px;
        }

        .detail-list { margin-top: 30px; }
        .detail-item { margin-bottom: 20px; }
        .detail-item label { 
            display: block; font-size: 0.75rem; 
            text-transform: uppercase; opacity: 0.8; 
            letter-spacing: 0.05em; margin-bottom: 4px;
        }
        .detail-item span { font-size: 1.25rem; font-weight: 600; }

        /* Right Column: Table */
        .right-col {
            flex: 1.5;
            padding: 40px;
            background: white;
        }

        .right-col h3 { margin: 0 0 24px 0; font-size: 1.25rem; color: var(--text-main); }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid var(--border); }
        td { padding: 16px 12px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }

        .badge {
            padding: 6px 12px; border-radius: 99px;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        }
        .badge-paid { background: #dcfce7; color: var(--success); }
        .badge-unpaid { background: #fee2e2; color: var(--danger); }

        /* Responsive */
        @media (max-width: 850px) {
            .split-container { flex-direction: column; }
            .left-col { flex: none; padding: 30px; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header-section">
        <form method="GET">
            <input type="hidden" name="page" value="view_split_expense">
            <select name="expense_id" class="selector" onchange="this.form.submit()">
                <?php foreach($all_expenses as $ex): ?>
                    <option value="<?= $ex['id'] ?>" <?= $ex['id']==$expense_id?'selected':'' ?>>
                        <?= htmlspecialchars($ex['description']) ?> (₱<?= number_format($ex['amount'],2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="split-container">
        <div class="left-col">
            <div class="tag"><?= htmlspecialchars($expense['category_name']) ?></div>
            <h1><?= htmlspecialchars($expense['description']) ?></h1>
            
            <div class="detail-list">
                <div class="detail-item">
                    <label>Total Expense</label>
                    <span>₱<?= number_format($expense['amount'],2) ?></span>
                </div>
                <div class="detail-item">
                    <label>Date</label>
                    <span><?= date("F d, Y", strtotime($expense['expense_date'])) ?></span>
                </div>
                <div class="detail-item">
                    <label>Method</label>
                    <span><?= htmlspecialchars($expense['payment_method_name'] ?? 'None') ?></span>
                </div>
            </div>
        </div>

        <div class="right-col">
            <h3>Participants Breakdown</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Owed</th>
                        <th style="text-align: right;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($participants as $p): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($p['fullname']) ?></td>
                        <td>₱<?= number_format($p['amount_owed'],2) ?></td>
                        <td style="text-align: right;">
                            <span class="badge badge-<?= strtolower($p['status']) ?>">
                                <?= $p['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>