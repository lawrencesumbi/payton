<?php
require_once "db.php";
include 'log_helper.php';


if(!isset($_SESSION['user_id']) || !isset($_GET['id'])){
    header("Location: splits.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$expense_id = intval($_GET['id']);
$message = "";

/* =====================================================
    1. FETCH EXISTING EXPENSE DATA
===================================================== */
$stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ? AND user_id = ?");
$stmt->execute([$expense_id, $user_id]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$expense) die("Expense not found.");

// Fetch shares for this expense
$stmt = $conn->prepare("SELECT people_id, amount_owed FROM expense_shares WHERE expense_id = ?");
$stmt->execute([$expense_id]);
$shares = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns [people_id => amount]

/* =====================================================
    2. HANDLE UPDATE SUBMISSION
===================================================== */
if(isset($_POST['update_expense'])){
    $description = trim($_POST['description']);
    $total_amount = floatval($_POST['amount']);
    $category_id = intval($_POST['category_id']);
    $expense_date = $_POST['expense_date'];
    $selected_participants = $_POST['participants'] ?? [];
    $split_type = $_POST['split_type'];
    $custom_amounts = $_POST['custom_amounts'] ?? [];

    try {
        $conn->beginTransaction();

        // 1. Update Main Expense
        $stmt = $conn->prepare("UPDATE expenses SET description=?, amount=?, category_id=?, expense_date=? WHERE id=? AND user_id=?");
        $stmt->execute([$description, $total_amount, $category_id, $expense_date, $expense_id, $user_id]);

        // 2. Delete Old Shares
        $stmt = $conn->prepare("DELETE FROM expense_shares WHERE expense_id = ?");
        $stmt->execute([$expense_id]);

        // 3. Insert New Shares
        $share_stmt = $conn->prepare("INSERT INTO expense_shares (expense_id, user_id, people_id, amount_owed, status) VALUES (?, ?, ?, ?, 'Unpaid')");

        if($split_type === 'equal'){
            $total_people = count($selected_participants) + 1; 
            $split_amount = round($total_amount / $total_people, 2);
            foreach($selected_participants as $pid){
                $share_stmt->execute([$expense_id, $user_id, $pid, $split_amount]);
            }
        } else {
            foreach($selected_participants as $pid){
                $amt = floatval($custom_amounts[$pid] ?? 0);
                if($amt > 0) $share_stmt->execute([$expense_id, $user_id, $pid, $amt]);
            }
        }

        $conn->commit();
        header("Location: splits.php?msg=updated"); // Redirect back to list
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Error: " . $e->getMessage();
    }

    $logAction = $user["fullname"] . " Edited an Expense Share " . ucfirst($user["role"]);
    addLog($conn, $user["id"], $logAction);

}

/* =====================================================
    3. FETCH DROPDOWN DATA
===================================================== */
$stmt = $conn->prepare("SELECT id, name FROM people WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT id, category_name FROM category ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate "My Share" for initial custom view
$total_shares_sum = array_sum($shares);
$initial_my_share = $expense['amount'] - $total_shares_sum;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Split</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reusing your beautiful UI variables */
        :root { --primary: #6366f1; --bg: #f8fafc; --text-main: #1e293b; --border: #e2e8f0; --danger: #ef4444; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); padding: 40px; }
        .edit-card { background: white; padding: 32px; border-radius: 20px; max-width: 850px; margin: auto; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .input-box { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 10px; box-sizing: border-box; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #64748b; }
        .btn { padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 600; text-decoration: none; border:none; }
        .btn-main { background: var(--primary); color: white; }
        .friend-grid { display: grid; grid-template-columns: 1fr; gap: 8px; max-height: 250px; overflow-y: auto; border: 1px solid var(--border); padding: 10px; border-radius: 10px; }
        .friend-item { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; }
        .friend-item:has(input:checked) { border-color: var(--primary); background: #e0e7ff; }
        .split-toggle { display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px; margin-bottom: 20px; }
        .split-toggle label { flex: 1; text-align: center; padding: 8px; cursor: pointer; border-radius: 10px; margin: 0; }
        .split-toggle input { display: none; }
        .split-toggle label:has(input:checked) { background: white; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="edit-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h2>Edit Split Expense</h2>
        <a href="splits.php" style="color:#64748b; font-size:0.9rem;">&larr; Back to List</a>
    </div>

    <form method="POST">
        <label>Description</label>
        <input type="text" name="description" class="input-box" value="<?= htmlspecialchars($expense['description']) ?>" required>

        <div style="display:flex; gap:16px;">
            <div style="flex:1;">
                <label>Total Amount</label>
                <input type="number" step="0.01" name="amount" id="mainAmount" class="input-box" value="<?= $expense['amount'] ?>" oninput="validateTotal()" required>
            </div>
            <div style="flex:1;">
                <label>Date</label>
                <input type="date" name="expense_date" class="input-box" value="<?= $expense['expense_date'] ?>" required>
            </div>
        </div>

        <div style="display:flex; gap:16px;">
            <div style="flex:1;">
                <label>Category</label>
                <select name="category_id" class="input-box">
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($c['id'] == $expense['category_id']) ? 'selected' : '' ?>><?= $c['category_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;">
                <label>Split Type</label>
                <div class="split-toggle">
                    <label><input type="radio" name="split_type" value="equal" checked onclick="toggleView('equal')"> Equal</label>
                    <label><input type="radio" name="split_type" value="custom" onclick="toggleView('custom')"> Custom</label>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 32px;">
            <div style="flex: 1;">
                <label>Who's involved?</label>
                <div class="friend-grid">
                    <?php foreach($friends as $f): 
                        $isChecked = isset($shares[$f['id']]) ? 'checked' : '';
                    ?>
                        <label class="friend-item">
                            <input type="checkbox" name="participants[]" value="<?= $f['id'] ?>" 
                                   data-name="<?= htmlspecialchars($f['name']) ?>" 
                                   <?= $isChecked ?> onchange="updateCustomList()">
                            <?= htmlspecialchars($f['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="customSide" style="flex: 1; border-left: 1px solid var(--border); padding-left: 20px;">
                <label>Individual Shares</label>
                <div id="customInputsContainer"></div>
                <div id="totalStatus" style="padding:10px; border-radius:8px; margin-top:10px; font-weight:700; font-size:0.8rem;"></div>
            </div>
        </div>

        <div style="margin-top: 32px; text-align: right;">
            <button type="submit" name="update_expense" id="submitBtn" class="btn btn-main">Update Split</button>
        </div>
    </form>
</div>

<script>
    // Initial data from PHP
    const initialShares = <?= json_encode($shares) ?>;
    const initialMyShare = <?= $initial_my_share ?>;

    function toggleView(mode) {
        document.getElementById('customSide').style.opacity = (mode === 'equal') ? '0.3' : '1';
        document.getElementById('customSide').style.pointerEvents = (mode === 'equal') ? 'none' : 'auto';
        validateTotal();
    }

    function updateCustomList() {
        const container = document.getElementById('customInputsContainer');
        const checked = document.querySelectorAll('input[name="participants[]"]:checked');
        
        container.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-size:0.85rem; font-weight:700;">Me (You)</span>
                <input type="number" step="0.01" name="my_share" class="input-box custom-val" style="width:110px; margin-bottom:0;" 
                       oninput="validateTotal()" value="${initialMyShare}">
            </div>
        `;

        checked.forEach(cb => {
            const friendId = cb.value;
            const friendName = cb.getAttribute('data-name');
            const amt = initialShares[friendId] || '';
            
            const div = document.createElement('div');
            div.style.marginBottom = '12px';
            div.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:0.85rem;">${friendName}</span>
                    <input type="number" step="0.01" name="custom_amounts[${friendId}]" class="input-box custom-val" 
                           style="width:110px; margin-bottom:0;" oninput="validateTotal()" value="${amt}" required>
                </div>
            `;
            container.appendChild(div);
        });
        validateTotal();
    }

    function validateTotal() {
        const splitType = document.querySelector('input[name="split_type"]:checked').value;
        const submitBtn = document.getElementById('submitBtn');
        const status = document.getElementById('totalStatus');

        if(splitType === 'equal') {
            status.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            return;
        }

        status.style.display = 'block';
        const totalExpected = parseFloat(document.getElementById('mainAmount').value) || 0;
        let currentTotal = 0;
        document.querySelectorAll('.custom-val').forEach(i => currentTotal += (parseFloat(i.value) || 0));

        const diff = (totalExpected - currentTotal).toFixed(2);

        if (Math.abs(diff) == 0 && totalExpected > 0) {
            status.innerHTML = "✓ Totals Match";
            status.style.background = "#dcfce7"; status.style.color = "#166534";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
        } else {
            status.innerHTML = diff > 0 ? `Remaining: ₱${diff}` : `Over: ₱${Math.abs(diff)}`;
            status.style.background = "#fee2e2"; status.style.color = "#991b1b";
            submitBtn.disabled = true;
            submitBtn.style.opacity = "0.5";
        }
    }

    // Run on load
    updateCustomList();
</script>
</body>
</html>