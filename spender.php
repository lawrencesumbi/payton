<?php
require 'db.php';
session_start();

// Role protection
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'spender') {
    header("Location: login.php");
    exit;
}

// Get user
$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Page routing
$page = $_GET['page'] ?? 'dashboard';

$allowed_pages = [
    'dashboard',
    'manage_expenses',
    'manage_payments',
    'manage_reminders',
    'my_account'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Spender Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    /* ===== BASIC RESET ===== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f5f7fb;
      min-height: 100vh;
    }

    .app {
      display: flex;
      min-height: 100vh;
    }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      background: #ffffff;
      padding: 25px 18px;
      border-right: 1px solid #eee;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 30px;
    }

    .logo {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background: #7f308f;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 18px;
    }

    .brand h2 {
      font-size: 20px;
      font-weight: 800;
      color: #222;
    }

    .menu {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .menu a {
      text-decoration: none;
      padding: 12px 14px;
      border-radius: 14px;
      color: #444;
      font-weight: 600;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: 0.25s;
    }

    .menu a:hover {
      background: #f3eaff;
      color: #7f308f;
    }

    .menu a.active {
      background: #7f308f;
      color: white;
    }

    .menu a.logout {
      margin-top: 15px;
      background: #f6f6f6;
    }

    /* MAIN */
    .main {
      flex: 1;
      padding: 30px;
    }

    /* TOPBAR */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }

    .topbar h1 {
      font-size: 26px;
      color: #222;
    }

    .topbar p {
      margin-top: 4px;
      color: #777;
      font-size: 14px;
    }

    /* RESPONSIVE */
    @media (max-width: 800px) {
      .sidebar {
        display: none;
      }
      .main {
        padding: 18px;
      }
    }
  </style>
</head>

<body>

<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="brand">
      <div class="logo"><i class="fa-solid fa-wallet"></i></div>
      <h2>Payton</h2>
    </div>

    <nav class="menu">
      <a href="?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>">
        <i class="fa-solid fa-house"></i> Dashboard
      </a>

      <a href="?page=manage_expenses" class="<?= $page=='manage_expenses'?'active':'' ?>">
        <i class="fa-solid fa-receipt"></i> Expenses
      </a>

      <a href="?page=manage_payments" class="<?= $page=='manage_payments'?'active':'' ?>">
        <i class="fa-solid fa-coins"></i> Payments
      </a>

      <a href="?page=manage_reminders" class="<?= $page=='manage_reminders'?'active':'' ?>">
        <i class="fa-solid fa-bell"></i> Reminders
      </a>

      <a href="?page=my_account" class="<?= $page=='my_account'?'active':'' ?>">
        <i class="fa-solid fa-user"></i> My Account
      </a>

      <a href="logout.php" class="logout"
         onclick="return confirm('Logout?');">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <header class="topbar">
      <div>
        <h1><?= ucfirst(str_replace('_',' ', $page)) ?></h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></p>
      </div>
    </header>

    <section class="content">
      <?php
        $file = "spender_pages/{$page}.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<p>Page not found.</p>";
        }
      ?>
    </section>

  </main>
</div>

</body>
</html>
