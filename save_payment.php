<?php 
require 'db.php';
session_start();
include 'log_helper.php';

if (!isset($_SESSION['user_id'])) { exit; }
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "User";

$id = $_POST['id'] ?? null;
$name = $_POST['payment_name'] ?? null;
$amount = $_POST['amount'] ?? 0;
$due_date = $_POST['due_date'] ?? null;
$payment_method_id = $_POST['payment_method_id'] ?? null;
$paid_date = $_POST['paid_date'] ?? null;

$today = date('Y-m-d');

// ================= CASE: ADD NEW PAYMENT =================
if (empty($id)) {
    // Determine if new entry is already overdue based on date
    $status = ($due_date < $today) ? 3 : 1; 

    $sql = "INSERT INTO scheduled_payments (user_id, payment_name, amount, due_date, due_status_id) VALUES (?, ?, ?, ?, ?)";
    $conn->prepare($sql)->execute([$user_id, $name, $amount, $due_date, $status]);
    
    addLog($conn, $user_id, "$user_name Scheduled: $name");
    $_SESSION['success_msg'] = "Payment added!";
} 

// ================= CASE: UPDATE / MARK AS PAID =================
else {
    if (!empty($paid_date)) {
        $status = 2; // Paid
    } else {
        $status = ($due_date < $today) ? 3 : 1; // Overdue or Unpaid
    }

    $sql = "UPDATE scheduled_payments 
            SET paid_date = ?, 
                payment_method_id = ?, 
                due_status_id = ?,
                payment_name = ?,
                amount = ?,
                due_date = ?
            WHERE id = ? AND user_id = ?";
            
    $conn->prepare($sql)->execute([
        $paid_date, 
        $payment_method_id, 
        $status, 
        $name, 
        $amount, 
        $due_date, 
        $id, 
        $user_id
    ]);

    addLog($conn, $user_id, "$user_name Updated: $name");
    $_SESSION['success_msg'] = "Changes saved!";
}

header("Location: spender.php?page=manage_payments");
exit;