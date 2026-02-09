<?php

require 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch expenses for the current user
$stmt = $conn->prepare("
    SELECT 
        e.id,
        e.description,
        e.amount,
        e.expense_date,
        e.receipt_upload,
        e.category_id,
        e.payment_method_id,
        c.category_name,
        pm.payment_method_name
    FROM expenses e
    JOIN category c ON e.category_id = c.id
    JOIN payment_method pm ON e.payment_method_id = pm.id
    WHERE e.user_id = ?
    ORDER BY e.expense_date DESC
");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories
$catStmt = $conn->prepare("SELECT id, category_name FROM category");
$catStmt->execute();
$allCategories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate category-wise breakdown
$categoryBreakdown = [];
$totalExpenses = 0;

// Initialize all categories with 0
foreach ($allCategories as $cat) {
    $categoryBreakdown[$cat['category_name']] = 0;
}

// Add expenses to categories
foreach ($expenses as $exp) {
    $category = $exp['category_name'];
    $amount = floatval($exp['amount']);
    
    $categoryBreakdown[$category] += $amount;
    $totalExpenses += $amount;
}

// Calculate percentages
$categoryPercentages = [];
if ($totalExpenses > 0) {
    foreach ($categoryBreakdown as $category => $amount) {
        $categoryPercentages[$category] = round(($amount / $totalExpenses) * 100, 1);
    }
} else {
    // If no expenses, all percentages are 0
    foreach ($categoryBreakdown as $category => $amount) {
        $categoryPercentages[$category] = 0;
    }
}
arsort($categoryBreakdown); // Sort by amount descending

// Calculate current month and previous month totals for trend
$thisMonthTotal = 0;
$prevMonthTotal = 0;

$now = new DateTime();
$startOfThisMonth = (clone $now)->modify('first day of this month')->setTime(0,0,0);
$startOfNextMonth = (clone $startOfThisMonth)->modify('+1 month');
$startOfPrevMonth = (clone $startOfThisMonth)->modify('-1 month');

foreach ($expenses as $exp) {
  $d = new DateTime($exp['expense_date']);
  if ($d >= $startOfThisMonth && $d < $startOfNextMonth) {
    $thisMonthTotal += floatval($exp['amount']);
  }
  if ($d >= $startOfPrevMonth && $d < $startOfThisMonth) {
    $prevMonthTotal += floatval($exp['amount']);
  }
}

$monthChangePct = null;
if ($prevMonthTotal > 0) {
  $monthChangePct = round((($thisMonthTotal - $prevMonthTotal) / $prevMonthTotal) * 100, 1);
} elseif ($thisMonthTotal > 0) {
  $monthChangePct = 100.0; // from 0 to some value
} else {
  $monthChangePct = 0.0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Expense</title>

  <!-- FONT AWESOME CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }

    body { background: #f5f5f5; min-height:100vh; position: relative;}

    /* =========================
   ANALYTICS SECTION (CLEAN)
========================= */

.analytics-container {
  background: #ffffff;
  border-radius: 18px;
  padding: 15px;
  border: 1px solid #eef1f6;
  box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
  max-width: 100%;
  width: 100%;
}

.analytics-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.analytics-header i {
  font-size: 20px;
  color: #2f7cff;
  background: #eaf2ff;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  border: 1px solid #dbe7ff;
}

.analytics-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 800;
}

/* GRID */
.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 10px;
  margin-bottom: 5px;
}

/* STAT CARD (PASTEL LOOK) */
.stat-card {
  background: #ffffff;
  padding: 10px 10px;
  border-radius: 12px;
  border: 1px solid #eef1f6;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
  transition: 0.18s ease;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* move icon to the right inside stat cards */
.stat-card { display:flex; align-items:center; gap:10px; }
.stat-card-content { order:1; flex:1; }
.stat-icon { order:2; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:10px; }
.stat-icon i { font-size:18px; }

/* place icon on the right and content on the left */
.stat-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.stat-card-content { flex: 1; order: 1; text-align: left; }

.stat-icon { 
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 18px;
  order: 2;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
}

/* Small colored top bar like dashboard cards */
.stat-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: #2f7cff;
}

/* CONTENT */
.stat-card-content {
  position: relative;
  z-index: 1;
  flex: 1 1 auto;
}

.stat-icon {
  font-size: 20px;
  color: #ffffff;
  background: #7c3aed;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  border: none;
}

.trend-up { color: #059669; font-weight:800; }
.trend-down { color: #dc2626; font-weight:800; }

.stat-label {
  font-size: 10px;
  color: #64748b;
  margin-bottom: 6px;
  font-weight: 700;
  letter-spacing: 0.2px;
  text-transform: uppercase;
}

.stat-value {
  font-size: 16px;
  font-weight: 900;
  color: #0f172a;
  margin-bottom: 4px;
}

.stat-subtitle {
  font-size: 10px;
  color: #94a3b8;
  font-weight: 600;
}

/* =========================
   COLOR VARIANTS (INSPO)
========================= */
/* You can add these classes in HTML:
   class="stat-card stat-blue"
   class="stat-card stat-purple"
   class="stat-card stat-green"
   class="stat-card stat-orange"
*/

.stat-blue {
  background: #f3f8ff;
  border: 1px solid #dbe7ff;
}
.stat-blue::before { background: #8994f7; }

.stat-purple {
  background: #f7f3ff;
  border: 1px solid #e7dcff;
}
.stat-purple::before { background: #8b5cf6; }

.stat-green {
  background: #f0fdf7;
  border: 1px solid #ccf3df;
}
.stat-green::before { background: #f590e8; }

.stat-orange {
  background: #fff7ed;
  border: 1px solid #ffe2c3;
}
.stat-orange::before { background: #f8bf5c; }

/* =========================
   BREAKDOWN SECTION
========================= */

.categories-breakdown {
  background: #ffffff;
  padding: 22px;
  border-radius: 18px;
  border: 1px solid #eef1f6;
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 10px;
  margin-top: 12px;
}

.categories-grid .category-item {
  padding: 10px;
  border-bottom: none;
  border: 1px solid #e8ecf1;
  border-radius: 8px;
  background: #fafbfc;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}



@media (max-width: 1200px) {
  .categories-grid { grid-template-columns: repeat(4, 1fr); }
}

@media (max-width: 920px) {
  .categories-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 680px) {
  .categories-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 480px) {
  .categories-grid { grid-template-columns: 1fr; }
}

.breakdown-title {
  font-weight: 900;
  color: #0f172a;
  margin-bottom: 18px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.breakdown-title::before {
  content: "";
  width: 6px;
  height: 18px;
  background: #3d13f8;
  border-radius: 99px;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #f1f5f9;
  transition: 0.2s ease;
}

.category-item:last-child {
  border-bottom: none;
}

.category-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
}

.category-left { flex: 1; margin-right: 12px; }
.category-right { text-align: right; min-width: 120px; }

@media (max-width: 700px) {
  .stat-card { padding: 12px; }
}

.category-name {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 6px;
  font-size: 11px;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: #eaf0fb;
  border-radius: 99px;
  overflow: hidden;
  margin-bottom: 5px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #2f7cff, #60a5fa);
  transition: width 0.4s ease;
  border-radius: 99px;
}

.category-percent {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 700;
}

.category-amount {
  font-weight: 900;
  color: #7c3aed;
  min-width: 100px;
  text-align: right;
  font-size: 14px;
}

/* Remove global purple override */
h2 {
  color: #0f172a;
}


.table-container {
    overflow-x: auto;
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-top: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td { 
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background: #7210c8;
    color: white;
    font-weight: 700;
}

tr:hover {
    background: #f3f0ff;
}

.receipt-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.actions {
  gap: 8px;
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;     
}

.btn-edit,
.btn-delete {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;


  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-edit {
  background: #f3eaff;
  color: #7210c8;
}

.btn-edit:hover {
  background: #c181f8;
  color: white;
}

/* Receipt View Button (inside table) */
.btn-view-receipt {
  border: none;
  cursor: pointer;

  padding: 6px 10px;
  border-radius: 8px;

  font-size: 13px;
  font-weight: 600;

  background: #f5efff;
  color: #7210c8;


  border: 1px solid #e7d6ff;
  transition: 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 18px rgba(91, 33, 182, 0.08);
}

/* Hover */
.btn-view-receipt:hover {
  background: #f3eaff;
  color: #7210c8;  
  border-color: #c4b5fd;
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(91, 33, 182, 0.16);
}

/* Click */
.btn-view-receipt:active {
  transform: translateY(0);
  box-shadow: 0 8px 16px rgba(91, 33, 182, 0.12);
}

/* Focus (keyboard friendly) */
.btn-view-receipt:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.18);
}


.btn-delete {
  background: #ffecec;
  color: #a30000;
}

.btn-delete:hover {
  background: #a30000;
  color: white;
}








    /* FAB */
    .fab {
      position: fixed;
      bottom: 100px;
      right: 100px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      border: none;
      background: #7210c8;
      color: white;
      font-size: 28px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      z-index: 1000;
      transition: 0.3s;
    }

    .fab:hover { background: #af35e8; transform: translateY(-3px); }

    /* MODAL */
    .modal-overlay {
      display: none;
      position: fixed;
      top:0; left:0;
      width:100%; height:100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .expense-area {
      background: #f5eded;
      padding: 22px;
      border-radius: 18px;
      max-width: 800px;
      width: 90%;
      box-shadow: 0px 6px 18px rgba(0,0,0,0.25);
      position: relative;
      animation: pop 0.3s ease;
    }

    @keyframes pop {
      0% { transform: scale(0.7); opacity:0; }
      100% { transform: scale(1); opacity:1; }
    }

    .expense-area .close-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      background: #d9534f;
      color: white;
      border: none;
      font-size: 20px;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
    }

    .label{margin-bottom: 10px; font-weight:700; font-size:14px; color:#333;}
    .cat-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(120px,1fr)); gap: 12px; margin-bottom: 15px; }
    .cat-card { background:#f7f9fc; border-radius: 16px; padding: 16px; text-align:center; cursor:pointer; transition:0.2s; user-select:none; border: 2px solid transparent;}
    .cat-card.active { border:2px solid #7f00d4; background:#f1fbfd;}
    .cat-card:hover { transform: translateY(-3px);}
    .cat-icon { font-size:24px; margin-bottom:6px; }
    .cat-name { font-weight:700; font-size:14px; color:#222; }

    .expense-form { margin-top: 12px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display:block; font-weight:700; margin-bottom:6px; font-size:14px; color:#333; }
    .form-group input { width:100%; padding:12px; border-radius:14px; border:1px solid #dde3ec; font-size:14px; }
    .form-group input:focus { border-color:#9800d4; outline:none; }
    .form-group select {
        width: 100%;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #dde3ec;
        font-size: 14px;
        outline: none;
        background: white;
        cursor: pointer;
        transition: 0.2s ease;
    }
    .btn-save { width:100%; padding:14px; background:#6300d4; color:white; border:none; border-radius:14px; font-weight:800; cursor:pointer; }
    .btn-save:hover { opacity:0.9; }

  </style>
</head>
<body>

<!-- ANALYTICS SECTION -->
<div class="analytics-container">
    <div class="analytics-header">
        <i class="fas fa-chart-pie"></i>
        <h2>Expense Analytics</h2>
    </div>

    <div class="analytics-grid">

    <div class="stat-card stat-blue">
    <div class="stat-icon">
        <i class="fa-solid fa-wallet"></i>
    </div>

    <div class="stat-card-content">
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value">₱ <?= number_format($totalExpenses, 2) ?></div>
        <div class="stat-subtitle"><?= count($expenses) ?> transaction<?= count($expenses) !== 1 ? 's' : '' ?></div>
    </div>
</div>

<div class="stat-card stat-purple">
    <div class="stat-icon">
        <i class="fa-solid fa-chart-line"></i>
    </div>

    <div class="stat-card-content">
        <div class="stat-label">Average Expense</div>
        <div class="stat-value">
            ₱ <?= count($expenses) > 0 ? number_format($totalExpenses / count($expenses), 2) : '0.00' ?>
        </div>
        <div class="stat-subtitle">per transaction</div>
    </div>
</div>

<div class="stat-card stat-green">
    <div class="stat-icon">
        <i class="fa-solid fa-layer-group"></i>
    </div>

    <div class="stat-card-content">
        <div class="stat-label">Categories Tracked</div>
        <div class="stat-value"><?= count($categoryBreakdown) ?></div>
        <div class="stat-subtitle">expense types</div>
    </div>
</div>

<div class="stat-card stat-orange">
  <div class="stat-card-content">
    <div class="stat-label">This Month</div>
    <div class="stat-value">₱ <?= number_format($thisMonthTotal, 2) ?></div>
    <div class="stat-subtitle">
      <?php if ($monthChangePct > 0): ?>
        <span class="trend-up"><i class="fa-solid fa-arrow-up"></i> <?= abs($monthChangePct) ?>%</span> vs last month
      <?php elseif ($monthChangePct < 0): ?>
        <span class="trend-down"><i class="fa-solid fa-arrow-down"></i> <?= abs($monthChangePct) ?>%</span> vs last month
      <?php else: ?>
        <span><?= $monthChangePct ?>% vs last month</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
</div>

<?php // Categories are shown in the detailed breakdown below ?>

    </div>
</div>

<!-- DETAILED CATEGORY BREAKDOWN -->
<div class="categories-breakdown" style="margin-top:10px;">
  <div class="breakdown-title">Category Breakdown</div>

  <div class="categories-grid">
  <?php foreach ($allCategories as $cat):
        $name = $cat['category_name'];
        $amt = isset($categoryBreakdown[$name]) ? $categoryBreakdown[$name] : 0;
        $pct = isset($categoryPercentages[$name]) ? $categoryPercentages[$name] : 0;
  ?>
    <div class="category-item">
      <div style="flex:1; margin-right:12px;">
        <div class="category-name"><?= htmlspecialchars($name) ?></div>
        <div class="progress-bar"><div class="progress-fill" style="width: <?= $pct ?>%;"></div></div>
      </div>
      <div style="text-align:right; min-width:120px;">
        <div class="category-amount">₱ <?= number_format($amt, 2) ?></div>
        <div class="category-percent"><?= $pct ?>%</div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>

</div>

<div class="table-container">
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Date</th>
            <th>Receipt</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($expenses): ?>
        <?php foreach ($expenses as $index => $exp): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($exp['category_name']) ?></td>
                <td><?= htmlspecialchars($exp['description']) ?></td>
                <td>₱ <?= number_format($exp['amount'], 2) ?></td>
                <td><?= htmlspecialchars($exp['payment_method_name']) ?></td>
                <td><?= date("M d, Y", strtotime($exp['expense_date'])) ?></td>
                <td>
                    <?php if ($exp['receipt_upload']): ?>
                        <button class="btn-view-receipt" data-receipt="<?= htmlspecialchars($exp['receipt_upload']) ?>">👁 View</button>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <!-- ACTIONS -->
                <td class="actions"> 
                    <a href="#"
                        class="btn-edit"
                        data-id="<?= $exp['id'] ?>"
                        data-category="<?= $exp['category_id'] ?>"
                        data-description="<?= htmlspecialchars($exp['description']) ?>"
                        data-amount="<?= $exp['amount'] ?>"
                        data-payment="<?= $exp['payment_method_id'] ?>"
                        data-receipt="<?= htmlspecialchars($exp['receipt_upload']) ?>">
                        ✏️ Edit
                    </a>

                    <a href="delete_expense.php?id=<?= $exp['id'] ?>" class="btn-delete" onclick="return confirm('Delete this expense?');">🗑 Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">No expenses recorded yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>


</div>













  <!-- FAB BUTTON -->
  <button class="fab" title="Add Expense">
      <i class="fa-solid fa-plus"></i>
  </button>

  <!-- MODAL -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="expense-area">
      <button class="close-btn">&times;</button>
      <p class="label">Choose Category</p>
      <div class="cat-grid" id="catGrid">
        <div class="cat-card active" data-category-id="1"><div class="cat-icon">🍔</div><p class="cat-name">Food & Dining</p></div>
        <div class="cat-card" data-category-id="2"><div class="cat-icon">🚗</div><p class="cat-name">Transportation</p></div>
        <div class="cat-card" data-category-id="3"><div class="cat-icon">🏡</div><p class="cat-name">Housing / Rent</p></div>
        <div class="cat-card" data-category-id="4"><div class="cat-icon">💡</div><p class="cat-name">Bills & Utilities</p></div>
        <div class="cat-card" data-category-id="5"><div class="cat-icon">🏥</div><p class="cat-name">Health & Personal Care</p></div>
        <div class="cat-card" data-category-id="6"><div class="cat-icon">📚</div><p class="cat-name">Education</p></div>
        <div class="cat-card" data-category-id="7"><div class="cat-icon">🎮</div><p class="cat-name">Entertainment & Leisure</p></div>
        <div class="cat-card" data-category-id="8"><div class="cat-icon">🛍</div><p class="cat-name">Shopping</p></div>
        <div class="cat-card" data-category-id="9"><div class="cat-icon">💰</div><p class="cat-name">Savings & Investments</p></div>
        <div class="cat-card" data-category-id="10"><div class="cat-icon">📝</div><p class="cat-name">Miscellaneous</p></div>
      </div>

      <form class="expense-form" 
      action="add_expense_process.php" 
      method="POST" 
      enctype="multipart/form-data">
      
    <input type="hidden" name="category_id" id="categoryInput" value="1">
    <input type="hidden" name="expense_id" id="expenseId">

    <div class="form-group">
      <label>Description</label>
      <input type="text" name="description" id="descInput" placeholder="e.g. Jollibee" required>
    </div>

    <div class="form-group">
      <label>Amount</label>
      <input type="number" name="amount" id="amountInput" placeholder="e.g. 250" required min="1">
    </div>

    <div class="form-group">
      <label>Payment Method</label>
      <select name="payment_method_id" id="paymentInput" required>
        <option value="1">Cash</option>
        <option value="2">Credit Card</option>
        <option value="3">Debit Card</option>
        <option value="4">GCash</option>
        <option value="5">Maya / Paymaya</option>
        <option value="6">Bank Transfer</option>
        <option value="7">Online Payment</option>
        <option value="8">Check</option>
      </select>
    </div>

    <div class="form-group">
        <label>Proof of Purchase</label>
        <input type="file" name="receipt_upload" accept="image/*">
        <p id="currentReceipt" style="font-size:12px; color:#555;">No receipt uploaded</p>
    </div>

    <button type="submit" class="btn-save" id="submitBtn">Add Expense</button>
</form>

    </div>
  </div>




<!-- RECEIPT VIEW MODAL -->
<div class="modal-overlay" id="receiptModal">
  <div class="receipt-area">
    <button class="close-receipt-btn">&times;</button>
    <img id="receiptImage" src="" alt="Receipt" style="max-width:100%; max-height:80vh; display:block; margin:auto; border-radius:12px;">
  </div>
</div>

<style>
  #receiptModal {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
    z-index: 10000;
  }
  .receipt-area {
    background: #fff;
    padding: 20px;
    border-radius: 18px;
    position: relative;
    max-width: 90%;
  }
  .close-receipt-btn {
    position: absolute;
    top:10px;
    right:10px;
    font-size:24px;
    background:#d9534f;
    color:white;
    border:none;
    border-radius:50%;
    width:40px;
    height:40px;
    cursor:pointer;
  }
</style>


  <script>
    const fab = document.querySelector('.fab');
    const modalOverlay = document.getElementById('modalOverlay');
    const closeBtn = document.querySelector('.close-btn');
    const catCards = document.querySelectorAll('.cat-card');
    const categoryInput = document.getElementById('categoryInput');

    fab.addEventListener('click', () => {
      modalOverlay.style.display = 'flex';
    });

    closeBtn.addEventListener('click', () => {
      modalOverlay.style.display = 'none';
    });

    catCards.forEach(card => {
      card.addEventListener('click', () => {
        catCards.forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        categoryInput.value = card.getAttribute('data-category-id');
      });
    });






    const editButtons = document.querySelectorAll('.btn-edit');

editButtons.forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();

    const id = this.dataset.id;
    const category = this.dataset.category;
    const description = this.dataset.description;
    const amount = this.dataset.amount;
    const payment = this.dataset.payment;
    const receipt = this.dataset.receipt; // NEW

    // Open modal
    modalOverlay.style.display = 'flex';

    // Fill the form fields
    categoryInput.value = category;
    catCards.forEach(c => c.classList.remove('active'));
    document.querySelector(`.cat-card[data-category-id="${category}"]`)?.classList.add('active');

    document.querySelector('input[name="description"]').value = description;
    document.querySelector('input[name="amount"]').value = amount;
    document.querySelector('select[name="payment_method_id"]').value = payment;

    // If you want, display current receipt file name somewhere
    const receiptLabel = document.getElementById('currentReceipt');
    if(receipt) {
        receiptLabel.textContent = `Current Receipt: ${receipt.split('/').pop()}`;
    } else {
        receiptLabel.textContent = 'No receipt uploaded';
    }

    // Change button text
    const btnSave = document.querySelector('.btn-save');
    btnSave.textContent = 'Update Expense';

    // Add hidden input for expense ID
    let hiddenInput = document.querySelector('input[name="expense_id"]');
    if(!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'expense_id';
        document.querySelector('.expense-form').appendChild(hiddenInput);
    }
    hiddenInput.value = id;

    // Change form action to update
    document.querySelector('.expense-form').action = 'update_expense_process.php';
  });
});





const viewButtons = document.querySelectorAll('.btn-view-receipt');
const receiptModal = document.getElementById('receiptModal');
const receiptImage = document.getElementById('receiptImage');
const closeReceiptBtn = document.querySelector('.close-receipt-btn');

viewButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const imgSrc = btn.dataset.receipt; // "uploads/filename.png"
    receiptImage.src = imgSrc;
    receiptModal.style.display = 'flex';
  });
});

closeReceiptBtn.addEventListener('click', () => {
  receiptModal.style.display = 'none';
  receiptImage.src = ''; // clear image
});



    
  </script>

</body>
</html>
