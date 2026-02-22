<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $date = $_POST['date'];
    $name = $_POST['payment_name'];
    $amount = $_POST['amount'];

    try {
        // Status 1 is usually 'Unpaid' in your logic
        $sql = "INSERT INTO scheduled_payments (user_id, payment_name, amount, due_date, due_status_id) 
                VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$user_id, $name, $amount, $date]);

        if ($result) {
            $_SESSION['success_msg'] = "Payment for '$name' added to " . date("M d, Y", strtotime($date));
        } else {
            $_SESSION['error_msg'] = "Failed to save payment.";
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
    }

    // Redirect back to the calendar page
    header("Location: spender.php?page=scheduler");
    exit;
}