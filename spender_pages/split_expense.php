<?php
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch friends
$stmt = $conn->prepare("
    SELECT u.id, u.fullname
    FROM users u
    JOIN spender_friends f ON (u.id=f.requester_id OR u.id=f.addressee_id)
    WHERE (f.requester_id=? OR f.addressee_id=?) AND u.id != ?
    ORDER BY u.fullname
");
$stmt->execute([$user_id,$user_id,$user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$stmt = $conn->query("SELECT id, category_name FROM category ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if(isset($_POST['create_expense'])){
    $description = trim($_POST['description']);
    $total_amount = floatval($_POST['amount']);
    $payment_method_id = intval($_POST['payment_method_id']);
    $category_id = intval($_POST['category_id']);
    $expense_date = $_POST['expense_date'];
    $selected_friends = $_POST['participants'] ?? [];

    $participants = $selected_friends;
    $participants[] = $user_id;

    if(count($participants) < 1){
        $message = "Please select at least one participant.";
    } elseif($total_amount <= 0){
        $message = "Amount must be greater than zero.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO expenses (user_id, category_id, description, amount, payment_method_id, expense_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $category_id, $description, $total_amount, $payment_method_id, $expense_date]);
        $expense_id = $conn->lastInsertId();

        $split_amount = round($total_amount / count($participants), 2);
        $stmt = $conn->prepare("
            INSERT INTO expense_shares (expense_id, user_id, amount_owed)
            VALUES (?, ?, ?)
        ");
        foreach($participants as $pid){
            $amount = ($pid == $user_id) ? 0 : $split_amount;
            $stmt->execute([$expense_id, $pid, $amount]);
        }
        $message = "Split expense created successfully!";
    }
}

// Fetch Split Expenses
$stmt = $conn->prepare("
    SELECT e.id, e.description, e.amount, e.expense_date, c.category_name
    FROM expenses e
    JOIN category c ON c.id = e.category_id
    WHERE e.user_id = ? AND EXISTS (
        SELECT 1 FROM expense_shares es WHERE es.expense_id = e.id
    )
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
    <title>Manage Splits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #e0e7ff;
            --danger: #ef4444;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); margin: 0; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 24px; }

        /* Header Area */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-header h1 { font-size: 1.85rem; font-weight: 700; margin: 0; color: #0f172a; }

        /* Table Card */
        .card { background: white; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 14px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; }
        td { padding: 18px 20px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #f8fafc; }

        /* Category Pill */
        .cat-pill { padding: 4px 10px; background: var(--primary-light); color: var(--primary); border-radius: 6px; font-size: 0.8rem; font-weight: 600; }

        /* Buttons */
        .btn { padding: 10px 16px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-main { background: var(--primary); color: white; }
        .btn-main:hover { background: #4f46e5; transform: translateY(-1px); }
        .btn-icon { padding: 8px; border-radius: 8px; background: transparent; border: 1px solid var(--border); }
        .btn-icon:hover { background: #f1f5f9; }
        .btn-delete { color: var(--danger); }
        .btn-delete:hover { background: #fef2f2; border-color: #fee2e2; }

        /* Modal Styling */
        #modalOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); justify-content:center; align-items:center; z-index: 1000; }
        .modal-card { background: white; padding: 32px; border-radius: 20px; width: 100%; max-width: 480px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .modal-card h2 { margin-top: 0; font-size: 1.5rem; margin-bottom: 24px; }
        
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text-muted); }
        .input-box { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 10px; font-family: inherit; font-size: 1rem; box-sizing: border-box; }
        .input-box:focus { border-color: var(--primary); outline: 3px solid #6366f120; }

        /* Friend Selection Tags */
        .friend-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 24px; }
        .friend-item { display: flex; align-items: center; gap: 8px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 0.9rem; }
        .friend-item:has(input:checked) { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
        .friend-item input { margin: 0; cursor: pointer; }

        .modal-actions { display: flex; justify-content: flex-end; gap: 12px; }
        .alert { background: #dcfce7; color: #166534; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>Split Expenses</h1>
        <button class="btn btn-main" onclick="openModal()">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-60H6"/></svg>
            Add New Split
        </button>
    </div>

    <?php if($message): ?>
        <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($expenses)): ?>
                    <?php foreach($expenses as $e): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($e['description']) ?></td>
                        <td><span class="cat-pill"><?= htmlspecialchars($e['category_name']) ?></span></td>
                        <td style="color: #0f172a; font-weight: 600;">₱<?= number_format($e['amount'],2) ?></td>
                        <td style="color: var(--text-muted);"><?= date("M d", strtotime($e['expense_date'])) ?></td>
                        <td style="text-align:right">
                            <a href="spender.php?page=view_split_expense&expense_id=<?= $e['id'] ?>" class="btn btn-icon" title="View Details">👁</a>
                            <a href="edit_split_expense.php?expense_id=<?= $e['id'] ?>" class="btn btn-icon" title="Edit">✏️</a>
                            <form style="display:inline;" action="delete_split_expense.php" method="POST" onsubmit="return confirm('Delete this split?');">
                                <input type="hidden" name="expense_id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-icon btn-delete" title="Delete">🗑</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 60px 0; color: var(--text-muted);">
                            <p>No split expenses found. Start by adding one!</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalOverlay">
    <div class="modal-card">
        <h2>Create Split Expense</h2>
        <form method="POST">
            <label>What was this for?</label>
            <input type="text" name="description" class="input-box" placeholder="Dinner, Movie, etc." required>

            <div style="display:flex; gap:16px;">
                <div style="flex:1;">
                    <label>Total Amount</label>
                    <input type="number" step="0.01" name="amount" class="input-box" placeholder="0.00" required>
                </div>
                <div style="flex:1;">
                    <label>Date</label>
                    <input type="date" name="expense_date" class="input-box" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div style="display:flex; gap:16px;">
                <div style="flex:1;">
                    <label>Category</label>
                    <select name="category_id" class="input-box" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Payment Method</label>
                    <select name="payment_method_id" class="input-box" required>
                        <option value="1">Cash</option>
                        <option value="2">Debit Card</option>
                        <option value="3">Credit Card</option>
                        <option value="4">GCash</option>
                        <option value="5">Maya / Paymaya</option>
                        <option value="6">Bank Transfer</option>
                        <option value="7">Online Payment</option>
                        <option value="8">Check</option>
                    </select>
                </div>
            </div>

            <label>Who's splitting this?</label>
            <div class="friend-grid">
                <?php foreach($friends as $f): ?>
                    <label class="friend-item">
                        <input type="checkbox" name="participants[]" value="<?= $f['id'] ?>">
                        <?= htmlspecialchars($f['fullname']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="closeModal()" class="btn" style="background:#f1f5f9;">Cancel</button>
                <button type="submit" name="create_expense" class="btn btn-main">Create Split</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(){document.getElementById('modalOverlay').style.display='flex';}
function closeModal(){document.getElementById('modalOverlay').style.display='none';}
window.onclick = function(e){if(e.target==document.getElementById('modalOverlay')) closeModal();}
</script>
</body>
</html>