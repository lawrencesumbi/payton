<?php 
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];

// Get POST values safely
$id = $_POST['id'] ?? null;  // Hidden field for edit
$name = $_POST['payment_name'] ?? null;
$amount = $_POST['amount'] ?? null;
$due_date = $_POST['due_date'] ?? null;
$recurrence_type_id = $_POST['recurrence_type_id'] ?? null; // fixed

// Hidden fields for paid/unpaid
$payment_method_id = $_POST['payment_method_id'] ?? null; // or null if using "Mark as Paid"
$due_status_id = $_POST['due_status_id'] ?? 1;          // Unpaid by default
$paid_date = $_POST['paid_date'] ?? null;

if (!$name || !$amount || !$due_date) {
    exit("Missing required fields.");
}

// ================= ADD PAYMENT =================
if (empty($id)) {

    $sql = "INSERT INTO scheduled_payments
            (user_id, payment_name, amount, due_date, recurrence_type_id, payment_method_id, due_status_id, paid_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $user_id,
        $name,
        $amount,
        $due_date,
        $recurrence_type_id,
        $payment_method_id,
        $due_status_id,
        $paid_date
    ]);

} 
// ================= EDIT PAYMENT =================
else {

    $sql = "UPDATE scheduled_payments
            SET payment_name = ?,
                amount = ?,
                due_date = ?,
                recurrence_type_id = ?,
                payment_method_id = ?,
                due_status_id = ?,
                paid_date = ?
            WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $name,
        $amount,
        $due_date,
        $recurrence_type_id,
        $payment_method_id,
        $due_status_id,
        $paid_date,
        $id,
        $user_id
    ]);
}

header("Location: http://localhost/payton/spender.php?page=manage_payments");
exit;
