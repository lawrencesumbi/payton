<?php
session_start();
require 'db.php';

// 1. Security Check: Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Validate the ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "Invalid payment ID.";
    header("Location: http://localhost/payton/spender.php?page=manage_payments");
    exit;
}

$payment_id = intval($_GET['id']);

try {
    // 3. Delete the payment (ensuring it belongs to the logged-in user)
    $stmt = $conn->prepare("DELETE FROM scheduled_payments WHERE id = ? AND user_id = ?");
    $stmt->execute([$payment_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_msg'] = "Scheduled payment deleted successfully.";
    } else {
        $_SESSION['error_msg'] = "Payment not found or unauthorized.";
    }

} catch (Exception $e) {
    $_SESSION['error_msg'] = "Error deleting payment: " . $e->getMessage();
}

// 4. Redirect back to the main page
header("Location: http://localhost/payton/spender.php?page=manage_payments");
exit;