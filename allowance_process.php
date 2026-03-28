<?php
session_start();
require 'db.php';
include 'log_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- DELETE ACTION ---
if (isset($_POST['delete_budget'])) {
    $budget_id = $_POST['budget_id'];
    $stmt = $conn->prepare("DELETE FROM budget WHERE id = ? AND sponsor_id = ?");
    
    if ($stmt->execute([$budget_id, $user_id])) {
        $_SESSION['success_msg'] = "Allowance removed successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to delete record.";
    }

    $logAction = $_SESSION['fullname'] . " Deleted an Allowance: $b_name";
    addLog($conn, $user_id, $logAction);

    header("Location: sponsor.php?page=manage_allowance");
    exit;
}

// --- ADD / UPDATE ACTION ---
if (isset($_POST['add_budget']) || isset($_POST['update_budget'])) {
    $b_name   = $_POST['budget_name'];
    $b_amount = $_POST['budget_amount'];
    $s_date   = $_POST['start_date'];
    $e_date   = $_POST['end_date'];
    $spender  = $_POST['spender_id'];
    
    if (empty($s_date) || empty($e_date)) {
        $_SESSION['error_msg'] = "Please select a date range.";
        header("Location: sponsor.php?page=manage_allowance");
        exit;
    }

    if (isset($_POST['update_budget'])) {
        $b_id = $_POST['budget_id'];
        $stmt = $conn->prepare("UPDATE budget SET budget_name=?, budget_amount=?, start_date=?, end_date=?, user_id=? WHERE id=? AND sponsor_id=?");
        $result = $stmt->execute([$b_name, $b_amount, $s_date, $e_date, $spender, $b_id, $user_id]);
        $msg = "Allowance updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO budget (budget_name, budget_amount, start_date, end_date, user_id, sponsor_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        $result = $stmt->execute([$b_name, $b_amount, $s_date, $e_date, $spender, $user_id]);
        $msg = "New allowance created!";
    }

    if ($result) {
        $_SESSION['success_msg'] = $msg;
    } else {
        $_SESSION['error_msg'] = "An error occurred. Please try again.";
    }
    
    $logAction = $_SESSION['fullname'] . " Added or Updated an Allowance: $b_name";
    addLog($conn, $user_id, $logAction);

    header("Location: sponsor.php?page=manage_allowance");
    exit;
}