<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "Invalid expense ID.";
    header("Location: spender.php?page=manage_expenses");
    exit;
}

try {
    $expense_id = intval($_GET['id']);

    // Check ownership
    $stmt = $conn->prepare("SELECT receipt_upload FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->execute([$expense_id, $user_id]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($expense) {
        // Delete file if exists
        if ($expense['receipt_upload'] && file_exists($expense['receipt_upload'])) {
            unlink($expense['receipt_upload']);
        }

        $del = $conn->prepare("DELETE FROM expenses WHERE id = ?");
        $del->execute([$expense_id]);
        
        $_SESSION['success_msg'] = "Expense deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Expense not found or unauthorized.";
    }
} catch (Exception $e) {
    $_SESSION['error_msg'] = "An error occurred while deleting.";
}

header("Location: spender.php?page=manage_expenses");
exit;