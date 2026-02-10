<?php
session_start();
require 'db.php'; // Adjust path to your db.php

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate expense ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: spender.php");
    exit;
}

$expense_id = intval($_GET['id']);

// Make sure the expense belongs to this user
$stmt = $conn->prepare("SELECT id FROM expenses WHERE id = ? AND user_id = ?");
$stmt->execute([$expense_id, $user_id]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    // Expense not found or doesn't belong to user
    header("Location: spender.php");
    exit;
}

// Delete the expense
$stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
$stmt->execute([$expense_id, $user_id]);

// Optionally: delete receipt file
$receipt_path = "../uploads/" . $_GET['receipt'] ?? '';
if ($receipt_path && file_exists($receipt_path)) {
    unlink($receipt_path);
}

// Redirect back with success message
header("Location: http://localhost/payton/spender.php?page=manage_expenses&deleted=1");
exit;
