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





// Example fetching from DB
$userId = $_SESSION['user_id'];
$query = "SELECT profile_pic, fullname, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$userId]);
$user = $stmt->fetch();

$profilePath = $user['profile_pic']; // this goes into our check above
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email'] = $user['email'];





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
      width: 200px;
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
      padding: 10px;
    }

    /* TOPBAR */
   /* TOPBAR */
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.topbar-left h1 {
  font-size: 26px;
  color: #222;
}

.topbar-left p {  
  margin-top: 4px;
  color: #777;
  font-size: 14px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

/* Search bar */
.search-form {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f2f4f8;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
}

.search-form i {
  color: #777;
  font-size: 14px;
}

.search-form input {
  border: none;
  outline: none;
  background: transparent;
  width: 180px;
  font-size: 14px;
  color: #333;
}

/* Icons */
.icon-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #f2f4f8;
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  position: relative;
  transition: 0.2s ease;
}

.icon-btn i {
  font-size: 16px;
  color: #444;
}

.icon-btn:hover {
  background: #eaf2ff;
  border-color: #cfe2ff;
}

.icon-btn:hover i {
  color: #1d4ed8;
}

/* Notification badge */
.notif-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ef4444;
  color: white;
  font-size: 11px;
  padding: 3px 6px;
  border-radius: 50px;
  font-weight: 700;
}

/* Profile */
.profile {
  display: flex;
  align-items: center;
  gap: 10px;
  padding-left: 8px;
}

.profile img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-info h4 {
  font-size: 14px;
  margin: 0;
  font-weight: 700;
  color: #222;
}

.profile-info span {
  font-size: 12px;
  color: #777;
}


    .navbar { width: 100%; background: white; display: flex;box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.08); position: fixed; top: 0;  left: 0;  z-index: 999;}
    .left-nav{ display: flex; margin-bottom: 20px;}
    .left-nav img{width: 50px; height: 50px; border-radius: 10px;}
    .left-nav h3{padding-left: 10px; font-size: 25px; font-weight: bold; color: #111; padding-top: 10px;}

    .profile {
  display: flex;
  align-items: center;
  gap: 10px;
  background: white;
  padding: 8px 12px;
  border-radius: 18px;
  box-shadow: 0 6px 18px rgba(253, 76, 224, 0.06);
}

.profile img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
}

.profile h4 {
  font-size: 13px;
  font-weight: 800;
  color: #222;
}

.profile span {
  font-size: 12px;
  color: #777;
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
    <div class="left-nav">
      <img src="img/logo.jpg" alt="logo">
      <h3>payton</h3>
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
  <div class="topbar-left">
    <h1><?= ucfirst(str_replace('_',' ', $page)) ?></h1>
  </div>

  <div class="topbar-right">

    <!-- Search -->
    <form class="search-form" method="get" action="">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" placeholder="Search..." />
    </form>

    <!-- Home -->
    <a href="dashboard.php" class="icon-btn" title="Dashboard">
      <i class="fa-solid fa-house"></i>
    </a>

    <!-- Notifications -->
    <a href="notifications.php" class="icon-btn notif" title="Notifications">
      <i class="fa-regular fa-bell"></i>
      <span class="notif-badge">3</span>
    </a>

    <!-- Profile -->
    <div class="profile">
      <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
      <div class="profile-info">
        <h4><?= htmlspecialchars($_SESSION['fullname']) ?></h4>
        <span><?= htmlspecialchars($_SESSION['email']) ?></span>
      </div>
    </div>

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
