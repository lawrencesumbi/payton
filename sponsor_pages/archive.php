<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================================
   AUTO UPDATE BUDGET STATUS
========================================= */
$updateStatus = $conn->prepare("
    UPDATE budget
    SET status = 
        CASE
            WHEN CURDATE() BETWEEN start_date AND end_date
            THEN 'Active'
            ELSE 'Inactive'
        END
");
$updateStatus->execute();

/* =========================================
   FETCH ONLY INACTIVE (ARCHIVED) BUDGETS
========================================= */
$stmt = $conn->prepare("
    SELECT b.*, u.fullname as spender_name
    FROM budget b
    LEFT JOIN users u ON b.user_id = u.id
    WHERE (b.user_id = ? OR b.sponsor_id = ?)
    AND b.status = 'Inactive'
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$archivedBudgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($status) {
    return "<span class='badge bg-secondary-subtle text-secondary'
            style='font-weight:600; padding:6px 12px; border-radius:6px;'>$status</span>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Archived Allowances | Payton</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

:root {
            --brand-purple: #6f42c1;
            --brand-purple-light: #f3f0ff;
            --brand-purple-dark: #59359a;
            --bg-body: #f8f9fa;
        }

body{
    background:#f8f9fa;
    font-family:'Inter',sans-serif;
}

.main-content{
    width: 100%;
    margin:auto;
    
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.table-container{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 30px rgba(0,0,0,0.05);
}

.table thead th{
    font-size:12px;
    text-transform:uppercase;
    color:#64748b;
    background:#f8fafc;
    padding:15px;
}

.table tbody td{
    padding:18px;
    vertical-align:middle;
}

.budget-name{
    font-weight:700;
}

.date-range{
    font-size:12px;
    color:#94a3b8;
}

.amount{
    font-weight:700;
    color:#6f42c1;
}



.btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid #edf2f7;
            background: white;
            transition: 0.2s;
        }

        .btn-action:hover {
            background: var(--brand-purple-light);
            color: var(--brand-purple);
            border-color: var(--brand-purple);
        }

</style>
</head>

<body>

<div class="main-content">

<div class="header">
    <div>
        <h2 class="fw-bold">Archived Allowances</h2>
        <p class="text-muted small">Expired or inactive allowance records.</p>
    </div>

    
</div>


<div class="table-container">

<div class="table-responsive">
<table class="table">

<thead>
<tr>
<th>Reference</th>
<th>Spender</th>
<th>Status</th>
<th>Amount</th>
<th class="text-end">Actions</th>
</tr>
</thead>

<tbody>

<?php if(!empty($archivedBudgets)): ?>

<?php foreach($archivedBudgets as $budget): ?>

<tr>

<td>
<span class="budget-name">
<?= htmlspecialchars($budget['budget_name']) ?>
</span>

<div class="date-range">
<?= date("M d",strtotime($budget['start_date'])) ?>
—
<?= date("M d, Y",strtotime($budget['end_date'])) ?>
</div>
</td>

<td>

<div class="d-flex align-items-center">

<div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2"
style="width:32px;height:32px;font-size:11px;font-weight:800;color:#6f42c1;">

<?= strtoupper(substr($budget['spender_name'],0,2)) ?>

</div>

<?= htmlspecialchars($budget['spender_name'] ?? 'N/A') ?>

</div>

</td>

<td>
<?= getStatusBadge($budget['status']) ?>
</td>

<td>
<span class="amount">
₱<?= number_format($budget['budget_amount'],2) ?>
</span>
</td>

<td class="text-end">
                        <a href="sponsor.php?page=monitoring_page&spender_id=<?= $budget['user_id'] ?>&allowance_id=<?= $budget['id'] ?>" class="btn-action">
                            <i class="bi bi-eye"></i>
                        </a>                         
                           
                        </td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="4" class="text-center text-muted py-5">
No archived allowances found.
</td>
</tr>

<?php endif; ?>

</tbody>
</table>
</div>

</div>

</div>

</body>
</html>