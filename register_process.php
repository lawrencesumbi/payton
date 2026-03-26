<?php
session_start();

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure this path is correct based on where your vendor folder is!
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? '');
    $email    = trim($_POST["email"] ?? '');
    $pass     = $_POST["password"] ?? '';

    // 1. Basic Validation
    if (empty($fullname) || empty($email) || empty($pass)) {
        $_SESSION["error"] = "All fields are required.";
        header("Location: register.php"); exit();
    }

    // 2. NAME VALIDATION (Letters and spaces only)
    if (!preg_match("/^[a-zA-Z\s]*$/", $fullname)) {
        $_SESSION["error"] = "Full name should only contain letters and spaces.";
        header("Location: register.php");
        exit();
    }

    // 3. EMAIL FORMAT VALIDATION
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format.";
        header("Location: register.php");
        exit();
    }

    $password = $_POST["password"];

    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.]).{8,}$/';

    if (!preg_match($passwordPattern, $password)) {
        $_SESSION["error"] = "Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.";
        header("Location: register.php");
        exit();
    }

    // 2. Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $_SESSION["error"] = "Email already registered.";
        header("Location: register.php"); exit();
    }

    // 3. Hash password and Generate 6-digit OTP
    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);

    // 4. INSERT User
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, verification_code) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$fullname, $email, $hashedPassword, $otp])) {
        
        $mail = new PHPMailer(true);

        try {
            // SMTP Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'guiansumbi@gmail.com';     
            $mail->Password   = 'qvuq rtbg syud xwfu';      
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('guiansumbi@gmail.com', 'Payton Support');
            $mail->addAddress($email, $fullname);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Payton Account';
            $mail->Body    = "<h3>Hello $fullname,</h3>
                              <p>Thank you for registering. Your verification code is:</p>
                              <h1 style='color: #7f308f;'>$otp</h1>
                              <p>Enter this code on the website to activate your account.</p>";

            $mail->send();

            // Store email in session to trigger the modal in register.php
            $_SESSION['pending_email'] = $email;
            $_SESSION["success"] = "Code sent! Check your Gmail inbox.";
            
            // REDIRECT BACK TO REGISTER (where the modal lives)
            header("Location: register.php"); 
            exit();

        } catch (Exception $e) {
            // If email fails, delete the user so they aren't "locked out"
            $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
            $_SESSION["error"] = "Email could not be sent. Please check your internet or try again.";
            header("Location: register.php");
            exit();
        }
    }
} else {
    header("Location: register.php");
    exit();
}