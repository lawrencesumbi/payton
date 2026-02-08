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
    SELECT e.id, e.description, e.amount, e.expense_date, e.receipt_upload,
           c.category_name, pm.payment_method_name
    FROM expenses e
    JOIN category c ON e.category_id = c.id
    JOIN payment_method pm ON e.payment_method_id = pm.id
    WHERE e.user_id = ?
    ORDER BY e.expense_date DESC
");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
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


h2 {
    color: #7210c8;
    margin-bottom: 20px;
}

.table-container {
    overflow-x: auto;
    background: white;
    border-radius: 12px;
    padding: 20px;
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
    border-radius: 8px;
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

<h2>My Expenses</h2>

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
                        <img src="uploads/<?= htmlspecialchars($exp['receipt_upload']) ?>" class="receipt-img" alt="Receipt">
                    <?php else: ?>
                        -
                    <?php endif; ?>
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

    <div class="form-group">
      <label>Description</label>
      <input type="text" name="description" placeholder="e.g. Jollibee" required>
    </div>

    <div class="form-group">
      <label>Amount</label>
      <input type="number" name="amount" placeholder="e.g. 250" required min="1">
    </div>

    <div class="form-group">
      <label>Payment Method</label>
      <select name="payment_method_id" required>
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
    </div>

    <button type="submit" class="btn-save">Add Expense</button>
</form>

    </div>
  </div>

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
  </script>

</body>
</html>
