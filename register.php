<?php
session_start();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Payton</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-purple: #7f308f;
        --primary-hover: #6a257a;
        --text-dark: #1a0b22;
        --text-muted: #6b7280;
        --input-border: #e5e7eb;
        --bg-light: #fdfaff;
    }

    * {margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif;}
    
    body {
        min-height:100vh; 
        background: linear-gradient(135deg, #f3e8ff 0%, #ffffff 100%);
        display:flex; 
        justify-content:center; 
        align-items:center; 
        padding:20px;
    }
    
    .register-container {
        background: #fff;
        width: 100%;
        max-width: 1000px;
        min-height: 650px;
        display: flex;
        flex-direction: column;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(127, 48, 143, 0.1);
    }

    /* LEFT SECTION - FORM */
    .register-left { 
        background: #fff; 
        padding: 40px; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        width: 100%; 
        order: 2;
    }

    /* RIGHT SECTION - BRANDING */
    .register-right { 
        background: var(--bg-light); 
        padding: 40px; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center;
        text-align: center;
        width: 100%; 
        order: 1;
        border-bottom: 1px solid #f3f4f6;
    }

    .right-title { 
        font-size: 1.4rem; 
        font-weight: 700; 
        color: var(--text-dark); 
        white-space: nowrap; 
        letter-spacing: -0.5px;
        margin-bottom: 10px;
    }

    .image-box {
        position: relative;
        margin: 20px 0;
        width: 100%;
        max-width: 320px;
        display: flex;
        justify-content: center;
    }

    .big-icon {
        width: 100%;
        height: auto;
        mix-blend-mode: multiply;
        position: relative;
        z-index: 2;
    }

    .image-box::after {
        content: '';
        position: absolute;
        width: 70%; height: 70%;
        background: radial-gradient(circle, rgba(127, 48, 143, 0.15) 0%, transparent 70%);
        top: 15%;
        filter: blur(25px);
        z-index: 1;
    }

    .desc { font-size: 14px; color: var(--text-muted); line-height: 1.6; max-width: 300px; }

    /* Modern Boxed Form Elements */
    .form-header { text-align: left; margin-bottom: 30px; }
    .form-header h2 { font-size: 28px; font-weight: 800; color: var(--text-dark); margin-bottom: 5px; }
    .form-header p { font-size: 14px; color: var(--text-muted); }
    .form-header a { color: var(--primary-purple); text-decoration: none; font-weight: 700; }

    .form-group { margin-bottom: 20px; position: relative; }
    .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--text-dark); }
    
    .input-wrapper { position: relative; width: 100%; }

    .form-group input {
        width: 100%; 
        padding: 13px 15px; 
        border: 1.5px solid var(--input-border);
        border-radius: 12px; 
        font-size: 14px; 
        background: #fff; 
        outline: none; 
        transition: 0.3s;
        color: var(--text-dark);
    }

    .form-group input:focus { 
        border-color: var(--primary-purple); 
        box-shadow: 0 0 0 4px rgba(127, 48, 143, 0.08); 
    }

    .register-btn {
        width: 100%; padding: 15px; border: none; border-radius: 14px;
        background: var(--primary-purple); color: #fff; font-size: 15px; font-weight: 700;
        cursor: pointer; margin-top: 10px; transition: 0.3s;
        box-shadow: 0 6px 15px rgba(127, 48, 143, 0.2);
    }
    .register-btn:hover { background: var(--primary-hover); transform: translateY(-1px); }

    .show-hide { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted); }

    /* Preserved Logic Styles */
    .error-msg { font-size: 12px; color: #e74c3c; margin-top: 6px; display: none; font-weight: 500; }
    .input-error { border: 1.5px solid #e74c3c !important; }
    .session-error { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; text-align: center; border: 1px solid #fecaca; }

    /* Modal Styling */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26, 11, 34, 0.6); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center; }
    .verify-modal { background: #fff; padding: 40px; border-radius: 24px; width: 90%; max-width: 420px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    .verify-modal h2 { color: var(--primary-purple); margin-bottom: 12px; font-weight: 800; }
    .otp-input { width: 100%; padding: 15px; border: 2px solid var(--input-border); border-radius: 12px; font-size: 24px; text-align: center; letter-spacing: 8px; margin: 20px 0; outline: none; transition: 0.3s; }
    .verify-btn { width: 100%; padding: 14px; background: var(--primary-purple); color: #fff; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }

    @media (min-width: 768px) {
        .register-container { flex-direction: row; }
        .register-left { width: 55%; padding: 60px; order: 1; }
        .register-right { width: 45%; padding: 60px; order: 2; border-bottom: none; border-left: 1px solid #f3f4f6; }
        .right-title { font-size: 1.6rem; }
    }
</style>
</head>
<body>

<div class="register-container">

    <div class="register-left">
        <div class="form-header">
            <h2>Create Account</h2>
            <p>Already have an account? <a href="login.php">Sign In</a></p>
        </div>

        <?php if($error): ?>
            <div class="session-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="registerForm" action="register_process.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-wrapper">
                    <input type="text" name="fullname" id="fullname" placeholder="Enter your full name" required>
                </div>
                <div class="error-msg" id="fullnameError">Full name is required</div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" placeholder="example@gmail.com" required>
                </div>
                <div class="error-msg" id="emailError">Valid Gmail address is required</div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="••••••••"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.]).{8,}"
                        title="Must contain at least one uppercase, one lowercase, one number, and one special character."
                        required
                    >
                    <i class="fa-solid fa-eye show-hide" id="togglePassword"></i>
                </div>
                <div class="error-msg" id="passwordError">Password must be at least 8 characters</div>
                <small id="pass-msg" style="font-size: 11px; display: block; margin-top: 8px; color: var(--text-muted);"></small>
            </div>

            <button type="submit" class="register-btn">Create Free Account</button>
        </form>
    </div>

    <div class="register-right">
        <div class="icon-area">
            <h1 class="right-title">Welcome to Payton!</h1>
            <div class="image-box">
                <img src="img/register-icon.jpg" class="big-icon" alt="Register Icon">
            </div>
            <p class="desc">Start managing your financial budget efficiently with our professional suite of tools.</p>
        </div>
    </div>

</div>

<script>
// --- YOUR ORIGINAL LOGIC UNTOUCHED ---
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

const registerForm = document.getElementById("registerForm");
const fullnameInput = document.getElementById("fullname");
const emailInput = document.getElementById("email");
const fullnameError = document.getElementById("fullnameError");
const emailError = document.getElementById("emailError");
const passwordError = document.getElementById("passwordError");

registerForm.addEventListener("submit", function(e){
  let valid = true;
  document.querySelectorAll(".error-msg").forEach(el=>el.style.display="none");
  document.querySelectorAll("input").forEach(el=>el.classList.remove("input-error"));

  if(fullnameInput.value.trim()===""){ showError(fullnameInput, fullnameError); valid=false; }
  if(emailInput.value.trim()==="" || !emailInput.value.endsWith("@gmail.com")){
    emailError.innerText="Email must be a valid @gmail.com address";
    showError(emailInput,emailError);
    valid=false;
  }
  if(password.value.length<8){ showError(password,passwordError); valid=false; }
  if(!valid) e.preventDefault();
});

function showError(input,errorEl){ input.classList.add("input-error"); errorEl.style.display="block"; }

const passwordInput = document.getElementById('password');
const passMsg = document.getElementById('pass-msg');
const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/;

passwordInput.addEventListener('input', () => {
    if (passwordInput.value === "") {
        passMsg.innerText = "Use 8+ chars with Uppercase, Lowercase, Number, and Symbol.";
        passMsg.style.color = "#777";
    } else if (regex.test(passwordInput.value)) {
        passMsg.innerText = "Strong password! Meets all requirements.";
        passMsg.style.color = "#27ae60";
    } else {
        passMsg.innerText = "Weak: Need Uppercase, Lowercase, Number, and Symbol.";
        passMsg.style.color = "#e74c3c";
    }
});
</script>
</body>
</html>