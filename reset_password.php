<?php
date_default_timezone_set('Asia/Manila');
session_start();
require_once "db.php"; // Using $conn from your db.php
require_once "log_helper.php";

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// 1. Verify Token immediately
if (empty($token)) {
    die("Invalid or missing reset token.");
}

$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("This reset link has expired or is invalid. Please request a new one.");
}

// 2. Handle Password Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    // Validation: Match
    if ($new_pass !== $confirm_pass) {
        $_SESSION['error'] = "Passwords do not match.";
    } 
    // Validation: Strength (Uppercase, Lowercase, Number, Symbol, 8+ chars)
    else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_pass)) {
        $_SESSION['error'] = "Password must be 8+ chars with Uppercase, Lowercase, Number, and Symbol.";
    } 
    else {
        // Success: Hash and Update
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $update->execute([$hashed, $user['id']]);

        $_SESSION['success'] = "Password reset successful! You can now login.";
        header("Location: login.php");
        exit();
    }

    $logAction = $user["fullname"] . " Reset Password " . ucfirst($user["role"]);
    addLog($conn, $user["id"], $logAction);

    header("Location: reset_password.php?token=" . $token);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | Payton</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    * {margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif;}
    body {min-height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(135deg,#f7f7f7,#6f47fd);}
    .reset-container {background:#fff; width:100%; max-width:400px; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.15); text-align:center;}
    h2 {font-size:24px; font-weight:800; color:#7f308f; margin-bottom:10px;}
    p {font-size:13px; color:#777; margin-bottom:20px;}
    .form-group {margin-bottom:20px; text-align:left;}
    .form-group label {display:block; margin-bottom:6px; font-size:12px; font-weight:600; color:#777;}
    .form-group input {width:100%; padding:12px; border:none; border-bottom:2px solid #ddd; outline:none; transition:0.3s;}
    .form-group input:focus {border-bottom:2px solid #7f308f;}
    .reset-btn {width:100%; padding:13px; border:none; border-radius:10px; background:#7f308f; color:#fff; font-weight:700; cursor:pointer;}
    .error-box {color:#e74c3c; font-size:12px; margin-bottom:15px;}
  </style>
</head>
<body>

<div class="reset-container">
    <h2>Set New Password</h2>
    <p>Please enter your new strong password below.</p>

    <?php if($error): ?> <div class="error-box"><?= htmlspecialchars($error) ?></div> <?php endif; ?>

    <form action="reset_password.php" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" required placeholder="Min. 8 characters">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required placeholder="Repeat password">
        </div>

        <button type="submit" class="reset-btn">Update Password</button>
    </form>
</div>

</body>
</html>