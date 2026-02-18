<?php

$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

$user_id = $_SESSION['user_id'] ?? 1;

$name = $_POST['payment_name'];
$amount = $_POST['amount'];
$due_date = $_POST['due_date'];
$is_recurring = $_POST['is_recurring'];
$recurrence_type_id = $_POST['recurrence_type_id'] ?: null;
$payment_method_id = $_POST['payment_method_id'];
$due_status_id = $_POST['due_status_id'];

$stmt = $pdo->prepare("
    INSERT INTO scheduled_payments
    (user_id, payment_name, amount, due_date, is_recurring, recurrence_type_id, payment_method_id, due_status_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $name,
    $amount,
    $due_date,
    $is_recurring,
    $recurrence_type_id,
    $payment_method_id,
    $due_status_id
]);

header("Location: http://localhost/payton/spender.php?page=manage_payments");
