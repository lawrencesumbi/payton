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

// Check if a new receipt is uploaded
if (isset($_FILES['receipt_upload']) && $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {
    
    // Generate unique filename
    $newFileName = uniqid() . '-' . $_FILES['receipt_upload']['name'];
    
    // Move uploaded file to 'uploads' folder
    move_uploaded_file($_FILES['receipt_upload']['tmp_name'], __DIR__ . '/uploads/' . $newFileName);
    
    // Full path to store in database
    $receiptPath = 'uploads/' . $newFileName;

    // Update with receipt
    $sql = "UPDATE expenses
            SET category_id = ?, description = ?, amount = ?, payment_method_id = ?, receipt_upload = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $category,
        $desc,
        $amount,
        $payment,
        $receiptPath,
        $id,
        $_SESSION['user_id']
    ]);

} else {
    // Update without changing receipt
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
}

header("Location: http://localhost/payton/spender.php?page=manage_expenses");
exit;
