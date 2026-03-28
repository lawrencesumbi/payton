<?php
session_start();
require 'db.php';
include 'log_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $category_id = $_POST['category_id'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $payment_method_id = $_POST['payment_method_id'];

        /* ===============================
           GET MOST RECENT ACTIVE BUDGET
        =============================== */
        $stmtBudget = $conn->prepare("
            SELECT id
            FROM budget
            WHERE user_id = ?
            AND status = 'Active'
            AND start_date <= CURDATE()
            AND end_date >= CURDATE()
            ORDER BY start_date DESC
            LIMIT 1
        ");
        $stmtBudget->execute([$user_id]);
        $budget = $stmtBudget->fetch(PDO::FETCH_ASSOC);

        // if no active budget → fallback to latest budget ever
        if ($budget) {
            $budget_id = $budget['id'];
        } else {
            $stmtFallback = $conn->prepare("
                SELECT id
                FROM budget
                WHERE user_id = ?
                ORDER BY end_date DESC
                LIMIT 1
            ");
            $stmtFallback->execute([$user_id]);
            $lastBudget = $stmtFallback->fetch(PDO::FETCH_ASSOC);
            $budget_id = $lastBudget['id'] ?? NULL;
        }

        /* ===============================
           RECEIPT UPLOAD
        =============================== */
        $receiptPath = null;
        if (!empty($_FILES['receipt_upload']['name']) 
            && $_FILES['receipt_upload']['error'] === UPLOAD_ERR_OK) {

            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = time() . '_' . basename($_FILES['receipt_upload']['name']);
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['receipt_upload']['tmp_name'], $targetFile)) {
                $receiptPath = $targetFile;
            }
        }

        /* ===============================
           INSERT EXPENSE
        =============================== */
        $stmt = $conn->prepare("
            INSERT INTO expenses
            (user_id, budget_id, category_id, description, amount,
             payment_method_id, receipt_upload, expense_date,
             created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
        ");

        $stmt->execute([
            $user_id,
            $budget_id,
            $category_id,
            $description,
            $amount,
            $payment_method_id,
            $receiptPath
        ]);

        $_SESSION['success_msg'] =
            "Transaction for '$description' has been recorded!";

    } catch (Exception $e) {
        error_log("Add expense error: " . $e->getMessage()); // log real error
        $_SESSION['error_msg'] =
            "Failed to add expense. Please try again.";
    }

    $logAction = $_SESSION['fullname'] . " Added an Expense: $description - ₱" . number_format($amount, 2);
    addLog($conn, $user_id, $logAction);

    header("Location: spender.php?page=manage_expenses");
    exit;
}