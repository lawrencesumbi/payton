<?php
session_start();
// Include your DB connection here
require 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_otp = $_POST['otp'] ?? '';
    $email = $_SESSION['pending_email'] ?? '';

    $stmt = $conn->prepare("SELECT verification_code FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['verification_code'] === $user_otp) {
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?");
        $update->execute([$email]);
        
        unset($_SESSION['pending_email']);
        $_SESSION['success'] = "Verification successful! You can now login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid verification code. Please try again.";
        header("Location: register.php");
        exit();
    }
}