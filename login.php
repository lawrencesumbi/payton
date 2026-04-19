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
  <title>Login | Payton</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
        --primary-purple: #7f308f;
        --primary-hover: #6a257a;
        --text-dark: #1a0b22;
        --text-muted: #6b7280;
        --input-border: #e5e7eb;
    }

    * { margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif; }
    
    body {
        min-height: 100vh; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        background: linear-gradient(135deg, #f3e8ff 0%, #ffffff 100%);
        padding: 20px; 
    }

    .login-container {
        background: #fff; 
        width: 100%; 
        max-width: 900px; 
        display: flex; 
        flex-direction: column; 
        border-radius: 24px; 
        overflow: hidden; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
    }

    /* LEFT SECTION - BRANDING */
    .login-left {
        width: 100%; 
        padding: 40px 30px; 
        background: #fff;
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center;
        text-align: center;
    }

    /* ONE-LINE SMALLER HEADER */
    .login-left h1 { 
        font-size: 1.4rem; /* Smaller size */
        font-weight: 700; 
        color: var(--text-dark); 
        white-space: nowrap; /* Forces one line */
        letter-spacing: -0.5px;
    }

    /* IMAGE BLENDING EFFECT */
    .image-box {
        position: relative;
        margin: 20px 0;
        width: 100%;
        max-width: 280px;
        display: flex;
        justify-content: center;
    }

    .big-icon {
        width: 100%;
        height: auto;
        mix-blend-mode: multiply; /* Blends white background of JPG with the page */
        position: relative;
        z-index: 2;
    }

    .image-box::after {
        content: '';
        position: absolute;
        width: 70%; height: 70%;
        background: radial-gradient(circle, rgba(127, 48, 143, 0.15) 0%, transparent 70%);
        top: 15%;
        filter: blur(20px);
        z-index: 1;
    }

    .desc { font-size: 14px; color: var(--text-muted); line-height: 1.5; max-width: 280px; }

    /* RIGHT SECTION - FORM */
    .login-right {
        width: 100%; 
        padding: 40px; 
        background: #fafafa; 
        position: relative;
    }

    .form-header { margin-bottom: 25px; }
    .form-header h2 { font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 5px; }
    .form-header p { font-size: 14px; color: var(--text-muted); }
    .form-header a { color: var(--primary-purple); font-weight: 600; text-decoration: none; }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--text-dark); }
    
    .input-wrapper { position: relative; width: 100%; }
    .form-group input {
        width: 100%; 
        padding: 12px 16px; 
        border: 1.5px solid var(--input-border);
        border-radius: 12px; 
        font-size: 14px; 
        background: #fff; 
        outline: none; 
        transition: 0.2s;
    }
    .form-group input:focus { border-color: var(--primary-purple); box-shadow: 0 0 0 4px rgba(127, 48, 143, 0.08); }

    .show-hide {
        position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
        cursor: pointer; color: var(--text-muted); font-size: 14px;
    }

    .form-forgot {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 25px; font-size: 13px;
    }
    .form-forgot label { display: flex; align-items: center; gap: 6px; cursor: pointer; color: var(--text-muted); }
    .form-forgot input { accent-color: var(--primary-purple); }
    .form-forgot a { color: var(--primary-purple); font-weight: 600; text-decoration: none; }

    .login-btn {
        width: 100%; padding: 14px; border: none; border-radius: 12px;
        background: var(--primary-purple); color: #fff; font-size: 15px; font-weight: 700;
        cursor: pointer; transition: 0.3s;
    }
    .login-btn:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 10px 20px rgba(127, 48, 143, 0.2); }

    .close-btn { position: absolute; top: 20px; right: 25px; font-size: 1.5rem; color: #ccc; text-decoration: none; transition: 0.2s; }
    .close-btn:hover { color: var(--text-dark); }

    .alert { padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; }
    .error-box { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

    /* DESKTOP STYLING */
    @media (min-width: 768px) {
        .login-container { flex-direction: row; min-height: 550px; }
        .login-left { width: 45%; padding: 50px; border-right: 1px solid #f3f4f6; }
        .login-right { width: 55%; padding: 60px; }
        .login-left h1 { font-size: 1.6rem; }
    }
  </style>
</head>
<body>

<div class="login-container">
  
  <div class="login-left">
    <h1>Welcome to Payton!</h1>
    <div class="image-box">
        <img src="img/login-icon.jpg" alt="Login Icon" class="big-icon">
    </div>
    <p class="desc">Log in to your account to manage your settings and view your dashboard.</p>
  </div>

  <div class="login-right">
    <a href="index.php" class="close-btn">×</a>

    <div class="form-header">
      <h2>Guest Login</h2>
      <p>No account? <a href="register.php">Create one</a></p>
    </div>

    <?php if($error): ?>
      <div class="alert error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
      <div class="form-group">
          <label>Email Address</label>
          <div class="input-wrapper">
            <input type="text" name="email" value="<?= $_COOKIE['user_email'] ?? '' ?>" required placeholder="Enter your email">
          </div>
      </div>

      <div class="form-group">
          <label>Password</label>
          <div class="input-wrapper">
              <input type="password" name="password" id="password" required placeholder="••••••••">
              <i class="fa-solid fa-eye show-hide" id="togglePassword"></i>
          </div>
      </div>

      <div class="form-forgot">
          <label><input type="checkbox" name="remember_me" <?= isset($_COOKIE['user_email']) ? 'checked' : '' ?>> Remember me</label>
          <a href="forgotpassword.php">Forgot Password?</a>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>
  </div>
</div>

<script>
    const password = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword");

    togglePassword.addEventListener("click", () => {
        const type = password.type === "password" ? "text" : "password";
        password.type = type;
        togglePassword.classList.toggle("fa-eye");
        togglePassword.classList.toggle("fa-eye-slash");
    });
</script>
</body>
</html>