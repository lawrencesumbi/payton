<?php
session_start();
$page = "Link Spender";
$_SESSION['fullname'] = $_SESSION['fullname'] ?? "Payton Sponsor";
$_SESSION['email'] = $_SESSION['email'] ?? "sponsor@email.com";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Link Spender</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f5f7fb;
      padding: 30px;
    }

    /* Topbar */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }

    .topbar h1 {
      font-size: 24px;
      color: #222;
    }

    .topbar p {
      margin-top: 6px;
      color: #666;
      font-size: 14px;
    }

    .profile {
      display: flex;
      align-items: center;
      gap: 12px;
      background: white;
      padding: 10px 14px;
      border-radius: 14px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .profile img {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
    }

    .profile h4 {
      font-size: 14px;
      margin: 0;
      color: #222;
    }

    .profile span {
      font-size: 12px;
      color: #777;
    }

    /* Layout */
    .container {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 20px;
      align-items: start;
    }

    .card {
      background: white;
      padding: 22px;
      border-radius: 16px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    }

    .card h2 {
      font-size: 18px;
      margin-bottom: 8px;
      color: #222;
    }

    .card p {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      margin-bottom: 16px;
    }

    /* Form */
    .form-group {
      margin-bottom: 16px;
    }

    label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 6px;
      color: #333;
    }

    input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #ddd;
      outline: none;
      font-size: 14px;
    }

    input:focus {
      border-color: #7f308f;
      box-shadow: 0 0 0 3px rgba(127,48,143,0.12);
    }

    .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 10px;
      background: #7f308f;
      color: white;
      font-size: 14px;
      cursor: pointer;
      transition: 0.2s;
      font-weight: 600;
    }

    .btn:hover {
      background: #6a2578;
    }

    /* Tips */
    .tip {
      background: #f9f2fb;
      border: 1px solid #f1d7f6;
      padding: 14px;
      border-radius: 12px;
      font-size: 13px;
      color: #5c1c69;
      line-height: 1.5;
    }

    /* Right Side Requests */
    .req-item {
      padding: 14px;
      border: 1px solid #eee;
      border-radius: 14px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    .req-item h4 {
      font-size: 14px;
      margin-bottom: 3px;
      color: #222;
    }

    .req-item span {
      font-size: 12px;
      color: #777;
    }

    .status {
      font-size: 12px;
      font-weight: 700;
      padding: 6px 10px;
      border-radius: 999px;
      background: #fff7d6;
      color: #8a6a00;
      white-space: nowrap;
    }

    @media(max-width: 900px) {
      .container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- TOPBAR -->
  <header class="topbar">
    <div>
      <h1><?= ucfirst(str_replace('_',' ', $page)) ?></h1>
      <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></p>
    </div>

    <div class="profile">
      <img src="https://i.pravatar.cc/60?img=3" alt="profile" />
      <div>
        <h4><?= htmlspecialchars($_SESSION['fullname']) ?></h4>
        <span><?= htmlspecialchars($_SESSION['email']) ?></span>
      </div>
    </div>
  </header>

  <div class="container">

    <!-- LEFT: LINK FORM -->
    <div class="card">
      <h2>Send Link Request</h2>
      <p>Enter the spender’s email. They must accept your request before you can manage their budget.</p>

      <form action="#" method="POST">
        <div class="form-group">
          <label for="spender_email">Spender Email</label>
          <input type="email" id="spender_email" name="spender_email" placeholder="spender@email.com" required>
        </div>

        <button type="submit" class="btn">Send Request</button>
      </form>

      <div style="margin-top:16px;" class="tip">
        💡 Tip: If the spender cannot be found, make sure they already registered as a <b>Spender</b> account.
      </div>
    </div>

    <!-- RIGHT: PENDING REQUESTS -->
    <div class="card">
      <h2>Pending Requests</h2>
      <p>These are link requests you already sent.</p>

      <!-- SAMPLE PENDING ITEMS -->
      <div class="req-item">
        <div>
          <h4>Jane Smith</h4>
          <span>jane@email.com</span>
        </div>
        <div class="status">Pending</div>
      </div>

      <div class="req-item">
        <div>
          <h4>John Doe</h4>
          <span>john@email.com</span>
        </div>
        <div class="status">Pending</div>
      </div>

    </div>

  </div>

</body>
</html>
