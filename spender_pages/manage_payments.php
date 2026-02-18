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
        sp.is_recurring,
        rt.recurrence_type_name AS recurrence_type,
        sp.paid_date,
        pm.payment_method_name AS payment_method,
        ds.due_status_name AS status
    FROM scheduled_payments sp
    LEFT JOIN recurrence_type rt ON sp.recurrence_type_id = rt.id
    LEFT JOIN payment_method pm ON sp.payment_method_id = pm.id
    LEFT JOIN due_status ds ON sp.due_status_id = ds.id
    WHERE sp.user_id = ?
    ORDER BY sp.due_date ASC
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
    width: 90%;
    margin: 30px auto;
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
    background: #3b82f6;
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
    background: #3b82f6;
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
    background: #1d4ed8;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    background: rgba(0,0,0,0.5);
    top: 0; left: 0;
    width: 100%; height: 100%;
}

.modal-content {
    background: white;
    width: 420px;
    margin: 6% auto;
    padding: 20px;
    border-radius: 10px;
}

/* FORM */
.modal input,
.modal select {
    width: 100%;
    padding: 8px;
    margin-top: 6px;
    margin-bottom: 10px;
}

.save {
    background: #22c55e;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
}

.cancel {
    background: #ef4444;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
}

</style>
</head>

<body>

<div class="container">
    <h2>Manage Payments</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Payment Name</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Recurring</th>
                <th>Recurrence Type</th>
                <th>Paid Date</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>

                <td><?= htmlspecialchars($p['payment_name']) ?></td>

                <td>₱<?= number_format($p['amount'], 2) ?></td>

                <td><?= $p['due_date'] ?></td>

                <td><?= $p['is_recurring'] ? 'Yes' : 'No' ?></td>

                <td><?= $p['recurrence_type'] ?? '-' ?></td>

                <td><?= $p['paid_date'] ?? '-' ?></td>

                <td><?= $p['payment_method'] ?? '-' ?></td>

                <td class="<?= strtolower($p['status']) ?>">
                    <?= ucfirst($p['status']) ?>
                </td>

                <td>
                    <a href="edit_payment.php?id=<?= $p['id'] ?>">
                        <button class="btn edit">Edit</button>
                    </a>

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

<!-- ADD PAYMENT MODAL -->
<div class="modal" id="addPaymentModal">
    <div class="modal-content">
        <h3>Add Payment</h3>

        <form method="POST" action="add_payment.php">
            
            <label>Payment Name</label>
            <input type="text" name="payment_name" required>

            <label>Amount</label>
            <input type="number" step="0.01" name="amount" required>

            <label>Due Date</label>
            <input type="date" name="due_date" required>

            <label>Recurring?</label>
            <select name="is_recurring">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>

            <label>Recurrence Type</label>
            <select name="recurrence_type_id">
                
                <?php
                $types = $pdo->query("SELECT id, recurrence_type_name FROM recurrence_type")->fetchAll();
                foreach ($types as $t) {
                    echo "<option value='{$t['id']}'>{$t['recurrence_type_name']}</option>";
                }
                ?>
            </select>

            <label>Payment Method</label>
            <select name="payment_method_id">
                <?php
                $methods = $pdo->query("SELECT id, payment_method_name FROM payment_method")->fetchAll();
                foreach ($methods as $m) {
                    echo "<option value='{$m['id']}'>{$m['payment_method_name']}</option>";
                }
                ?>
            </select>

            <label>Status</label>
            <select name="due_status_id">
                <?php
                $statuses = $pdo->query("SELECT id, due_status_name FROM due_status")->fetchAll();
                foreach ($statuses as $s) {
                    echo "<option value='{$s['id']}'>{$s['due_status_name']}</option>";
                }
                ?>
            </select>

            <div style="margin-top:15px;">
                <button type="submit" class="save">Save</button>
                <button type="button" class="cancel" onclick="closeAddModal()">Cancel</button>
            </div>

        </form>
    </div>
</div>



<script>
function openAddModal() {
    document.getElementById("addPaymentModal").style.display = "block";
}

function closeAddModal() {
    document.getElementById("addPaymentModal").style.display = "none";
}
</script>


</body>
</html>
