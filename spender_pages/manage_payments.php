<?php
// Start session at the very top to ensure toasts work
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

// use logged-in user
$user_id = $_SESSION['user_id'] ?? 1;

/* ================= FETCH PAYMENTS WITH JOINS ================= */
$stmt = $pdo->prepare("
    SELECT 
        sp.id,
        sp.payment_name,
        sp.amount,
        sp.due_date,
        sp.paid_date,
        pm.payment_method_name AS payment_method,
        ds.due_status_name AS status
    FROM scheduled_payments sp
    LEFT JOIN payment_method pm ON sp.payment_method_id = pm.id
    LEFT JOIN due_status ds ON sp.due_status_id = ds.id
    WHERE sp.user_id = ?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= ANALYTICS LOGIC ================= */
$totalCount = count($payments);
$totalPaid = 0; $totalUnpaid = 0; $totalOverdue = 0;

foreach ($payments as $p) {
    $status = strtolower($p['status'] ?? '');
    if ($status === 'paid') $totalPaid += $p['amount'];
    elseif ($status === 'unpaid') $totalUnpaid += $p['amount'];
    elseif ($status === 'overdue') $totalOverdue += $p['amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Payments</title>

<style>
body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; }


/* --- ANALYTICS --- */
.analytics-row { display: flex; gap: 15px; margin: 20px auto; width: 96%; }
.stat-card {
    flex: 1; background: #ffffff; padding: 18px; border-radius: 12px; border: 1px solid #eef1f6;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05); position: relative; overflow: hidden;
    display: flex; align-items: center; justify-content: space-between;
}
.stat-card::before { content: ""; position: absolute; top: 0; left: 0; height: 100%; width: 4px; }
.stat-blue::before { background: #2f7cff; }
.stat-purple::before { background: #2a7a31; }
.stat-orange::before { background: #f8bf5c; }
.stat-red::before { background: #ef4444; }
.stat-label { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 17px; font-weight: 900; color: #0f172a; margin-top: 4px; }

/* --- SCROLLABLE TABLE CONTAINER --- */
.container { 
    width: 96%; 
    margin: 0 auto; 
    background: white; 
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    overflow: hidden; /* Clips the internal scroll box border-radius */
}

.table-wrapper {
    max-height: 500px; /* Adjust this height as needed */
    overflow-y: auto;
    width: 100%;
}

/* Custom Scrollbar for the right border */
.table-wrapper::-webkit-scrollbar { width: 8px; }
.table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; }
.table-wrapper::-webkit-scrollbar-thumb { background: #838383; border-radius: 4px; }
.table-wrapper::-webkit-scrollbar-thumb:hover { background: #8c3bf6; }

table { width: 100%; border-collapse: collapse; }
th { 
    background: #8c3bf6; color: white; padding: 12px; 
    position: sticky; top: 0; z-index: 10; /* Keeps header visible while scrolling */
}
td { padding: 12px; border-bottom: .5px solid #ddd; text-align: center; }

.paid { color: green; font-weight: bold; }
.unpaid { color: orange; font-weight: bold; }
.overdue { color: red; font-weight: bold; }

/* --- BUTTONS & ACTIONS --- */
.actions { display: flex; justify-content: center; gap: 8px; }
.btn-edit, .btn-delete { padding: 6px 10px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; height: 34px; }
.btn-edit { background: #f3eaff; color: #7210c8; }
.btn-delete { background: #ffecec; color: #a30000; }
.fab { position: fixed; bottom: 30px; right: 30px; background: #8c3bf6; color: white; font-size: 26px; border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }

/* --- MODAL --- */
.modal { display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); }
.modal-content { background: transparent; width: 90%; max-width: 950px; margin: 8vh auto; display: flex; gap: 20px; justify-content: center; }

@keyframes slideInUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-left { 
    width: 500px; flex-shrink: 0; padding: 40px; background: #ffffff; border-radius: 20px; 
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideInUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; 
}
.modal-right { 
    width: 380px; flex-shrink: 0; background: #fcfaff; border-radius: 20px; padding: 40px; display: none; 
    flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideInUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; 
}

.title-payment h3 { margin: 0; color: #1e293b; font-size: 24px; font-weight: 800; }
.form-subtitle { color: #94a3b8; font-size: 13px; margin-bottom: 25px; margin-top: 5px; }
.modal label { display: block; font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
.modal input, .modal select { width: 100%; padding: 12px 16px; margin-bottom: 20px; border: 2px solid #f1f5f9; border-radius: 12px; box-sizing: border-box; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
.save { background: #8c3bf6; color: white; padding: 12px 28px; border-radius: 10px; border:none; cursor: pointer; font-weight: 700; }
.mark-paid { background: #7c3aed; color: white; padding: 12px 28px; border-radius: 10px; border:none; cursor: pointer; font-weight: 700; }
.cancel { background: #f1f5f9; color: #64748b; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 700; border: none !important; }

.summary-card { width: 100%; background: white; border: 1px solid #e2e8f0; border-radius: 15px; padding: 20px; margin-top: 15px; }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
.total-divider { border-top: 1px dashed #cbd5e1; margin: 10px 0; padding-top: 10px; }

/* --- TOASTS --- */
.toast-container { position: fixed; top: 20px; right: 20px; z-index: 10001; display: flex; flex-direction: column; gap: 10px; }
.custom-toast { display: flex; align-items: center; background: #fff; width: 350px; padding: 15px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); animation: toastSlideIn 0.4s ease forwards; border-left: 5px solid #62C976; }
@keyframes toastSlideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes toastSlideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }
.slide-out { animation: toastSlideOut 0.5s ease forwards; }
</style>
</head>

<body>

<div class="toast-container" id="toastContainer">
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="custom-toast" id="activeToast">
            <div style="flex: 1;">
                <div style="font-weight: 800; color: #62C976;">SUCCESS</div>
                <div style="color: #666; font-size: 13px;"><?= $_SESSION['success_msg'] ?></div>
            </div>
            <button onclick="dismissToast()" style="border:none; background:none; cursor:pointer;">✕</button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>
</div>

<div class="analytics-row">
    <div class="stat-card stat-blue"><div><div class="stat-label">Total Payments</div><div class="stat-value"><?= $totalCount ?></div></div><div class="stat-icon">📑</div></div>
    <div class="stat-card stat-purple"><div><div class="stat-label">Total Paid</div><div class="stat-value">₱<?= number_format($totalPaid, 2) ?></div></div><div class="stat-icon">✅</div></div>
    <div class="stat-card stat-orange"><div><div class="stat-label">Total Unpaid</div><div class="stat-value">₱<?= number_format($totalUnpaid, 2) ?></div></div><div class="stat-icon">⌛</div></div>
    <div class="stat-card stat-red"><div><div class="stat-label">Total Overdue</div><div class="stat-value">₱<?= number_format($totalOverdue, 2) ?></div></div><div class="stat-icon">⚠️</div></div>
</div>

<div class="container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>No.</th><th>Payment Name</th><th>Amount</th><th>Due Date</th><th>Paid Date</th><th>Method</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php $no = 1; foreach ($payments as $p): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($p['payment_name']) ?></td>
                <td>₱<?= number_format($p['amount'], 2) ?></td>
                <td><?= $p['due_date'] ?></td>
                <td><?= $p['paid_date'] ?? '-' ?></td>
                <td><?= $p['payment_method'] ?? '-' ?></td>
                <td class="<?= strtolower($p['status'] ?? '') ?>"><?= ucfirst($p['status'] ?? '') ?></td>
                <td class="actions"> 
                    <a href="javascript:void(0);" class="btn-edit" onclick="openEditModal(<?= $p['id'] ?>)">✏️ Edit</a>
                    <a href="delete_payment.php?id=<?= $p['id'] ?>" class="btn-delete" onclick="return confirm('Delete?');">🗑 Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<button class="fab" onclick="openAddModal()">+</button>

<div class="modal" id="addPaymentModal">
    <div class="modal-content">
        <div class="modal-left" id="modalLeftContainer">
            <div class="title-payment"><h3 id="modalTitle">Add Payment</h3></div>
            <p class="form-subtitle">Complete the fields below to manage your transaction.</p>
            <form method="POST" action="save_payment.php" id="paymentForm">
                <input type="hidden" name="id" id="payment_id">
                <input type="hidden" name="mode" id="form_mode">
                <label>Payment Reference</label>
                <input type="text" name="payment_name" id="payment_name" required>
                <label>Transaction Amount (PHP)</label>
                <input type="number" step="0.01" name="amount" id="amount" required>
                <label>Schedule Date</label>
                <input type="date" name="due_date" id="due_date" required>
                <div id="editFields">
                    <label>Completion Date</label>
                    <input type="date" name="paid_date" id="paid_date">
                    <label>Payment Channel</label>
                    <select name="payment_method_id" id="payment_method">
                        <?php
                        $methods = $pdo->query("SELECT id, payment_method_name FROM payment_method")->fetchAll();
                        foreach ($methods as $m) { echo "<option value='{$m['id']}'>{$m['payment_method_name']}</option>"; }
                        ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="save" id="saveBtn">Save Entry</button>
                    <button type="button" class="mark-paid" id="markPaidBtn" style="display:none" onclick="showCongrats()">Mark as Paid</button>
                    <button type="button" class="cancel" onclick="closeAddModal()">Dismiss</button>
                </div>
            </form>
        </div>
        <div class="modal-right" id="congratsPanel">
            <div style="font-size:50px; margin-bottom:10px;">🎉</div>
            <h3 style="margin:0; color:#0f172a;">Congratulations!</h3>
            <div class="summary-card">
                <div class="summary-row"><span style="color:#64748b;">Bill:</span><span id="res_name" style="font-weight:700;">--</span></div>
                <div class="summary-row"><span style="color:#64748b;">Method:</span><span id="res_method" style="font-weight:700;">--</span></div>
                <div class="total-divider"></div>
                <div class="summary-row"><span style="color:#64748b;">Paid:</span><span id="res_amount" style="color:#8c3bf6; font-weight:700;">--</span></div>
            </div>
        </div>
    </div>
</div>

<script>
function dismissToast() {
    const toast = document.getElementById('activeToast');
    if(toast) {
        toast.classList.add('slide-out');
        setTimeout(() => { toast.remove(); }, 500);
    }
}
window.onload = () => { if(document.getElementById('activeToast')) setTimeout(dismissToast, 4000); };

function closeAddModal() { document.getElementById("addPaymentModal").style.display = "none"; }

function resetLeftAnimation() {
    const el = document.getElementById("modalLeftContainer");
    el.style.animation = 'none';
    el.offsetHeight; 
    el.style.animation = null;
}

function openAddModal() {
    document.getElementById("addPaymentModal").style.display = "block";
    resetLeftAnimation();
    document.getElementById("modalTitle").innerText = "Add Payment";
    document.getElementById("form_mode").value = "add";
    document.getElementById("paymentForm").reset();
    document.getElementById("editFields").style.display = "none";
    document.getElementById("saveBtn").style.display = "inline-block";
    document.getElementById("markPaidBtn").style.display = "none";
    document.getElementById("congratsPanel").style.display = "none";
    toggleInputs(false);
}

function openEditModal(id) {
    document.getElementById("addPaymentModal").style.display = "block";
    resetLeftAnimation();
    document.getElementById("modalTitle").innerText = "Update Payment";
    document.getElementById("form_mode").value = "edit";
    document.getElementById("editFields").style.display = "block";
    document.getElementById("congratsPanel").style.display = "none";
    fetch('get_payment.php?id=' + id).then(res => res.json()).then(data => {
        document.getElementById("payment_id").value = data.id;
        document.getElementById("payment_name").value = data.payment_name;
        document.getElementById("amount").value = data.amount;
        document.getElementById("due_date").value = data.due_date;
        document.getElementById("paid_date").value = data.paid_date ?? "";
        document.getElementById("payment_method").value = data.payment_method_id ?? "";
        toggleInputs(true);
    });
    document.getElementById("saveBtn").style.display = "none";
    document.getElementById("markPaidBtn").style.display = "inline-block";
}

function toggleInputs(disabled) {
    document.getElementById("payment_name").readOnly = disabled;
    document.getElementById("amount").readOnly = disabled;
    document.getElementById("due_date").readOnly = disabled;
}

function showCongrats() {
    document.getElementById("res_name").innerText = document.getElementById("payment_name").value;
    document.getElementById("res_amount").innerText = "₱" + parseFloat(document.getElementById("amount").value).toLocaleString(undefined, {minimumFractionDigits: 2});
    const sel = document.getElementById("payment_method");
    document.getElementById("res_method").innerText = sel.options[sel.selectedIndex].text;
    document.getElementById("congratsPanel").style.display = "flex";
    setTimeout(() => { document.getElementById("paymentForm").submit(); }, 2000);
}
</script>
</body>
</html>