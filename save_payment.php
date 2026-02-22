<?php 
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];

// Get POST values safely
$id = $_POST['id'] ?? null;
$name = $_POST['payment_name'] ?? null;
$amount = $_POST['amount'] ?? null;
$due_date = $_POST['due_date'] ?? null;

// Optional fields
$payment_method_id = $_POST['payment_method_id'] ?? null;
$paid_date = $_POST['paid_date'] ?? null;

// ================= ADD PAYMENT =================
if (empty($id)) {

    if (!$name || !$amount || !$due_date) {
        $_SESSION['error_msg'] = "Please fill in all required fields.";
        header("Location: http://localhost/payton/spender.php?page=manage_payments");
        exit();
    }

    // Default unpaid
    $due_status_id = 1;

    $sql = "INSERT INTO scheduled_payments
            (user_id, payment_name, amount, due_date, due_status_id)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $user_id,
        $name,
        $amount,
        $due_date,
        $due_status_id
    ]);

    $_SESSION['success_msg'] = "Payment for '$name' scheduled successfully!";
}

// ================= EDIT PAYMENT =================
else {

    // If paid_date exists → mark as paid
    if (!empty($paid_date)) {
        $due_status_id = 2; // Paid
    } else {
        $due_status_id = 1; // Still unpaid
    }

    $sql = "UPDATE scheduled_payments
            SET 
                paid_date = ?,
                payment_method_id = ?,
                due_status_id = ?
            WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $paid_date,
        $payment_method_id,
        $due_status_id,
        $id,
        $user_id
    ]);

    $_SESSION['success_msg'] = "Payment details updated successfully!";
}

header("Location: http://localhost/payton/spender.php?page=manage_payments");
exit;