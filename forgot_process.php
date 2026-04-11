<?php

date_default_timezone_set('Asia/Manila');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();
require_once "db.php";
require_once "log_helper.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $update->execute([$token, $expiry, $email]);

        // --- ACTUAL PHPMAILER LOGIC ---
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'payton.support@gmail.com'; // Your Gmail
            $mail->Password   = 'mmvq ebkg ctww kirs';    // Your 16-character App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('payton.support@gmail.com', 'Payton');
            $mail->addAddress($email);

            // 1. Detect the protocol (http or https)
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            // 2. Detect the host (localhost OR karan-unrevolved-unconcretely.ngrok-free.dev)
            $host = $_SERVER['HTTP_HOST'];
            // 3. Combine them to create the base URL
            $baseUrl = $protocol . "://" . $host . "/payton/";
            // 4. Create your link
            $resetLink = $baseUrl . "reset_password.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Payton Password';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #7f308f;'>Password Reset Request</h2>
                    <p>You requested to reset your password for your <strong>Payton</strong> account.</p>
                    <p>Click the button below to set a new password. This link expires in 1 hour.</p>
                    <a href='$resetLink' style='background: #7f308f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                    <p style='margin-top: 20px; font-size: 12px; color: #777;'>If you didn't request this, please ignore this email.</p>
                </div>
            ";

            $mail->send();
            $_SESSION['success'] = "A reset link has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['success'] = "If that email is in our system, a link has been sent.";
    }

    $logAction = $user["fullname"] . " Reset Password " . ucfirst($user["role"]);
    addLog($conn, $user["id"], $logAction);

    header("Location: forgotpassword.php");
    exit();
}