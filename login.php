<?php
session_start();

// Capture error or success messages from session
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    // Search for a user with this token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Log them in automatically
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        // Redirect to their dashboard
        header("Location: " . ($user['role'] ?: 'option.php'));
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <style>
    * {margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif;}
    body {min-height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(135deg,#f7f7f7,#6f47fd);}
    .login-container {background:#fff; width:100%; max-width:900px; height:600px; display:flex; border-radius:20px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.15);}
    .login-left {width:50%; background:#fff; padding:30px; display:flex; flex-direction:column; justify-content:center; text-align:center;}
    .login-left h1 {font-size:28px; font-weight:700; color:#353435; margin-bottom:10px;}
    .login-left .desc {font-size:14px; color:#000; line-height:1.5;}
    .big-icon {width:350px; height:350px; margin-top:20px;}
    .login-right {width:50%; padding:35px; background:#f5f3f5; display:flex; flex-direction:column; justify-content:center; position:relative;}
    .form-header {margin-bottom: 15px;}
    .form-header h2 {font-size:28px; font-weight:800; color:#222; text-align:center; margin-bottom:6px;}
    .form-header p {font-size:13px; color:#777; text-align:center;}
    .form-header a {color:#7f308f; font-weight:700; text-decoration:none;}
    .form-header a:hover {text-decoration:underline;}
    .form-group {margin-bottom:22px;}
    .form-group label {display:block; margin-bottom:6px; font-size:13px; font-weight:600; color:#777;}
    .form-group input {width:100%; padding:10px 2px; border:none; border-bottom:2px solid #ddd; font-size:14px; background:transparent; outline:none; color:#333;}
    .form-group input:focus {border-bottom:2px solid #7f308f;}
    .form-forgot {
      display: flex;
      margin-bottom: 30px;
    }

    .form-forgot label{
        width: 250px;
        font-size: 13px;
        color: #000000;
    }

    .form-forgot p {
      font-size: 13px;
      color: #777;
    }

    .form-forgot a {
      color: #7f308f;
      font-weight: 700;
      text-decoration: none;
    }

    .form-forgot a:hover {
      text-decoration: underline;
    }
    .login-btn {width:100%; padding:13px; border:none; border-radius:10px; background:#7f308f; color:#fff; font-size:14px; font-weight:700; cursor:pointer; margin-top:5px; transition:0.3s;}
    .login-btn:hover {background:#9357f5;}
    .close-btn {position:absolute; top:20px; right:25px; font-size:1.5rem; font-weight:bold; color:#7f308f; text-decoration:none;}
    .close-btn:hover {color:#9357f5;}
    .error-box {color:#e74c3c; padding:10px; border-radius:8px; font-size:13px; margin-bottom:5px; text-align:center;}
    .success-box {color:#27ae60; padding:10px; border-radius:8px; font-size:13px; margin-bottom:5px; text-align:center;}
    .show-hide{position:absolute; top:320px; right:45px; cursor:pointer; color:#777;}
  </style>
</head>
<body>

<div class="login-container">

  <!-- LEFT SECTION -->
  <div class="login-left">
    <h1>Welcome to Payton!</h1>
    <img src="img/login-icon.jpg" alt="Login Icon" class="big-icon">
    <p class="desc">Welcome back! Please login to continue and access your account.</p>
  </div>

  <!-- RIGHT SECTION -->
  <div class="login-right">

    <a href="index.php" class="close-btn">×</a>

    <div class="form-header">
      <h2>Welcome Guest!</h2>
      <p>Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>

    <!-- DISPLAY ERRORS / SUCCESS -->
    <?php if($error): ?>
      <div class="error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="success-box"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form action="login_process.php" method="POST">
      <div class="form-group">
          <label for="email">Email</label>
          <input type="text" name="email" id="email" 
                value="<?= $_COOKIE['user_email'] ?? '' ?>" required>
      </div>

      <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" 
                value="<?= $_COOKIE['user_password'] ?? '' ?>" required>
          <i class="fa-solid fa-eye show-hide" id="togglePassword"></i>
      </div>

      <div class="form-forgot">
          <label>
              <input type="checkbox" name="remember_me" <?= isset($_COOKIE['user_email']) ? 'checked' : '' ?>> 
              Remember me
          </label>
          <p><a href="forgotpassword.php">Forgot Password?</a></p>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

  </div>

</div>
<script>
  const password = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");
  togglePassword.addEventListener("click", ()=>{
    if(password.type === "password"){
      password.type="text";
      togglePassword.classList.replace("fa-eye","fa-eye-slash");
    } else {
      password.type="password";
      togglePassword.classList.replace("fa-eye-slash","fa-eye");
    }
  });
</script>
</body>
</html>