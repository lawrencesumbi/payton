<?php
require_once "db.php";
session_start();

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
    header("Location: spender.php?page=split_expense");
    exit();
}

// --- ACTION HANDLER: MARK AS PAID ---
if (isset($_POST['mark_paid'])) {
    $person_id = intval($_POST['person_id']); 
    $target_expense_id = intval($_POST['expense_id']);

    $update_stmt = $conn->prepare("
        UPDATE expense_shares 
        SET status = 'Paid' 
        WHERE expense_id = ? AND people_id = ?
    ");
    
    if($update_stmt->execute([$target_expense_id, $person_id])) {
        $_SESSION['success_msg'] = "Payment marked as settled!";
    } else {
        $_SESSION['error_msg'] = "Failed to update payment status.";
    }
    
    // Redirect back to the view page
    header("Location: spender.php?page=view_split_expense&expense_id=" . $target_expense_id);
    exit();
}