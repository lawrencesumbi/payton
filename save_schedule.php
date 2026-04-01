<?php
session_start();
require 'db.php';
include 'log_helper.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    // Basic sanitization
    $date    = $_POST['date'];
    $name    = htmlspecialchars($_POST['payment_name']);
    $amount  = floatval($_POST['amount']);

    try {
        // 1. Insert the schedule
        $sql = "INSERT INTO scheduled_payments (user_id, payment_name, amount, due_date, due_status_id) 
                VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$user_id, $name, $amount, $date]);

        if ($result) {
            $_SESSION['success_msg'] = "Payment for '$name' added to " . date("M d, Y", strtotime($date));
            
            // 2. LOGGING FIX: Fetch user details if they aren't in the session
            // Assuming your 'users' table has these columns
            $userStmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ?");
            $userStmt->execute([$user_id]);
            $user = $userStmt->fetch();

            if ($user) {
                $logAction = $user["fullname"] . " Scheduled a Payment: $name (" . ucfirst($user["role"]) . ")";
                addLog($conn, $user_id, $logAction);
            }
        } else {
            $_SESSION['error_msg'] = "Failed to save payment.";
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
    }

    header("Location: spender.php?page=scheduler");
    exit;
}