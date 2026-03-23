<?php
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- ACTION HANDLER: MARK AS PAID ---
if (isset($_POST['mark_paid'])) {
    $person_id = $_POST['person_id']; 
    $target_expense_id = $_POST['expense_id'];

    $update_stmt = $conn->prepare("
        UPDATE expense_shares 
        SET status = 'Paid' 
        WHERE expense_id = ? AND people_id = ?
    ");
    
    if($update_stmt->execute([$target_expense_id, $person_id])) {
        $message = "Payment status updated!";
    }
}

// 1. Fetch all expenses belonging to this user that have splits
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

// 2. Determine which expense to display
$expense_id = $_GET['expense_id'] ?? ($all_expenses[0]['id'] ?? null);

if(!$expense_id){
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>
            <h2>No split expenses found.</h2>
            <p>Go to the Split Expenses page to create one!</p>
         </div>");
}

// 3. Fetch general expense details
$stmt = $conn->prepare("
    SELECT e.description, e.amount, e.expense_date, c.category_name, pm.payment_method_name
    FROM expenses e
    JOIN category c ON c.id = e.category_id
    LEFT JOIN payment_method pm ON pm.id = e.payment_method_id
    WHERE e.id = ? AND e.user_id = ?
");
$stmt->execute([$expense_id, $user_id]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Fetch participants and their specific saved shares
$stmt = $conn->prepare("
    SELECT p.id as person_id, p.name, es.amount_owed, es.status
    FROM expense_shares es
    JOIN people p ON p.id = es.people_id
    WHERE es.expense_id = ?
");
$stmt->execute([$expense_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- NEW CALCULATION FOR "YOUR" SHARE ---
// Instead of dividing equally, we sum up what others owe and subtract it from the total.
$total_others_owe = 0;
foreach($participants as $p) {
    $total_others_owe += $p['amount_owed'];
}
$your_share = $expense['amount'] - $total_others_owe;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Breakdown</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1; --bg: #f8fafc; --card: #ffffff;
            --text-main: #1e293b; --text-muted: #64748b;
            --success: #22c55e; --danger: #ef4444; --border: #e2e8f0;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); margin: 0; }
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
        .container { width: 100%; padding: 0 20px; box-sizing: border-box; }
        .selector { width: 100%; padding: 14px; border-radius: 12px; border: 2px solid var(--border); background: var(--card); font-weight: 600; font-family: inherit; cursor: pointer; margin-bottom: 24px; }
        .split-container { display: flex; background: var(--card); border-radius: 24px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden; min-height: 460px; }
        .left-col { flex: 1; padding: 40px; background: linear-gradient(145deg, #6366f1, #4f46e5); color: white; display: flex; flex-direction: column; justify-content: center; }
        .left-col h1 { margin: 10px 0; font-size: 2rem; font-weight: 800; }
        .tag { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .detail-item { margin-bottom: 24px; }
        .detail-item label { display: block; font-size: 0.75rem; text-transform: uppercase; opacity: 0.8; letter-spacing: 0.05em; margin-bottom: 4px; }
        .detail-item span { font-size: 1.4rem; font-weight: 600; }
        .right-col { flex: 1.6; padding: 40px; background: white; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid var(--border); }
        td { padding: 18px 12px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        .badge { padding: 6px 12px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: var(--success); }
        .badge-unpaid { background: #fee2e2; color: var(--danger); }
        .btn-pay { background: #fff; color: var(--primary); border: 1.5px solid var(--primary); padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; cursor: pointer; text-transform: uppercase; transition: 0.2s; }
        .btn-pay:hover { background: var(--primary); color: white; }
        @media (max-width: 850px) { .split-container { flex-direction: column; } }
    </style>
</head>
<body>

<div class="container">
    <form method="GET">
        <input type="hidden" name="page" value="view_split_expense">
        <select name="expense_id" class="selector" onchange="this.form.submit()">
            <?php foreach($all_expenses as $ex): ?>
                <option value="<?= $ex['id'] ?>" <?= $ex['id']==$expense_id?'selected':'' ?>>
                    <?= htmlspecialchars($ex['description']) ?> (₱<?= number_format($ex['amount'], 2) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="split-container">
        <div class="left-col">
            <div class="tag"><?= htmlspecialchars($expense['category_name']) ?></div>
            <h1><?= htmlspecialchars($expense['description']) ?></h1>
            
            <div style="margin-top: 30px;">
                <div class="detail-item">
                    <label>Total Bill</label>
                    <span>₱<?= number_format($expense['amount'], 2) ?></span>
                </div>
                <div class="detail-item">
                    <label>Date Spent</label>
                    <span><?= date("F d, Y", strtotime($expense['expense_date'])) ?></span>
                </div>
                <div class="detail-item">
                    <label>Payment Method</label>
                    <span><?= htmlspecialchars($expense['payment_method_name'] ?? 'Cash') ?></span>
                </div>
            </div>
        </div>

        <div class="right-col">
            <h3 style="margin-top: 0; margin-bottom: 24px;">Breakdown</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Share</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 700;">You</td>
                        <td style="font-weight: 700;">₱<?= number_format($your_share, 2) ?></td>
                        <td><span class="badge" style="background: #e0e7ff; color: #4338ca;">Paid</span></td>
                        <td style="text-align: right; color: #cbd5e1;">—</td>
                    </tr>

                    <?php foreach($participants as $p): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></td>
                        <td>₱<?= number_format($p['amount_owed'], 2) ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($p['status']) ?>">
                                <?= $p['status'] ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <?php if (strtolower($p['status']) === 'unpaid'): ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="person_id" value="<?= $p['person_id'] ?>">
                                    <input type="hidden" name="expense_id" value="<?= $expense_id ?>">
                                    <button type="submit" name="mark_paid" class="btn-pay">Settle</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--success); font-weight: bold;">✓ Settled</span>
                            <?php endif; ?>
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