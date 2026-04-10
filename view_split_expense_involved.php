<?php
require_once "db.php";

$expense_id = $_GET['id'] ?? null;
$auth_hash = $_GET['auth'] ?? null;
$secret_key = "Karan_Secret_789"; // MUST match the one in process_split.php

// 1. VALIDATION: Check if the hash matches
if (!$expense_id || !$auth_hash || md5($expense_id . $secret_key) !== $auth_hash) {
    die("Invalid or expired access link.");
}

// 2. FETCH EXPENSE DATA
$stmt = $conn->prepare("
    SELECT e.*, c.category_name, u.fullname as owner_name 
    FROM expenses e 
    JOIN category c ON e.category_id = c.id 
    JOIN users u ON e.user_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$expense_id]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) die("Expense not found.");

// 3. FETCH ALL SHARES FOR THIS EXPENSE
$shareStmt = $conn->prepare("
    SELECT es.*, p.name as person_name, p.email 
    FROM expense_shares es 
    JOIN people p ON es.people_id = p.id 
    WHERE es.expense_id = ?
");
$shareStmt->execute([$expense_id]);
$shares = $shareStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settle Shared Expense</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --bg: #f8fafc; --text: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); padding: 40px 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .amount-card { background: #f1f5f9; padding: 20px; border-radius: 12px; text-align: center; margin: 20px 0; }
        .status-pill { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-unpaid { background: #fee2e2; color: #ef4444; }
        .status-paid { background: #dcfce7; color: #22c55e; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
        .btn-settle { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Expense Details</h1>
        <p>Created by <strong><?= htmlspecialchars($expense['owner_name']) ?></strong></p>
    </div>

    <div class="amount-card">
        <span style="color: #64748b;">Total Bill</span>
        <h2 style="margin: 5px 0; font-size: 32px;">₱<?= number_format($expense['amount'], 2) ?></h2>
        <p><strong>Description:</strong> <?= htmlspecialchars($expense['description']) ?></p>
    </div>

    <h3>Who's Involved</h3>
    <table>
        <?php foreach($shares as $s): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($s['person_name']) ?></strong><br>
                <small style="color: #64748b;"><?= htmlspecialchars($s['email']) ?></small>
            </td>
            <td style="text-align: right;">
                <span style="font-weight: 700;">₱<?= number_format($s['amount_owed'], 2) ?></span><br>
                <span class="status-pill <?= $s['status'] == 'Paid' ? 'status-paid' : 'status-unpaid' ?>">
                    <?= $s['status'] ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div style="margin-top: 30px; border-top: 2px dashed #eee; padding-top: 20px;">
        <p style="text-align: center; font-size: 14px; color: #64748b;">
            To settle your share, please contact <strong><?= $expense['owner_name'] ?></strong> 
            or use their preferred payment method.
        </p>
        </div>
</div>

</body>
</html>