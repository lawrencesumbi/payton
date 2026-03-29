<?php
session_start();
$page = "Link Requests";
$_SESSION['fullname'] = $_SESSION['fullname'] ?? "Payton Spender";
$_SESSION['email'] = $_SESSION['email'] ?? "spender@email.com";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Link Requests</title>

  <style>
    /* ===== THEME VARIABLES ===== */
    :root {
        --bg-body: #f5f7fb;
        --bg-card: #ffffff;
        --text-main: #333333;
        --text-muted: #666666;
        --border-color: #e0e0e0;
        --accent-purple: #7c3aed;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --shadow: rgba(0,0,0,0.1);
    }

    [data-theme="dark"] {
        --bg-body: #12141a;
        --bg-card: #191c24;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #2a2e39;
        --accent-purple: #a855f7;
        --accent-green: #22c55e;
        --accent-red: #ef4444;
        --shadow: rgba(0,0,0,0.2);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: var(--bg-body);
      padding: 30px;
      color: var(--text-main);
      transition: background 0.3s ease;
    }
          /* --- Force Hide Scrollbar but allow scrolling --- */
      html, body {
          height: 100%;
          margin: 0;
          padding: 0;
          /* Hide for IE, Edge and Firefox */
          -ms-overflow-style: none;  
          scrollbar-width: none;  
      }

      /* Hide for Chrome, Safari and Opera */
      html::-webkit-scrollbar, 
      body::-webkit-scrollbar {
          display: none;
          width: 0 !important;
          height: 0 !important;
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
    .card {
      background: var(--bg-card);
      padding: 22px;
      border-radius: 16px;
      box-shadow: 0 4px 14px var(--shadow);
      max-width: 900px;
      margin: auto;
      transition: background 0.3s ease;
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

    /* Requests */
    .req-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      border-radius: 16px;
      border: 1px solid var(--border-color);
      margin-bottom: 12px;
      background: var(--bg-card);
      transition: background 0.3s ease;
      gap: 14px;
    }

    .req-item h4 {
      font-size: 15px;
      margin-bottom: 4px;
      color: #222;
    }

    .req-item span {
      font-size: 13px;
      color: #777;
    }

    .btns {
      display: flex;
      gap: 10px;
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-size: 13px;
      font-weight: 700;
      transition: 0.2s;
      white-space: nowrap;
    }

    .btn-accept {
      background: #7f308f;
      color: white;
    }

    .btn-accept:hover {
      background: #6a2578;
    }

    .btn-decline {
      background: #eee;
      color: #333;
    }

    .btn-decline:hover {
      background: #ddd;
    }

    /* Empty */
    .empty-box {
      margin-top: 20px;
      padding: 30px;
      text-align: center;
      border-radius: 16px;
      border: 1px dashed #ccc;
      color: #666;
      font-size: 14px;
    }
  </style>
</head>

<body>

  <header class="topbar">
    <div>
      <h1><?= ucfirst(str_replace('_',' ', $page)) ?></h1>
      <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></p>
    </div>

    <div class="profile">
      <img src="https://i.pravatar.cc/60?img=5" alt="profile" />
      <div>
        <h4><?= htmlspecialchars($_SESSION['fullname']) ?></h4>
        <span><?= htmlspecialchars($_SESSION['email']) ?></span>
      </div>
    </div>
  </header>

  <div class="card">
    <h2>Pending Link Requests</h2>
    <p>These sponsors want to link your account. Accepting will allow them to manage your budgets and monitor expenses.</p>

    <!-- SAMPLE REQUEST -->
    <div class="req-item">
      <div>
        <h4>Sponsor: Maria Santos</h4>
        <span>maria@email.com</span>
      </div>

      <div class="btns">
        <form action="#" method="POST">
          <button class="btn btn-accept" type="submit">Accept</button>
        </form>

        <form action="#" method="POST">
          <button class="btn btn-decline" type="submit">Decline</button>
        </form>
      </div>
    </div>

    <!-- If no requests -->
    <div class="empty-box">
      No link requests at the moment.
    </div>

  </div>

</body>
</html>
