<?php
require_once "db.php";

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$message = "";

/* =====================================================
    1. HANDLE DELETE ACTION
    This block executes when you click the 🗑 button
===================================================== */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $conn->beginTransaction();
        
        // 1. Delete associated shares first
        $stmt1 = $conn->prepare("DELETE FROM expense_shares WHERE expense_id = ?");
        $stmt1->execute([$delete_id]);
        
        // 2. Delete the main expense record (ensure it belongs to the logged-in user)
        $stmt2 = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
        $stmt2->execute([$delete_id, $user_id]);
        
        $conn->commit();
        
       
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Error deleting: " . $e->getMessage();
    }
}

// Success message from redirect
if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message = "Expense successfully deleted.";
}

/* =====================================================
    2. FETCH DATA FOR UI (Remaining code stays same)
===================================================== */
$budgetStmt = $conn->prepare("SELECT id FROM budget WHERE user_id = ? AND status = 'Active' AND CURDATE() BETWEEN start_date AND end_date LIMIT 1");
$budgetStmt->execute([$user_id]);
$activeBudget = $budgetStmt->fetch(PDO::FETCH_ASSOC);
$current_budget_id = $activeBudget['id'] ?? null;

$userStmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$me = $userStmt->fetch(PDO::FETCH_ASSOC);
$my_name = $me['fullname'] ?? "Me (You)";

/* =====================================================
    3. HANDLE SAVE (CREATE & UPDATE)
===================================================== */
if(isset($_POST['save_expense'])){
    $expense_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : null;
    $description = trim($_POST['description']);
    $total_amount = floatval($_POST['amount']);
    $payment_method_id = intval($_POST['payment_method_id']);
    $category_id = intval($_POST['category_id']);
    $expense_date = $_POST['expense_date'];
    $selected_participants = $_POST['participants'] ?? [];
    $split_type = $_POST['split_type'] ?? 'equal';
    $custom_amounts = $_POST['custom_amounts'] ?? [];

    if(!$current_budget_id){
        $message = "Error: No active budget found.";
    } else {
        try {
            $conn->beginTransaction();
            if($expense_id) {
                $stmt = $conn->prepare("UPDATE expenses SET category_id=?, description=?, amount=?, payment_method_id=?, expense_date=? WHERE id=? AND user_id=?");
                $stmt->execute([$category_id, $description, $total_amount, $payment_method_id, $expense_date, $expense_id, $user_id]);
                $conn->prepare("DELETE FROM expense_shares WHERE expense_id = ?")->execute([$expense_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO expenses (user_id, budget_id, category_id, description, amount, payment_method_id, expense_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $current_budget_id, $category_id, $description, $total_amount, $payment_method_id, $expense_date]);
                $expense_id = $conn->lastInsertId();
            }
            $share_stmt = $conn->prepare("INSERT INTO expense_shares (expense_id, user_id, people_id, amount_owed, status) VALUES (?, ?, ?, ?, 'Unpaid')");
            if($split_type === 'equal'){
                $total_people = count($selected_participants) + 1; 
                $split_amount = round($total_amount / $total_people, 2);
                foreach($selected_participants as $pid){ $share_stmt->execute([$expense_id, $user_id, $pid, $split_amount]); }
            } else {
                foreach($selected_participants as $pid){
                    $amt = floatval($custom_amounts[$pid] ?? 0);
                    if($amt > 0) $share_stmt->execute([$expense_id, $user_id, $pid, $amt]);
                }
            }
            $conn->commit();
            $message = "Record saved successfully!";
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch list for table
$stmt = $conn->prepare("SELECT e.*, c.category_name FROM expenses e JOIN category c ON c.id = e.category_id WHERE e.user_id = ? AND EXISTS (SELECT 1 FROM expense_shares es WHERE es.expense_id = e.id) ORDER BY e.expense_date DESC");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories and friends for modal
$stmt = $conn->prepare("SELECT id, name FROM people WHERE user_id = ? ORDER BY name ASC"); $stmt->execute([$user_id]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->query("SELECT id, category_name FROM category ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Splits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --primary-light: #e0e7ff; --bg: #f8fafc; --text-main: #1e293b; --text-muted: #64748b; --border: #e2e8f0; --danger: #ef4444; --success: #22c55e; }
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
        .container { width:100%; padding: 0 24px; box-sizing: border-box;}
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .card { background: white; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 14px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; }
        td { padding: 18px 20px; border-bottom: 1px solid var(--border); }
        .btn { padding: 10px 16px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: 0.2s; border: none; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-main { background: var(--primary); color: white; }
        .btn-icon { background: transparent; border: 1px solid var(--border); padding: 8px; border-radius: 8px; color: var(--text-main); }
        .btn-icon:hover { background: #f1f5f9; }
        .btn-delete:hover { color: var(--danger); border-color: #fecaca; background: #fee2e2; }

        #modalOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); justify-content:center; align-items:center; z-index: 9999; }
        .modal-card { background: white; padding: 32px; border-radius: 20px; width: 100%; max-width: 480px; transition: max-width 0.4s ease; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .modal-card h2{margin-bottom: 10px;}
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text-muted); }
        .input-box { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 10px; font-family: inherit; box-sizing: border-box; }
        .friend-grid { display: grid; grid-template-columns: 1fr; gap: 8px; max-height: 200px; overflow-y: auto; }
        .friend-item { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; font-size: 0.9rem; }
        .friend-item:has(input:checked) { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
        .split-toggle { display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px; margin-bottom: 20px; }
        .split-toggle label { flex: 1; text-align: center; padding: 8px; cursor: pointer; border-radius: 10px; }
        .split-toggle input { display: none; }
        .split-toggle label:has(input:checked) { background: white; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 6px; margin-top: 10px; display: inline-block; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>Split Expenses</h1>
        <button class="btn btn-main" onclick="openNewModal()">+ Add New Split</button>
    </div>

    <?php if($message): ?>
        <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:24px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr><th>Description</th><th>Category</th><th>Total</th><th>Date</th><th style="text-align:right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($expenses as $e): 
                    $sStmt = $conn->prepare("SELECT people_id, amount_owed FROM expense_shares WHERE expense_id = ?");
                    $sStmt->execute([$e['id']]);
                    $shares = $sStmt->fetchAll(PDO::FETCH_KEY_PAIR);
                ?>
                <tr>
                    <td style="font-weight:600;"><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= htmlspecialchars($e['category_name']) ?></td>
                    <td style="font-weight:700;">₱<?= number_format($e['amount'], 2) ?></td>
                    <td><?= date("M d, Y", strtotime($e['expense_date'])) ?></td>
                    <td style="text-align:right; display:flex; justify-content:flex-end; gap:8px;">
                        <a href="spender.php?page=view_split_expense&expense_id=<?= $e['id'] ?>" class="btn btn-icon">👁</a>
                        <button class="btn btn-icon" onclick='openEditModal(<?= json_encode($e) ?>, <?= json_encode($shares) ?>)'>✏️</button>
                        
                        <a href="spender.php?page=split_expense&delete_id=<?= $e['id'] ?>" 
                           class="btn btn-icon btn-delete" 
                           onclick="return confirm('Delete this split?')">🗑</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalOverlay">
    <div class="modal-card" id="modalCard">
        <h2 id="modalTitle">Create Split Expense</h2>
        <form method="POST" id="splitForm">
            <input type="hidden" name="edit_id" id="edit_id">
            <label>Description</label>
            <input type="text" name="description" class="input-box" required>
            <div style="display:flex; gap:16px;">
                <div style="flex:1;"><label>Total Amount</label><input type="number" step="0.01" name="amount" id="mainAmount" class="input-box" oninput="validateTotal()" required></div>
                <div style="flex:1;"><label>Date</label><input type="date" name="expense_date" class="input-box" value="<?= date('Y-m-d') ?>" required></div>
            </div>
            <div style="display:flex; gap:16px;">
                <div style="flex:1;"><label>Category</label><select name="category_id" class="input-box"><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= $c['category_name'] ?></option><?php endforeach; ?></select></div>
                <div style="flex:1;"><label>Payment</label><select name="payment_method_id" class="input-box"><option value="1">Cash</option><option value="4">GCash</option><option value="6">Bank Transfer</option></select></div>
            </div>
            <label>Split Type</label>
            <div class="split-toggle">
                <label><input type="radio" name="split_type" value="equal" checked onclick="setSplitMode('equal')"> Equally</label>
                <label><input type="radio" name="split_type" value="custom" onclick="setSplitMode('custom')"> Custom</label>
            </div>
            <div style="display: flex; gap: 24px;">
                <div style="flex: 1;"><label>Who's involved?</label><div class="friend-grid"><?php foreach($friends as $f): ?><label class="friend-item"><input type="checkbox" name="participants[]" value="<?= $f['id'] ?>" data-name="<?= htmlspecialchars($f['name']) ?>" onchange="updateCustomList()"><?= htmlspecialchars($f['name']) ?></label><?php endforeach; ?></div></div>
                <div id="customSide" style="flex: 1; display: none; border-left: 1px solid var(--border); padding-left: 20px;"><label>Individual Shares</label><div id="customInputsContainer"></div><div id="totalStatus" class="status-badge"></div></div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;"><button type="button" onclick="closeModal()" class="btn">Cancel</button><button type="submit" name="save_expense" id="submitBtn" class="btn btn-main">Save Split</button></div>
        </form>
    </div>
</div>

<script>
    const myName = "<?= $my_name ?>";
    function openModal(){ document.getElementById('modalOverlay').style.display='flex'; }
    function closeModal(){ document.getElementById('modalOverlay').style.display='none'; }
    function openNewModal() { document.getElementById('splitForm').reset(); document.getElementById('edit_id').value = ""; document.getElementById('modalTitle').innerText = "Create Split Expense"; setSplitMode('equal'); openModal(); }
    function openEditModal(data, shares) {
        document.getElementById('splitForm').reset();
        document.getElementById('edit_id').value = data.id;
        document.getElementById('modalTitle').innerText = "Edit Split Expense";
        document.querySelector('input[name="description"]').value = data.description;
        document.querySelector('input[name="amount"]').value = data.amount;
        document.querySelector('input[name="expense_date"]').value = data.expense_date;
        document.querySelector('select[name="category_id"]').value = data.category_id;
        document.querySelector('select[name="payment_method_id"]').value = data.payment_method_id;
        const pIds = Object.keys(shares);
        document.querySelectorAll('input[name="participants[]"]').forEach(cb => { cb.checked = pIds.includes(cb.value); });
        setSplitMode('custom'); document.querySelector('input[value="custom"]').checked = true;
        openModal();
        setTimeout(() => {
            let friendTotal = 0;
            for (const [id, amt] of Object.entries(shares)) {
                const input = document.querySelector(`input[name="custom_amounts[${id}]"]`);
                if(input) { input.value = amt; friendTotal += parseFloat(amt); }
            }
            document.querySelector('input[name="my_share"]').value = (data.amount - friendTotal).toFixed(2);
            validateTotal();
        }, 50);
    }
    function setSplitMode(mode) { const card = document.getElementById('modalCard'); const side = document.getElementById('customSide'); if(mode === 'custom') { card.style.maxWidth = '850px'; side.style.display = 'block'; updateCustomList(); } else { card.style.maxWidth = '480px'; side.style.display = 'none'; } }
    function updateCustomList() {
        const container = document.getElementById('customInputsContainer');
        const checked = document.querySelectorAll('input[name="participants[]"]:checked');
        const myVal = document.querySelector('input[name="my_share"]')?.value || "";
        container.innerHTML = `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;"><span style="font-size:0.85rem; font-weight:700;">${myName}</span><input type="number" step="0.01" name="my_share" class="input-box custom-val" style="width:110px; margin-bottom:0;" oninput="validateTotal()" value="${myVal}"></div>`;
        checked.forEach(cb => { container.innerHTML += `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;"><span style="font-size:0.85rem;">${cb.getAttribute('data-name')}</span><input type="number" step="0.01" name="custom_amounts[${cb.value}]" class="input-box custom-val" style="width:110px; margin-bottom:0;" oninput="validateTotal()"></div>`; });
        validateTotal();
    }
    function validateTotal() {
        if(document.querySelector('input[name="split_type"]:checked').value !== 'custom') return;
        const total = parseFloat(document.getElementById('mainAmount').value) || 0;
        let sum = 0; document.querySelectorAll('.custom-val').forEach(i => sum += (parseFloat(i.value) || 0));
        const diff = (total - sum).toFixed(2);
        const status = document.getElementById('totalStatus'); const btn = document.getElementById('submitBtn');
        if(Math.abs(diff) == 0 && total > 0) { status.innerHTML = "✓ Match"; status.style.background = "#dcfce7"; status.style.color = "var(--success)"; btn.disabled = false; btn.style.opacity = "1"; }
        else { status.innerHTML = diff > 0 ? `₱${diff} left` : `Over ₱${Math.abs(diff)}`; status.style.background = "#fee2e2"; status.style.color = "var(--danger)"; btn.disabled = true; btn.style.opacity = "0.5"; }
    }
    window.onclick = function(e){ if(e.target == document.getElementById('modalOverlay')) closeModal(); }
</script>
</body>
</html>