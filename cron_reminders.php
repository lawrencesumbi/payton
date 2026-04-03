<?php
header('Content-Type: text/html; charset=utf-8');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Path to your PHPMailer - update these based on your folder structure
require 'vendor/autoload.php'; 
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

/**
 * SMART LOGIC: 
 * We fetch Unpaid bills due in 3 days, 1 day, or Overdue bills from yesterday.
 */
$query = "
    SELECT u.email, u.fullname, sp.payment_name, sp.amount, sp.due_date, ds.due_status_name,
           DATEDIFF(sp.due_date, CURDATE()) as days_left
    FROM scheduled_payments sp
    JOIN users u ON sp.user_id = u.id
    JOIN due_status ds ON sp.due_status_id = ds.id
    WHERE (ds.due_status_name = 'Unpaid' AND DATEDIFF(sp.due_date, CURDATE()) IN (3, 1))
       OR (ds.due_status_name = 'Overdue' AND sp.due_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY))
";

$stmt = $pdo->query($query);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$reminders) {
    die("No smart reminders to send today.");
}

foreach ($reminders as $row) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; // <--- ADD THIS LINE
    try {
        // --- SMTP CONFIGURATION ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'guiansumbi@gmail.com'; // YOUR GMAIL
        $mail->Password   = 'qvuq rtbg syud xwfu'; // YOUR GMAIL APP PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- EMAIL HEADER ---
        $mail->setFrom('no-reply@payton.com', 'Payton Smart Reminders');
        $mail->addAddress($row['email'], $row['fullname']);

        // --- DYNAMIC CONTENT BASED ON URGENCY ---
        $days = $row['days_left'];
        $amount = number_format($row['amount'], 2);
        
        if ($row['due_status_name'] == 'overdue') {
            $subject = "🚨 OVERDUE: {$row['payment_name']} Payment";
            $message = "Your payment of <b>₱$amount</b> was due yesterday. Please settle this immediately to avoid penalties.";
            $color = "#ef4444"; // Red
        } elseif ($days == 1) {
            $subject = "⏳ Final Call: {$row['payment_name']} due tomorrow";
            $message = "Quick heads up! Your <b>₱$amount</b> payment for {$row['payment_name']} is due <b>tomorrow</b>.";
            $color = "#f59e0b"; // Orange
        } else {
            $subject = "📅 Upcoming Payment: {$row['payment_name']}";
            $message = "Friendly reminder: You have an upcoming payment of <b>₱$amount</b> due in 3 days ({$row['due_date']}).";
            $color = "#8c3bf6"; // Purple
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; border: 1px solid #eee; padding: 20px;'>
                <h2 style='color: $color;'>Payton Payment Alert</h2>
                <p>Hi <b>{$row['fullname']}</b>,</p>
                <p>$message</p>
                <div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <b>Bill:</b> {$row['payment_name']}<br>
                    <b>Amount:</b> ₱$amount<br>
                    <b>Due Date:</b> {$row['due_date']}
                </div>
                <p>Click below to view your dashboard and mark this as paid:</p>
                <a href='http://localhost/payton/spender.php?page=manage_payments' 
                   style='background: $color; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                   Go to Dashboard
                </a>
            </div>";

        $mail->send();
        echo "Sent: " . $row['payment_name'] . " to " . $row['email'] . "<br>";
    } catch (Exception $e) {
        echo "Failed to send to {$row['email']}. Error: {$mail->ErrorInfo}<br>";
    }
}