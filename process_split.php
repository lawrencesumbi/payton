<?php
require_once "db.php";
session_start();
include "log_helper.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

// --- HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM expense_shares WHERE expense_id = ?")->execute([$delete_id]);
        $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
        $stmt->execute([$delete_id, $user_id]);
        $conn->commit();
        $_SESSION['success_msg'] = "Expense deleted successfully.";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_msg'] = "Error deleting: " . $e->getMessage();
    }

    $logAction = $_SESSION['fullname'] . " Deleted an Expense: $description - ₱" . number_format($amount, 2);
    addLog($conn, $user_id, $logAction);

    header("Location: spender.php?page=split_expense");
    exit();
}

// --- HANDLE SAVE (CREATE & UPDATE) ---
if (isset($_POST['save_expense'])) {
    $expense_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : null;
    $description = trim($_POST['description']);
    $total_amount = floatval($_POST['amount']);
    $payment_method_id = intval($_POST['payment_method_id']);
    $category_id = intval($_POST['category_id']);
    $expense_date = $_POST['expense_date'];
    $selected_participants = $_POST['participants'] ?? [];
    $split_type = $_POST['split_type'] ?? 'equal';
    $custom_amounts = $_POST['custom_amounts'] ?? [];
    $budget_id = $_POST['budget_id'] ?? null;

    if (!$budget_id) {
        $_SESSION['error_msg'] = "No active budget found.";
        header("Location: spender.php?page=split_expense");
        exit();
    }

    try {
        $conn->beginTransaction();
        if ($expense_id) {
            $stmt = $conn->prepare("UPDATE expenses SET category_id=?, description=?, amount=?, payment_method_id=?, expense_date=? WHERE id=? AND user_id=?");
            $stmt->execute([$category_id, $description, $total_amount, $payment_method_id, $expense_date, $expense_id, $user_id]);
            $conn->prepare("DELETE FROM expense_shares WHERE expense_id = ?")->execute([$expense_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO expenses (user_id, budget_id, category_id, description, amount, payment_method_id, expense_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $budget_id, $category_id, $description, $total_amount, $payment_method_id, $expense_date]);
            $expense_id = $conn->lastInsertId();
        }

        $share_stmt = $conn->prepare("INSERT INTO expense_shares (expense_id, user_id, people_id, amount_owed, status) VALUES (?, ?, ?, ?, 'Unpaid')");
        if ($split_type === 'equal') {
            $total_people = count($selected_participants) + 1;
            $split_amount = round($total_amount / $total_people, 2);
            foreach ($selected_participants as $pid) {
                $share_stmt->execute([$expense_id, $user_id, $pid, $split_amount]);
            }
        } else {
            foreach ($selected_participants as $pid) {
                $amt = floatval($custom_amounts[$pid] ?? 0);
                if ($amt > 0) $share_stmt->execute([$expense_id, $user_id, $pid, $amt]);
            }
        }
        $conn->commit();
        $_SESSION['success_msg'] = "Expense saved successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }

    $logAction = $_SESSION['fullname'] . " Added or Updated an Expense: $description - ₱" . number_format($total_amount, 2);
    addLog($conn, $user_id, $logAction);

    header("Location: spender.php?page=split_expense");
    exit();
}

// --- ACTION HANDLER: PARTIAL OR FULL SETTLE ---
if (isset($_POST['partial_settle'])) {
    $person_id = intval($_POST['person_id']); 
    $target_expense_id = intval($_POST['expense_id']);
    $settle_amount = floatval($_POST['settle_amount']);

    try {
        $conn->beginTransaction();

        // 1. Get current balance and description for logging
        $stmt = $conn->prepare("
            SELECT es.amount_owed, e.description 
            FROM expense_shares es
            JOIN expenses e ON e.id = es.expense_id
            WHERE es.expense_id = ? AND es.people_id = ?
        ");
        $stmt->execute([$target_expense_id, $person_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $current_owed = floatval($data['amount_owed']);
            $description = $data['description'];

            // 2. Calculate new balance
            $new_balance = $current_owed - $settle_amount;
            if ($new_balance < 0) $new_balance = 0; // Prevent negative balance

            // 3. Determine status (Paid if balance is effectively 0)
            $new_status = ($new_balance <= 0.01) ? 'Paid' : 'Unpaid';

            // 4. Update the database
            $update_stmt = $conn->prepare("
                UPDATE expense_shares 
                SET amount_owed = ?, status = ? 
                WHERE expense_id = ? AND people_id = ?
            ");
            $update_stmt->execute([$new_balance, $new_status, $target_expense_id, $person_id]);

            // 5. Log the action
            $logAction = $_SESSION['fullname'] . " settled ₱" . number_format($settle_amount, 2) . " for '$description'. Remaining: ₱" . number_format($new_balance, 2);
            addLog($conn, $user_id, $logAction);

            $conn->commit();
            $_SESSION['success_msg'] = "Payment processed! Remaining balance: ₱" . number_format($new_balance, 2);
        } else {
            throw new Exception("Record not found.");
        }

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }

    header("Location: spender.php?page=view_split_expense&expense_id=" . $target_expense_id);
    exit();
}