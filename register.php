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
<title>Register</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

<style>
/* ... same styles as before ... */
* {margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif;}
body {min-height:100vh; background:linear-gradient(135deg, #6f47fd, #f7f7f7); display:flex; justify-content:center; align-items:center; padding:20px;}
.register-container {background: #fff;
      width: 100%;
      max-width: 900px;
      height: 600px;
      display: flex;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
.register-left, .register-right {flex:1; padding:45px;justify-content:center; display:flex; flex-direction:column;}
.form-header{text-align:center;}
.form-header h2{margin-bottom:8px;color:#000;font-weight:800;font-size:28px;}
.form-header p{font-size:14px;color:#666;margin-bottom:25px;}
.form-header a{color:#7f308f;text-decoration:none;font-weight:700;}
.form-group{margin-bottom:22px;position:relative;}
.form-group label{display:block;margin-bottom:6px;font-size:13px;font-weight:600;color:#777;}
.form-group input{width:100%;padding:10px 2px;border:none;border-bottom:2px solid #ddd;font-size:14px;background:transparent;outline:none;transition:.3s;}
.form-group input:focus{border-bottom:2px solid #7f308f;}
.register-btn{width:100%;padding:12px;border:none;border-radius:20px;background:#7f308f;color:#fff;font-weight:700;cursor:pointer;margin-top:10px;}
.register-btn:hover{background:#9357f5;}
.center-divider{width:1px;background:#ddd;}
.icon-area{text-align:center;}
.right-title{font-size:26px;font-weight:800;}
.big-icon{width:300px;margin:20px auto;}
.desc{font-size:14px;color:#666;}
.error-msg{font-size:12px;color:#e74c3c;margin-top:5px; display:none;}
.input-error{border-bottom:2px solid #e74c3c !important;}
.show-hide{position:absolute; top:30px; right:10px; cursor:pointer; color:#777;}
.success-popup{display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);  color:#fff; padding:20px 30px; border-radius:10px; font-weight:700; font-size:16px; box-shadow:0 4px 15px rgba(0,0,0,0.3); z-index:999;}
.session-error{display:block; color:#e74c3c; font-size:14px; margin-bottom:15px;}
@media(max-width:900px){.register-container{flex-direction:column;}}
/* Modal Overlay */
.modal-overlay {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

/* Modal Box */
.verify-modal {
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.verify-modal h2 { color: #7f308f; margin-bottom: 15px; font-weight: 800; }
.verify-modal p { font-size: 14px; color: #666; margin-bottom: 20px; }

.otp-input {
    width: 100%;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 10px;
    font-size: 20px;
    text-align: center;
    letter-spacing: 5px;
    margin-bottom: 20px;
    outline: none;
}

.otp-input:focus { border-color: #7f308f; }

.verify-btn {
    width: 100%;
    padding: 12px;
    background: #7f308f;
    color: #fff;
    border: none;
    border-radius: 20px;
    font-weight: 700;
    cursor: pointer;
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
    <input type="text" name="fullname" id="fullname">
    <div class="error-msg" id="fullnameError">Full name is required</div>
  </div>

  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" id="email">
    <div class="error-msg" id="emailError">Valid Gmail address is required</div>
  </div>

  <div class="form-group">
    <label>Password</label>
    <input 
        type="password" 
        name="password" 
        id="password" 
        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.]).{8,}"
        title="Must contain at least one uppercase, one lowercase, one number, and one special character."
    >
    <i class="fa-solid fa-eye show-hide" id="togglePassword"></i>
    <div class="error-msg" id="passwordError">Password must be at least 8 characters</div>
  </div>

  <button type="submit" class="register-btn">Register</button>
</form>
</div>

<div class="center-divider"></div>

<div class="register-right">
  <div class="icon-area">
    <h1 class="right-title">Welcome to Payton!</h1>
    <img src="img/register-icon.jpg" class="big-icon">
    <p class="desc">Create your account to explore Payton services.</p>
  </div>
</div>

</div>

<?php if($success): ?>
<div class="success-popup" id="successPopup"><?= htmlspecialchars($success) ?></div>
<script>
  const popup = document.getElementById('successPopup');
  popup.style.display = 'block';

</script>
<?php endif; ?>

<div class="modal-overlay" id="verifyModal" style="<?= isset($_SESSION['pending_email']) ? 'display:flex;' : '' ?>">
    <div class="verify-modal">
        <h2>Verify Email</h2>
        <p>We sent a 6-digit code to <br><strong><?= htmlspecialchars($_SESSION['pending_email'] ?? '') ?></strong></p>
        
        <?php if($error): ?>
        <div class="session-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="verify_logic.php" method="POST">
            <input type="text" name="otp" class="otp-input" placeholder="000000" maxlength="6" required>
            <button type="submit" class="verify-btn">Verify Account</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 12px;">
            Didn't get a code? 
            <a href="resend_code.php" style="color: #7f308f; font-weight: 700;">Resend Code</a>
        </p>
    </div>
</div>

<script>
// Show/hide password
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

// Frontend validation
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

const resendBtn = document.querySelector('a[href="resend_code.php"]');
if (resendBtn) {
    resendBtn.addEventListener('click', function(e) {
        // Simple visual feedback that it's working
        this.innerText = "Sending...";
        this.style.pointerEvents = "none";
        this.style.color = "#ccc";
    });
}

const passwordInput = document.getElementById('password');
const passMsg = document.getElementById('pass-msg');

// Updated regex to include (?=.*[a-z]) for lowercase
const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/;

passwordInput.addEventListener('input', () => {
    if (passwordInput.value === "") {
        passMsg.innerText = "Use 8+ chars with Uppercase, Lowercase, Number, and Symbol.";
        passMsg.style.color = "#777";
    } else if (regex.test(passwordInput.value)) {
        passMsg.innerText = "Strong password! Meets all requirements.";
        passMsg.style.color = "#27ae60"; // Green
    } else {
        passMsg.innerText = "Weak: Need Uppercase, Lowercase, Number, and Symbol.";
        passMsg.style.color = "#e74c3c"; // Red
    }
});
</script>

</body>
</html>