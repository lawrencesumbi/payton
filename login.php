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
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #f7f7f7, #6f47fd);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container {
      background: #fff;
      width: 100%;
      max-width: 900px;
      height: 600px;
      display: flex;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }


    .login-container h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #7f308f;
      font-weight: 800;
    }

    .login-left {
  width: 50%;
  background: #ffffff;
  color: white;
  padding: 30px;
  display: flex;
  flex-direction: column;
  justify-content: center;
 
}

.logo-area {
  display: flex;
  justify-content: flex-start;
}

.logo {
  width: 90px;
}

.icon-area {
  text-align: center;
}

.big-icon {
  width: 350px;
  height: 350px;
  margin-top: 20px;
}

.desc {
  font-size: 14px;
  line-height: 1.5;
  padding: 0 10px;
  color: #000;
}


  .login-right {
  width: 50%;
  padding: 35px;
  background: #f5f3f5;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

    .form-group {
  margin-bottom: 22px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #777;
}

.form-group input {
  width: 100%;
  padding: 10px 2px;
  border: none;
  border-bottom: 2px solid #ddd;
  font-size: 14px;
  background: transparent;
  outline: none;
  transition: 0.3s ease;
  color: #333;
}

.form-group input:focus {
  border-bottom: 2px solid #7f308f;
}
.form-group input::placeholder {
  color: #aaa;
  font-size: 13px;
}


.form-header {
  text-align: center;
  margin-bottom: 30px;
}

.form-header h2 {
  font-size: 28px;
  font-weight: 800;
  color: #222;
  margin-bottom: 6px;
}

.form-header p {
  font-size: 13px;
  color: #777;
}

.form-header a {
  color: #7f308f;
  font-weight: 700;
  text-decoration: none;
}

.form-header a:hover {
  text-decoration: underline;
}

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


    .login-btn {
  width: 100%;
  padding: 13px;
  border: none;
  border-radius: 10px;
  background: #7f308f;
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.3s ease;
  margin-top: 5px;
}

.login-btn:hover {
  background: #9357f5;
  transform: none;
}

.divider {
  margin: 25px 0 15px;
  text-align: center;
  position: relative;
}

.divider span {
  font-size: 12px;
  color: #838282;
  background: white;
  padding: 0 10px;
  position: relative;
  z-index: 2;
}

.divider::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 1px;
  background: #838282;
  z-index: 1;
}



    .close-btn {
    position: absolute;
    top: 20px;
    right: 25px;
    text-decoration: none;
    font-size: 1.5rem;
    font-weight: bold;
    color: #7f308f;
    transition: color 0.3s ease;
    }

    .close-btn:hover {
    color: #9357f5;
    }

.social-icons {
    display: flex;
    justify-content: center;
    gap: 15px; /* space between icons */
}

.social-icons a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px; 
    height: 40px;
    border-radius: 50%; /* makes it circular */
    color: white;
    font-size: 18px;
    transition: transform 0.3s, box-shadow 0.3s;
}

/* Brand Colors */
.social-icons a.google {
    background: #db4437;
}

.social-icons a.twitter {
    background: #1da1f2;
}

.social-icons a.facebook {
    background: #1877f2;
}

/* Hover effect */
.social-icons a:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}



.logo-area{ width: 120px;  display: flex; }
.logo-area img{width: 30px; height: 30px; border-radius: 10px; line spacing: 5px; }
.logo-area h3{font-size: 24px; font-weight: bold; color: #111; padding-top: 5px; }

.left-title {
  font-size: 28px;
  font-weight: 700;
  text-align: center;
  letter-spacing: 1px;
  color: #353435;
}

  </style>
</head>
<body>

  <div class="login-container">

  <!-- LEFT SECTION -->
  <div class="login-left">


    <!-- CENTER ICON + DESCRIPTION -->
    <div class="icon-area">
      <h1 class="left-title">Welcome to Payton!</h1>
      <img src="img/login-icon.jpg" alt="Login Icon" class="big-icon">
      <p class="desc">
        Welcome back! Please login to continue and access your account.
      </p>
    </div>

  </div>


  <!-- RIGHT SECTION -->
  <div class="login-right">

    <a href="index.html" class="close-btn">×</a>

    <div class="form-header">
      <h2>Welcome Guest!</h2>
      <p>Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>


    <form action="login_process.php" method="POST">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" name="email" id="email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>

      <div class="form-forgot">
        <label>
          <input type="checkbox" name="remember_me">
          Remember me
        </label>
        <p><a href="forgotpassword.php">Forgot Password?</a></p>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>

<div class="divider">
  <span>Or sign up with</span>
</div>

<div class="social-icons">
    <a href="#" class="google" aria-label="Google"><i class="fab fa-google"></i></a>
    <a href="#" class="twitter" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
    <a href="#" class="facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
</div>


  </div>

</div>


</body>
</html>
