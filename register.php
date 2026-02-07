<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #7f308f, #9357f5);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .register-container {
      background: #fff;
      width: 100%;
      max-width: 420px;
      padding: 35px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .register-container h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #7f308f;
      font-weight: 800;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      margin-bottom: 6px;
      font-size: 14px;
      font-weight: 600;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #ccc;
      font-size: 14px;
      transition: 0.3s;
    }

    .form-group input:focus {
      outline: none;
      border-color: #9357f5;
      box-shadow: 0 0 0 2px rgba(147, 87, 245, 0.2);
    }

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

    .extra-links {
      margin-top: 20px;
      text-align: center;
      font-size: 14px;
    }

    .extra-links a {
      color: #7f308f;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }

    .extra-links a:hover {
      color: #9357f5;
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="register-container">
    <h2>Create Account</h2>

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

    <div class="extra-links">
      <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>

</body>
</html>
