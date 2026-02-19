<?php

session_start();

$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

$user_id = $_SESSION['user_id'] ?? 1;

$name = $_POST['payment_name'];
$amount = $_POST['amount'];
$due_date = $_POST['due_date'];
$recurrence_type_id = $_POST['recurrence_type_id'] ?: null;

$payment_method_id = null;   // always NULL on add
$due_status_id = 1;          // force "Unpaid"

$stmt = $pdo->prepare("
    INSERT INTO scheduled_payments
    (user_id, payment_name, amount, due_date, recurrence_type_id, payment_method_id, due_status_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $name,
    $amount,
    $due_date,
    $recurrence_type_id,
    $payment_method_id,
    $due_status_id
]);

header("Location: http://localhost/payton/spender.php?page=manage_payments");
exit;
