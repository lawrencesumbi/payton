<?php
require_once "db.php";

// Ensure session is started to read Toast messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];

/* =====================================================
    1. FETCH DATA FOR UI
===================================================== */
// Get Active Budget
$budgetStmt = $conn->prepare("SELECT id FROM budget WHERE user_id = ? AND status = 'Active' AND CURDATE() BETWEEN start_date AND end_date LIMIT 1");
$budgetStmt->execute([$user_id]);
$activeBudget = $budgetStmt->fetch(PDO::FETCH_ASSOC);
$current_budget_id = $activeBudget['id'] ?? null;

// Get User Info
$userStmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$me = $userStmt->fetch(PDO::FETCH_ASSOC);
$my_name = $me['fullname'] ?? "Me (You)";

// Get search term from URL
$searchTerm = $_GET['search'] ?? '';

// Fetch list for table
$query = "SELECT e.*, c.category_name FROM expenses e JOIN category c ON c.id = e.category_id WHERE e.user_id = ? AND EXISTS (SELECT 1 FROM expense_shares es WHERE es.expense_id = e.id)";

if (!empty($searchTerm)) {
    $query .= " AND (e.description LIKE ? OR c.category_name LIKE ?)";
}

$query .= " ORDER BY e.expense_date DESC";

$stmt = $conn->prepare($query);
$params = [$user_id];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories and friends for modal
$stmt = $conn->prepare("SELECT id, name FROM people WHERE user_id = ? ORDER BY name ASC"); 
$stmt->execute([$user_id]);
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
        :root { 
            --primary: #6366f1; 
            --primary-light: #e0e7ff; 
            --bg: #f8fafc; 
            --bg-card: #ffffff;
            --text-main: #1e293b; 
            --text-muted: #64748b; 
            --border: #e2e8f0; 
            --border-light: #f1f5f9;
            --danger: #ef4444; 
            --danger-light: #fee2e2;
            --success: #22c55e; 
            --shadow: rgba(0,0,0,0.05);
            --accent-purple: #7c3aed;
            --accent-purple-dark: #8c3bf6;
        }

        [data-theme="dark"] {
            --bg: #12141a;
            --bg-card: #191c24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #2a2e39;
            --border-light: #374151;
            --danger: #ef4444;
            --danger-light: #451a1a;
            --success: #22c55e;
            --shadow: rgba(0,0,0,0.2);
            --accent-purple: #a855f7;
            --accent-purple-dark: #a855f7;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); margin: 0; transition: background 0.3s ease; }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            -ms-overflow-style: none;  
            scrollbar-width: none;  
        }

        html::-webkit-scrollbar, body::-webkit-scrollbar {
            display: none;
            width: 0 !important;
            height: 0 !important;
        }

        .container { width:100%; padding: 0 24px; box-sizing: border-box;}
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; padding-top: 20px; }
        .card { background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px var(--shadow); overflow: hidden; transition: background 0.3s ease; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--accent-purple-dark); padding: 14px 20px; text-align: left; font-size: 14px; text-transform: uppercase; color: white; font-weight: 700; }
        td { padding: 18px 20px; border-bottom: 1px solid var(--border); color: var(--text-main); }
        .btn { padding: 10px 16px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: 0.2s; border: none; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-main { background: var(--accent-purple); color: white; }
        .btn-icon { background: transparent; border: 1px solid var(--border); padding: 8px; border-radius: 8px; color: var(--text-main); }
        .btn-icon:hover { background: var(--border-light); }
        .btn-delete:hover { color: var(--danger); border-color: var(--danger-light); background: var(--danger-light); }

        #modalOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); justify-content:center; align-items:center; z-index: 9999; }
        .modal-card { background: var(--bg-card); padding: 32px; border-radius: 20px; width: 100%; max-width: 480px; transition: max-width 0.4s ease; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); transition: background 0.3s ease; }
        .modal-card h2{margin-bottom: 10px;}
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text-muted); }
        .input-box { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid var(--border); border-radius: 10px; font-family: inherit; box-sizing: border-box; background: var(--bg-card); color: var(--text-main); transition: border-color 0.2s; }
        .input-box:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        .friend-grid { display: grid; grid-template-columns: 1fr; gap: 8px; max-height: 200px; overflow-y: auto; }
        .friend-item { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; font-size: 0.9rem; background: var(--bg-card); color: var(--text-main); transition: all 0.2s; }
        .friend-item:has(input:checked) { border-color: var(--primary); background: var(--primary-light); color: var(--primary); }
        .friend-item:hover { background: var(--hover-bg); }
        .split-toggle { display: flex; background: var(--bg-card); padding: 4px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border); }
        .split-toggle label { flex: 1; text-align: center; padding: 8px; cursor: pointer; border-radius: 10px; color: var(--text-muted); transition: all 0.2s; }
        .split-toggle input { display: none; }
        .split-toggle label:has(input:checked) { background: var(--primary); color: white; }
        .status-badge { font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 6px; margin-top: 10px; display: inline-block; }

        /* TOAST STYLES */
        .toast-container { position:fixed; top:20px; right:20px; z-index:10000; }
        .custom-toast { 
            display:flex; align-items:flex-start; gap:10px; background: var(--bg-card); 
            padding:15px; border-radius:10px; margin-bottom:10px; min-width:280px; 
            box-shadow:0 10px 25px rgba(0,0,0,0.1); animation: slideIn 0.3s ease;
            transition: opacity 0.3s ease;
        }
        @keyframes slideIn { from{ transform: translateX(100%); opacity:0; } to{ transform: translateX(0); opacity:1; } }
        .toast-success { border-left:5px solid #22c55e; }
        .toast-error { border-left:5px solid #ef4444; }
        .toast-title { font-weight:700; margin-bottom: 2px; }
        .toast-message { font-size:14px; color:#555; }
        .toast-close { margin-left:auto; cursor:pointer; border:none; background:none; font-size:16px; color: #999; }
    </style>
</head>
<body>

<div class="toast-container">
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="custom-toast toast-success">
            <div>
                <div class="toast-title">SUCCESS!</div>
                <div class="toast-message"><?= $_SESSION['success_msg'] ?></div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">✖</button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="custom-toast toast-error">
            <div>
                <div class="toast-title">ERROR</div>
                <div class="toast-message"><?= $_SESSION['error_msg'] ?></div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">✖</button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
</div>

<div class="container">
    <div class="page-header">
        <h1>Split Expenses</h1>
        <button class="btn btn-main" onclick="openNewModal()">+ Add New Split</button>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($expenses)): ?>
                    <?php 
                        $count = 1; // 1. Initialize the counter 
                        foreach($expenses as $e): 
                            $sStmt = $conn->prepare("SELECT people_id, amount_owed FROM expense_shares WHERE expense_id = ?");
                            $sStmt->execute([$e['id']]);
                            $shares = $sStmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    ?>
                    <tr>
                        <td style="color: var(--text-muted); font-size: 0.9rem;"><?= $count++ ?>.</td>
                        
                        <td style="font-weight:600;"><?= htmlspecialchars($e['description']) ?></td>
                        <td><?= htmlspecialchars($e['category_name']) ?></td>
                        <td style="font-weight:700;">₱<?= number_format($e['amount'], 2) ?></td>
                        <td><?= date("M d, Y", strtotime($e['expense_date'])) ?></td>
                        <td style="text-align:right; display:flex; justify-content:flex-end; gap:8px;">
                            <a href="spender.php?page=view_split_expense&expense_id=<?= $e['id'] ?>" class="btn btn-icon">👁</a>
                            <button class="btn btn-icon" onclick='openEditModal(<?= json_encode($e) ?>, <?= json_encode($shares) ?>)'>✏️</button>
                            
                            <a href="process_split.php?delete_id=<?= $e['id'] ?>" 
                            class="btn btn-icon btn-delete" 
                            onclick="return confirm('Delete this split?')">🗑</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:gray">
                                No split expenses found. Click the button to add one.
                            </td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalOverlay">
    <div class="modal-card" id="modalCard">
        <h2 id="modalTitle">Create Split Expense</h2>
        <form method="POST" id="splitForm" action="process_split.php">
            <input type="hidden" name="edit_id" id="edit_id">
            <input type="hidden" name="budget_id" value="<?= $current_budget_id ?>">
            
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
                <div style="flex: 1;">
                    <label>Who's involved?</label>
                    <div class="friend-grid">
                        <?php foreach($friends as $f): ?>
                            <label class="friend-item">
                                <input type="checkbox" name="participants[]" value="<?= $f['id'] ?>" data-name="<?= htmlspecialchars($f['name']) ?>" onchange="updateCustomList()">
                                <?= htmlspecialchars($f['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="customSide" style="flex: 1; display: none; border-left: 1px solid var(--border); padding-left: 20px;">
                    <label>Individual Shares</label>
                    <div id="customInputsContainer"></div>
                    <div id="totalStatus" class="status-badge"></div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button" onclick="closeModal()" class="btn">Cancel</button>
                <button type="submit" name="save_expense" id="submitBtn" class="btn btn-main">Save Split</button>
            </div>
        </form>
    </div>
</div>

<script>
    const myName = "<?= $my_name ?>";
    
    // Auto-hide toast logic
    setTimeout(() => {
        document.querySelectorAll('.custom-toast').forEach(t => {
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 300);
        });
    }, 4000);

    function openModal(){ document.getElementById('modalOverlay').style.display='flex'; }
    function closeModal(){ document.getElementById('modalOverlay').style.display='none'; }
    
    function openNewModal() { 
        document.getElementById('splitForm').reset(); 
        document.getElementById('edit_id').value = ""; 
        document.getElementById('modalTitle').innerText = "Create Split Expense"; 
        setSplitMode('equal'); 
        openModal(); 
    }
    
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
        document.querySelectorAll('input[name="participants[]"]').forEach(cb => { 
            cb.checked = pIds.includes(cb.value); 
        });
        
        setSplitMode('custom'); 
        document.querySelector('input[value="custom"]').checked = true;
        openModal();
        
        setTimeout(() => {
            let friendTotal = 0;
            for (const [id, amt] of Object.entries(shares)) {
                const input = document.querySelector(`input[name="custom_amounts[${id}]"]`);
                if(input) { input.value = amt; friendTotal += parseFloat(amt); }
            }
            const myShareInput = document.querySelector('input[name="my_share"]');
            if(myShareInput) myShareInput.value = (data.amount - friendTotal).toFixed(2);
            validateTotal();
        }, 50);
    }

    function setSplitMode(mode) { 
        const card = document.getElementById('modalCard'); 
        const side = document.getElementById('customSide'); 
        if(mode === 'custom') { 
            card.style.maxWidth = '850px'; 
            side.style.display = 'block'; 
            updateCustomList(); 
        } else { 
            card.style.maxWidth = '480px'; 
            side.style.display = 'none'; 
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').style.opacity = "1";
        } 
    }

    function updateCustomList() {
        const container = document.getElementById('customInputsContainer');
        const checked = document.querySelectorAll('input[name="participants[]"]:checked');
        const myVal = document.querySelector('input[name="my_share"]')?.value || "";
        
        container.innerHTML = `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;"><span style="font-size:0.85rem; font-weight:700;">${myName}</span><input type="number" step="0.01" name="my_share" class="input-box custom-val" style="width:110px; margin-bottom:0;" oninput="validateTotal()" value="${myVal}"></div>`;
        
        checked.forEach(cb => { 
            container.innerHTML += `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;"><span style="font-size:0.85rem;">${cb.getAttribute('data-name')}</span><input type="number" step="0.01" name="custom_amounts[${cb.value}]" class="input-box custom-val" style="width:110px; margin-bottom:0;" oninput="validateTotal()"></div>`; 
        });
        validateTotal();
    }

    function validateTotal() {
        if(document.querySelector('input[name="split_type"]:checked').value !== 'custom') return;
        const total = parseFloat(document.getElementById('mainAmount').value) || 0;
        let sum = 0; 
        document.querySelectorAll('.custom-val').forEach(i => sum += (parseFloat(i.value) || 0));
        
        const diff = (total - sum).toFixed(2);
        const status = document.getElementById('totalStatus'); 
        const btn = document.getElementById('submitBtn');
        
        if(Math.abs(diff) == 0 && total > 0) { 
            status.innerHTML = "✓ Match"; 
            status.style.background = "#dcfce7"; 
            status.style.color = "var(--success)"; 
            btn.disabled = false; 
            btn.style.opacity = "1"; 
        } else { 
            status.innerHTML = diff > 0 ? `₱${diff} left` : `Over ₱${Math.abs(diff)}`; 
            status.style.background = "#fee2e2"; 
            status.style.color = "var(--danger)"; 
            btn.disabled = true; 
            btn.style.opacity = "0.5"; 
        }
    }

    window.onclick = function(e){ if(e.target == document.getElementById('modalOverlay')) closeModal(); }
</script>
</body>
</html>