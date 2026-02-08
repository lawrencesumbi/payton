<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $payment_method_id = $_POST['payment_method_id'];

    // Handle file upload
    $receiptPath = null;
    if (!empty($_FILES['receipt_upload']['name']) && $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/receipts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = time() . '_' . basename($_FILES['receipt_upload']['name']);
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['receipt_upload']['tmp_name'], $targetFile)) {
            $receiptPath = $targetFile;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO expenses 
        (user_id, category_id, description, amount, payment_method_id, receipt_upload, expense_date, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
    ");
    $stmt->execute([$user_id, $category_id, $description, $amount, $payment_method_id, $receiptPath]);

    $_SESSION['success'] = "Expense added successfully!";
    header("Location: spender.php");
    exit;
}
?>
