<?php
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | Payton</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <style>
    * {margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif;}
    body {min-height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(135deg,#f7f7f7,#6f47fd);}
    .forgot-container {background:#fff; width:100%; max-width:450px; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.15); position:relative; text-align:center;}
    .forgot-container h2 {font-size:26px; font-weight:800; color:#222; margin-bottom:10px;}
    .forgot-container p {font-size:14px; color:#777; margin-bottom:25px; line-height:1.5;}
    .form-group {margin-bottom:20px; text-align:left;}
    .form-group label {display:block; margin-bottom:6px; font-size:13px; font-weight:600; color:#777;}
    .form-group input {width:100%; padding:12px; border:none; border-bottom:2px solid #ddd; font-size:14px; background:transparent; outline:none; transition:0.3s;}
    .form-group input:focus {border-bottom:2px solid #7f308f;}
    .reset-btn {width:100%; padding:13px; border:none; border-radius:10px; background:#7f308f; color:#fff; font-size:14px; font-weight:700; cursor:pointer; transition:0.3s;}
    .reset-btn:hover {background:#9357f5;}
    .back-to-login {margin-top:20px; font-size:13px;}
    .back-to-login a {color:#7f308f; font-weight:700; text-decoration:none;}
    .error-box {color:#e74c3c; font-size:13px; margin-bottom:15px;}
    .success-box {color:#27ae60; font-size:13px; margin-bottom:15px;}
    .icon-circle {width:70px; height:70px; background:#f5f3f5; border-radius:50%; display:flex; justify-content:center; align-items:center; margin:0 auto 20px auto; color:#7f308f; font-size:30px;}
  </style>
</head>
<body>

<div class="forgot-container">
    <div class="icon-circle">
        <i class="fa-solid fa-lock-open"></i>
    </div>
    
    <h2>Forgot Password?</h2>
    <p>Enter your email address and we'll send you a link to reset your password.</p>

    <?php if($error): ?> <div class="error-box"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="success-box"><?= htmlspecialchars($success) ?></div> <?php endif; ?>

    <form action="forgot_process.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="e.g. juandelacruz@gmail.com" required>
        </div>
        <button type="submit" class="reset-btn">Send Reset Link</button>
    </form>

    <div class="back-to-login">
        <a href="login.php"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
    </div>
</div>

</body>
</html>