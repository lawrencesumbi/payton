<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

try {
    $id = $_POST['expense_id'];
    $category = $_POST['category_id'];
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $payment = $_POST['payment_method_id'];
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['receipt_upload']) && $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {
        $newFileName = uniqid() . '-' . $_FILES['receipt_upload']['name'];
        move_uploaded_file($_FILES['receipt_upload']['tmp_name'], __DIR__ . '/uploads/' . $newFileName);
        $receiptPath = 'uploads/' . $newFileName;

        $sql = "UPDATE expenses SET category_id=?, description=?, amount=?, payment_method_id=?, receipt_upload=?, updated_at=NOW() WHERE id=? AND user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$category, $desc, $amount, $payment, $receiptPath, $id, $user_id]);
    } else {
        $sql = "UPDATE expenses SET category_id=?, description=?, amount=?, payment_method_id=?, updated_at=NOW() WHERE id=? AND user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$category, $desc, $amount, $payment, $id, $user_id]);
    }

    $_SESSION['success_msg'] = "Changes to '$desc' saved successfully.";

} catch (Exception $e) {
    $_SESSION['error_msg'] = "Could not update expense.";
}

header("Location: spender.php?page=manage_expenses");
exit;