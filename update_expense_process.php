<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  exit;
}

$id = $_POST['expense_id'];
$category = $_POST['category_id'];
$desc = $_POST['description'];
$amount = $_POST['amount'];
$payment = $_POST['payment_method_id'];

$sql = "UPDATE expenses
        SET category_id = ?, description = ?, amount = ?, payment_method_id = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([
  $category,
  $desc,
  $amount,
  $payment,
  $id,
  $_SESSION['user_id']
]);

header("Location: spender.php");
exit;
