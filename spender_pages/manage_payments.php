<?php

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
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Payments</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

.container {
    width: 100%;
    
    background: white;
    padding: 20px;
    border-radius: 10px;
}

h2 {
    margin-bottom: 20px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

th {
    background: #8c3bf6;
    color: white;
}

/* STATUS COLORS */
.paid { color: green; font-weight: bold; }
.unpaid { color: orange; font-weight: bold; }
.overdue { color: red; font-weight: bold; }

/* BUTTONS */
.btn {
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.edit {
    background: #22c55e;
    color: white;
}

.delete {
    background: #ef4444;
    color: white;
}

/* FLOATING BUTTON */
.fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #8c3bf6;
    color: white;
    font-size: 26px;
    border: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

.fab:hover {
    background: #651dd8;
}

/* Modal Overlay */
.modal {
    display: none; 
    position: fixed;
    z-index: 1000;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.7); /* Deep slate tint */
    backdrop-filter: blur(5px); /* Modern blur effect */
}

/* Modal Box */
.modal-content {
    background: #ffffff;
    width: 90%;
    max-width: 440px;
    margin: 8vh auto;
    padding: 32px;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    font-family: 'Segoe UI', Roboto, sans-serif;
}

.title-payment {
    text-align: center;
    margin-bottom: 24px;
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
}

/* Form Styling */
.modal label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
}

.modal input,
.modal select {
    width: 100%;
    padding: 12px;
    margin-bottom: 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    box-sizing: border-box;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.modal input:focus, 
.modal select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Container for the buttons */
.modal-footer {
    display: flex;
    justify-content: flex-end; /* Aligns buttons to the right */
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9; /* Subtle divider */
}

/* Base Button Styles */
.modal-footer button {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Save (Primary Action) */
.save {
    background: #8e3bbe; /* Emerald Green */
    color: white;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2), 
                0 2px 4px -2px rgba(16, 185, 129, 0.1);
}

.save:hover {
    background: #511892;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
}

.save:active {
    transform: translateY(0);
}

/* Save (Primary Action) */
.mark-paid {
    background: #10b981; /* Emerald Green */
    color: white;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2), 
                0 2px 4px -2px rgba(16, 185, 129, 0.1);
}

.mark-paid:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
}

.mark-paid:active {
    transform: translateY(0);
}

/* Cancel (Secondary Action) */
.cancel {
    background: transparent;
    color: #64748b; /* Slate Gray */
    border: 1px solid #e2e8f0 !important;
}

.cancel:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1 !important;
}

</style>
</head>

<body>

<div class="container">


    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Payment Name</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Paid Date</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
<?php $no = 1; ?>
<?php foreach ($payments as $p): ?>
    <tr>
        <td><?= $no++ ?></td>

        <td><?= htmlspecialchars($p['payment_name']) ?></td>

        <td>₱<?= number_format($p['amount'], 2) ?></td>

        <td><?= $p['due_date'] ?></td>

        <td><?= $p['paid_date'] ?? '-' ?></td>

        <td><?= $p['payment_method'] ?? '-' ?></td>

        <td class="<?= strtolower($p['status']) ?>">
            <?= ucfirst($p['status']) ?>
        </td>

        <td>
            <button class="btn edit" onclick="openEditModal(<?= $p['id'] ?>)">Update</button>
            <a href="delete_payment.php?id=<?= $p['id'] ?>"
               onclick="return confirm('Delete this payment?')">
                <button class="btn delete">Delete</button>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
    </table>
</div>

<!-- FLOATING ADD BUTTON -->
<button class="fab" onclick="openAddModal()">+</button>

<!-- ADD / EDIT PAYMENT MODAL -->
<div class="modal" id="addPaymentModal">
    <div class="modal-content">
        <div class="title-payment">
            <h3 id="modalTitle">Add Payment</h3>
        </div>

        <form method="POST" action="save_payment.php" id="paymentForm">
            <input type="hidden" name="id" id="payment_id">
            <input type="hidden" name="mode" id="form_mode">

            <label>Payment Name</label>
            <input type="text" name="payment_name" id="payment_name" required>

            <label>Amount</label>
            <input type="number" step="0.01" name="amount" id="amount" required>

            <label>Due Date</label>
            <input type="date" name="due_date" id="due_date" required>

            <!-- HIDDEN IN ADD MODE -->
            <div id="editFields">

                <label>Paid Date</label>
                <input type="date" name="paid_date" id="paid_date">

                <label>Payment Method</label>
                <select name="payment_method_id" id="payment_method">
                    <?php
                    $methods = $pdo->query("SELECT id, payment_method_name FROM payment_method")->fetchAll();
                    foreach ($methods as $m) {
                        echo "<option value='{$m['id']}'>{$m['payment_method_name']}</option>";
                    }
                    ?>
                </select>

                

            </div>

            <div class="modal-footer">
    <!-- Add mode -->
    <button type="submit" class="save" id="saveBtn">Save</button>

    <!-- Edit mode -->
    <button type="button" class="mark-paid" id="markPaidBtn" style="display:none" onclick="markAsPaid()">
        Mark as Paid
    </button>

    <!-- Common Cancel -->
    <button type="button" class="cancel" onclick="closeAddModal()">Cancel</button>
</div>
        </form>
    </div>
</div>



<script>
function closeAddModal() {
    document.getElementById("addPaymentModal").style.display = "none";
}

/* ================= ADD MODE ================= */
function openAddModal() {

    document.getElementById("addPaymentModal").style.display = "block";
    document.getElementById("modalTitle").innerText = "Add Payment";

    document.getElementById("form_mode").value = "add";

    // Reset form
    document.getElementById("paymentForm").reset();
    document.getElementById("payment_id").value = "";

    // Show Save button, hide Mark as Paid
    document.getElementById("saveBtn").style.display = "inline-block";
    document.getElementById("markPaidBtn").style.display = "none";

    // Enable inputs
    toggleInputs(false);

    // Hide edit-only fields
    document.getElementById("editFields").style.display = "none";
}

/* ================= EDIT MODE ================= */
function openEditModal(id) {

    document.getElementById("addPaymentModal").style.display = "block";
    document.getElementById("modalTitle").innerText = "Update Payment";
    document.getElementById("form_mode").value = "edit";

    // Show edit fields
    document.getElementById("editFields").style.display = "block";

    fetch('get_payment.php?id=' + id)
        .then(res => res.json())
        .then(data => {
            document.getElementById("payment_id").value = data.id;

            document.getElementById("payment_name").value = data.payment_name;
            document.getElementById("amount").value = data.amount;
            document.getElementById("due_date").value = data.due_date;
            document.getElementById("paid_date").value = data.paid_date ?? "";
            document.getElementById("payment_method").value = data.payment_method_id ?? "";

            // Disable immutable fields
            toggleInputs(true);
        });

        // Hide Save button, show Mark as Paid
    document.getElementById("saveBtn").style.display = "none";
    document.getElementById("markPaidBtn").style.display = "inline-block";
}

/* Disable fields helper */
function toggleInputs(disabled) {
    document.getElementById("payment_name").disabled = disabled;
    document.getElementById("amount").disabled = disabled;
    document.getElementById("due_date").disabled = disabled;
}

function markAsPaid() {
    // Submit form
    document.getElementById("paymentForm").submit();
}





</script>


</body>
</html>