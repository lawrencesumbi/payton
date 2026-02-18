<?php
session_start();

$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

$user_id = $_SESSION['user_id']; // make sure login exists
$name = $_POST['payment_name'];
$amount = $_POST['amount'];
$date = $_POST['date'];

$stmt = $pdo->prepare("
    INSERT INTO scheduled_payments (user_id, payment_name, amount, due_date)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $name, $amount, $date]);

header("Location: spender.php?page=scheduler");
