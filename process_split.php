<?php
require_once "db.php";
session_start();
include "log_helper.php";

// 1. IMPORT PHPMAILER CLASSES
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

// CHANGE: Only block if it's NOT a public settle request
if (!isset($_SESSION['user_id']) && !isset($_POST['is_public_settle'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'] ?? null;
$my_name = $_SESSION['fullname'] ?? "A friend"; // Used for the email sender name

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
    // ... (Your existing variable assignments remain exactly the same) ...
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
        
        // ... (Your existing UPDATE/INSERT logic remains exactly the same) ...
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
        
        // Prepare to store email data
        $notifications = [];

        if ($split_type === 'equal') {
            $total_people = count($selected_participants) + 1;
            $split_amount = round($total_amount / $total_people, 2);
            foreach ($selected_participants as $pid) {
                $share_stmt->execute([$expense_id, $user_id, $pid, $split_amount]);
                $notifications[] = ['pid' => $pid, 'amt' => $split_amount];
            }
        } else {
            foreach ($selected_participants as $pid) {
                $amt = floatval($custom_amounts[$pid] ?? 0);
                if ($amt > 0) {
                    $share_stmt->execute([$expense_id, $user_id, $pid, $amt]);
                    $notifications[] = ['pid' => $pid, 'amt' => $amt];
                }
            }
        }

        $conn->commit();
        $_SESSION['success_msg'] = "Expense saved and notifications are being sent!";

        // --- GMAIL NOTIFICATION LOGIC ---
        foreach ($notifications as $note) {
            // Fetch friend's name and email
            $pStmt = $conn->prepare("SELECT name, email FROM people WHERE id = ?");
            $pStmt->execute([$note['pid']]);
            $person = $pStmt->fetch(PDO::FETCH_ASSOC);

            if ($person && !empty($person['email'])) {
                // Add $expense_id here
                sendGmailNotification(
                    $person['email'], 
                    $person['name'], 
                    $description, 
                    $note['amt'], 
                    $my_name, 
                    $expense_id, 
                    $note['pid'] // <--- THIS IS WHAT WAS MISSING
                );
            }
        }

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }

    $logAction = $my_name . " Added/Updated Expense: $description - ₱" . number_format($total_amount, 2);
    addLog($conn, $user_id, $logAction);

    header("Location: spender.php?page=split_expense");
    exit();
}

/**
 * Function to send Gmail Notification
 */
function sendGmailNotification($toEmail, $toName, $desc, $amt, $sender, $expense_id, $pid) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'payton.support@gmail.com';
        $mail->Password   = 'mmvq ebkg ctww kirs'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('payton.support@gmail.com', 'Payton');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = "New Shared Expense: $desc";

        // --- DYNAMIC LINK & SECURITY HASH START ---
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $folder = "/payton/";
        $baseUrl = $protocol . $host . $folder;

        // 1. ADD YOUR SECRET KEY
        $secret_key = "Karan_Secret_789"; 

        // 2. GENERATE THE AUTH HASH
        $hash = md5($expense_id . $secret_key); 

        // 3. POINT TO THE NEW INVOLVED PAGE (Note the filename change and new parameters)
        $viewUrl = $baseUrl . "view_split_expense_involved.php?id=" . $expense_id . "&pid=" . $pid . "&auth=" . $hash;
        // --- DYNAMIC LINK & SECURITY HASH END ---
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; border: 1px solid #e2e8f0; padding: 30px; border-radius: 16px; max-width: 500px; margin: auto; color: #1e293b;'>
                <h2 style='color: #6366f1; margin-top: 0;'>New Split Expense</h2>
                <p style='font-size: 16px;'>Hi <strong>$toName</strong>,</p>
                <p style='font-size: 14px; color: #64748b;'>$sender added a new expense and split a portion with you.</p>
                
                <div style='background: #f8fafc; padding: 20px; border-radius: 12px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='color: #64748b; font-size: 12px; text-transform: uppercase;'>Description</td>
                            <td style='text-align: right; font-weight: 600;'>$desc</td>
                        </tr>
                        <tr>
                            <td style='color: #64748b; font-size: 12px; text-transform: uppercase; padding-top: 10px;'>Your Share</td>
                            <td style='text-align: right; font-weight: 700; color: #ef4444; font-size: 20px; padding-top: 10px;'>₱" . number_format($amt, 2) . "</td>
                        </tr>
                    </table>
                </div>

                <div style='text-align: center; margin-top: 30px;'>
                    <a href='$viewUrl' style='background-color: #6366f1; color: white; padding: 14px 24px; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-block;'>View & Settle Share</a>
                </div>

            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
    }
}

// --- ACTION HANDLER: PARTIAL OR FULL SETTLE ---
if (isset($_POST['partial_settle'])) {
    $person_id = intval($_POST['person_id']); 
    $target_expense_id = intval($_POST['expense_id']);
    $settle_amount = floatval($_POST['settle_amount']);
    $is_public = isset($_POST['is_public_settle']);

    try {
        $conn->beginTransaction();

        // 1. Get current balance and description for logging
        $stmt = $conn->prepare("
            SELECT es.amount_owed, e.description, e.user_id as owner_id, 
                   u.email as owner_email, u.fullname as owner_name,
                   p.name as payer_name
            FROM expense_shares es
            JOIN expenses e ON e.id = es.expense_id
            JOIN users u ON e.user_id = u.id
            JOIN people p ON es.people_id = p.id
            WHERE es.expense_id = ? AND es.people_id = ?
        ");
        $stmt->execute([$target_expense_id, $person_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $current_owed = floatval($data['amount_owed']);
            $description = $data['description'];
            $owner_id = $data['owner_id']; // The user who created the expense

            // 2. Calculate new balance
            $new_balance = $current_owed - $settle_amount;
            if ($new_balance < 0) $new_balance = 0; 

            // 3. Determine status
            $new_status = ($new_balance <= 0.01) ? 'Paid' : 'Unpaid';

            // 4. Update the database
            $update_stmt = $conn->prepare("
                UPDATE expense_shares 
                SET amount_owed = ?, status = ? 
                WHERE expense_id = ? AND people_id = ?
            ");
            $update_stmt->execute([$new_balance, $new_status, $target_expense_id, $person_id]);

            // 5. Log the action
            // If public, we use "A Friend" and the owner_id from the DB. 
            // If logged in, we use the session data.
            $log_user_id = $is_public ? $owner_id : $_SESSION['user_id'];
            $log_name = $is_public ? "A Friend (via Email)" : $_SESSION['fullname'];
            
            $logAction = $log_name . " settled ₱" . number_format($settle_amount, 2) . " for '$description'. Remaining: ₱" . number_format($new_balance, 2);
            addLog($conn, $log_user_id, $logAction);

            $conn->commit();
            notifyOwnerOfPayment(
                $data['owner_email'], 
                $data['owner_name'], 
                $data['payer_name'], 
                $settle_amount, 
                $description, 
                $target_expense_id
            );
            $_SESSION['success_msg'] = "Payment processed! Remaining balance: ₱" . number_format($new_balance, 2);
        } else {
            throw new Exception("Record not found.");
        }

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }

    // --- REDIRECT LOGIC ---
    if ($is_public) {
        $secret_key = "Karan_Secret_789"; 
        $hash = md5($target_expense_id . $secret_key);
        // Include the &pid= in the redirect
        header("Location: view_split_expense_involved.php?id=$target_expense_id&pid=$person_id&auth=$hash");
    } else {
        header("Location: spender.php?page=view_split_expense&expense_id=" . $target_expense_id);
    }
    exit();
}

function notifyOwnerOfPayment($ownerEmail, $ownerName, $payerName, $amount, $description, $expense_id) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'payton.support@gmail.com';
        $mail->Password   = 'mmvq ebkg ctww kirs'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('payton.support@gmail.com', 'Payton');
        $mail->addAddress($ownerEmail, $ownerName);
        $mail->isHTML(true);
        $mail->Subject = "Payment Received: $payerName paid you ₱" . number_format($amount, 2);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $viewUrl = $protocol . $host . "/payton/spender.php?page=view_split_expense&expense_id=" . $expense_id;

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; border: 1px solid #e2e8f0; padding: 30px; border-radius: 16px; max-width: 500px; margin: auto; color: #1e293b;'>
                <h2 style='color: #22c55e; margin-top: 0;'>Payment Recorded!</h2>
                <p style='font-size: 16px;'>Hi <strong>$ownerName</strong>,</p>
                <p style='font-size: 14px; color: #64748b;'><strong>$payerName</strong> just recorded a payment to you on Payton.</p>
                
                <div style='background: #f0fdf4; border: 1px solid #bbf7d0; padding: 20px; border-radius: 12px; margin: 20px 0; text-align: center;'>
                    <span style='color: #166534; font-size: 12px; text-transform: uppercase; font-weight: bold;'>Amount Received</span>
                    <h2 style='margin: 5px 0; color: #15803d; font-size: 28px;'>₱" . number_format($amount, 2) . "</h2>
                    <p style='margin: 0; color: #64748b; font-size: 14px;'>For: $description</p>
                </div>

                <div style='text-align: center; margin-top: 30px;'>
                    <a href='$viewUrl' style='background-color: #1e293b; color: white; padding: 14px 24px; text-decoration: none; border-radius: 10px; font-weight: 600; display: inline-block;'>View on Breakdown</a>
                </div>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Owner Notification Error: " . $mail->ErrorInfo);
    }
}
