<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
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
    background: linear-gradient(135deg, #6f47fd, #f7f7f7);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  /* MAIN CONTAINER (NOW 2 COLUMN) */
  .register-container {
    background: #fff;
    width: 100%;
    max-width: 1050px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    display: flex;
    overflow: hidden;
  }

  /* LEFT & RIGHT */
  .register-left,
  .register-right {
    flex: 1;
    padding: 45px;
  }

  /* LEFT FORM HEADER */
  .form-header h2 {
    margin-bottom: 8px;
    color: #7f308f;
    font-weight: 800;
    font-size: 28px;
  }

  .form-header p {
    font-size: 14px;
    color: #666;
    margin-bottom: 25px;
  }

  .form-header a {
    color: #7f308f;
    text-decoration: none;
    font-weight: 700;
    transition: 0.3s;
  }

  .form-header a:hover {
    color: #9357f5;
    text-decoration: underline;
  }

  /* FORM GROUP */
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

  /* REGISTER BUTTON */
  .register-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 20px;
    background: #7f308f;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
  }

  .register-btn:hover {
    background: #9357f5;
    transform: translateY(-2px);
  }

  /* CENTER DIVIDER LINE */
  .center-divider {
    width: 1px;
    background: #ddd;
    position: relative;
  }

  .center-divider span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    color: #666;
  }

  /* RIGHT ICON AREA */
  .icon-area {
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .right-title {
    font-size: 26px;
    color: #222;
    font-weight: 800;
  }

  .big-icon {
    width: 350px;
    max-width: 100%;
    margin: 0 auto;
  }

  .desc {
    font-size: 15px;
    color: #666;
    line-height: 1.6;
    max-width: 320px;
    margin: 0 auto;
  }

  /* SOCIAL DIVIDER */
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

  /* SOCIAL ICONS */
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



  /* RESPONSIVE */
  @media (max-width: 900px) {
    .register-container {
      flex-direction: column;
      max-width: 520px;
    }

    .center-divider {
      width: 100%;
      height: 1px;
    }

    .center-divider span {
      top: 50%;
      left: 50%;
    }

    .register-left,
    .register-right {
      padding: 35px;
    }
  }
</style>

</head>
<body>

  <div class="register-container">

  <!-- LEFT SECTION (FORM) -->
  <div class="register-left">

    <div class="form-header">
      <h2>Create Account</h2>
      <p>Already have an account? <a href="login.php">Sign In</a></p>
    </div>

    <form action="register_process.php" method="POST">

      <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" name="fullname" id="fullname" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>

      <button type="submit" class="register-btn">Register</button>

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


  <!-- CENTER LINE DIVIDER -->
  <div class="center-divider">
  </div>


  <!-- RIGHT SECTION (ICON + TEXT) -->
  <div class="register-right">

    <div class="icon-area">
      <h1 class="right-title">Welcome to Payton!</h1>

      <img src="img/register-icon.jpg" alt="Register Icon" class="big-icon">

      <p class="desc">
        Create your account to get started and explore Payton services.
      </p>
    </div>

  </div>

</div>

</body>
</html>
