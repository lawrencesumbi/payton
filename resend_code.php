<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

/* ===== DATABASE CONNECTION ===== */
$host = "localhost";
$dbname = "payton";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed.");
}

// Check if there is a pending registration
if (!isset($_SESSION['pending_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['pending_email'];
$new_otp = rand(100000, 999999);

// 1. Update the database with the NEW code
$update = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
$update->execute([$new_otp, $email]);

// 2. Send the new code via PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'guiansumbi@gmail.com'; 
    $mail->Password   = 'qvuq rtbg syud xwfu'; // Use your 16-char App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('guiansumbi@gmail.com', 'Payton Support');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your New Verification Code - Payton';
    $mail->Body    = "<h3>Hello,</h3>
                      <p>You requested a new verification code. Here it is:</p>
                      <h1 style='color: #7f308f;'>$new_otp</h1>
                      <p>This code replaces your previous one.</p>";

    $mail->send();

    $_SESSION["success"] = "A new code has been sent to your inbox.";
    header("Location: register.php");
    exit();

} catch (Exception $e) {
    $_SESSION["error"] = "Could not resend code. Please try again later.";
    header("Location: register.php");
    exit();
}