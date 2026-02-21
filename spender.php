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
    'manage_budget',
    'manage_expenses',
    'manage_payments',
    'scheduler',
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

  /* ===== WHITE SIDEBAR: HOVER TO EXPAND ===== */
.sidebar {
  width: 90px; /* Collapsed width */
  background: #f4edf8; /* Original White Background */
  padding: 10px 0;
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: sticky;
  top: 0;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden; 
  border-right: 1px solid #eee; /* Original border */
  z-index: 1000;
}

/* Expand on Hover */
.sidebar:hover {
  width: 200px;
}

/* BRANDING (payton logo/name) */
.left-nav {
  
  display: flex;
  align-items: center;
  padding: 5px 25px;
  margin-bottom: 10px;
  gap: 10px;
  white-space: nowrap;
}

.left-nav img {
  width: 45px;
  min-width: 45px;
  height: 45px;
  border-radius: 12px;
  object-fit: cover;
}

.left-nav h3 {
  font-size: 20px;
  font-weight: 800;
  color: #222; /* Original dark text */
  opacity: 0;
  transition: opacity 0.2s;
}

.sidebar:hover .left-nav h3 {
  opacity: 1;
}

/* MENU LAYOUT */
.menu {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-left: 15px;
}

.menu a {
  text-decoration: none;
  padding: 14px 20px;
  color: #444; /* Original dark gray text */
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.2s ease;
  border-radius: 30px 0 0 30px; /* Capsule shape */
  white-space: nowrap;
}

.menu a i {
  font-size: 18px;
  min-width: 25px;
  text-align: center;
  color: #7f308f; /* Original Purple for icons */
}

/* Hide labels when collapsed */
.menu a span {
  opacity: 0;
  transition: opacity 0.2s;
}

.sidebar:hover .menu a span {
  opacity: 1;
}

/* ACTIVE STATE (Light Purple Capsule) */
.menu a.active {
  background: #ebe0f7; /* Light Purple / Lavender background */
  color: #7f308f !important; /* Original Purple text */
}

.menu a.active i {
  color: #7f308f;
}

.menu a:hover:not(.active) {
  background: #f9f9f9;
  color: #7f308f;
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

/* PROFILE DROPDOWN */
.profile-dropdown {
  position: relative;
  display: inline-block;
}

.profile-btn {
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 18px;
  box-shadow: 0 6px 18px rgba(109, 40, 217, 0.08);
}

.profile-btn img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-info h4 {
  font-size: 13px;
  font-weight: 800;
  color: #222;
  margin: 0;
  line-height: 1.2;
}

.profile-info span {
  font-size: 12px;
  color: #777;
}

.profile-btn i {
  font-size: 12px;
  color: #666;
  margin-left: 6px;
}

/* DROPDOWN MENU */
.profile-menu {
  position: absolute;
  top: 58px;
  right: 0;
  width: 230px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 18px 40px rgba(0,0,0,0.12);
  padding: 10px;
  display: none;
  z-index: 999;
  border: 1px solid #f1f5f9;
}

.profile-menu.show {
  display: block;
}

.profile-menu a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  text-decoration: none;
  font-size: 13px;
  font-weight: 700;
  color: #111827;
  transition: 0.2s ease;
}

.profile-menu a:hover {
  background: #f3f4f6;
}

.profile-menu a i {
  width: 18px;
  text-align: center;
  color: #6d28d9;
}

.profile-menu .menu-divider {
  height: 1px;
  background: #eef2ff;
  margin: 8px 0;
}

.profile-menu a.danger {
  color: #b91c1c;
}

.profile-menu a.danger i {
  color: #b91c1c;
}



    .profile {
  display: flex;
  align-items: center;
  gap: 10px;
  background: white;
  padding: 8px 12px;
  border-radius: 18px;
  box-shadow: 0 6px 18px rgba(253, 76, 224, 0.06);
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
      <i class="fa-solid fa-house"></i>
      <span>Dashboard</span>
    </a>

    <a href="?page=manage_budget" class="<?= $page=='manage_budget'?'active':'' ?>">
      <i class="fa-solid fa-wallet"></i>
      <span>Budget</span>
    </a>
    
    <a href="?page=manage_expenses" class="<?= $page=='manage_expenses'?'active':'' ?>">
      <i class="fa-solid fa-file-invoice-dollar"></i>
      <span>Expenses</span>
    </a>

    <a href="?page=manage_payments" class="<?= $page=='manage_payments'?'active':'' ?>">
      <i class="fa-solid fa-money-bill-transfer"></i>
      <span>Payments</span>
    </a>

    <a href="?page=scheduler" class="<?= $page=='scheduler'?'active':'' ?>">
      <i class="fa-solid fa-calendar-days"></i>
      <span>Scheduler</span>
    </a>
  </nav>
</aside>
  <!-- MAIN -->
  <main class="main">

    <header class="topbar">
  <div class="topbar-left">
    <h1><?= ucwords(str_replace('_',' ', $page)) ?></h1>
  </div>

  <div class="topbar-right">

    <!-- Search -->
    <form class="search-form" method="get" action="">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" placeholder="Search..." />
    </form>

    <!-- Home -->
    <a href="spender.php?page=dashboard" class="icon-btn" title="Dashboard">
      <i class="fa-solid fa-house"></i>
    </a>

    <!-- Notifications -->
    <a href="notifications.php" class="icon-btn notif" title="Notifications">
      <i class="fa-regular fa-bell"></i>
      <span class="notif-badge">3</span>
    </a>

    <!-- Profile -->
    <div class="profile-dropdown">
  <button class="profile-btn" id="profileBtn">
    <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">

    <div class="profile-info">
      <h4><?= htmlspecialchars($_SESSION['fullname']) ?></h4>
      <span><?= htmlspecialchars($_SESSION['role']) ?></span>
    </div>

    <i class="fa-solid fa-chevron-down"></i>
  </button>

  <div class="profile-menu" id="profileMenu">
    <a href="create_group.php">
      <i class="fa-solid fa-users"></i> Create Group
    </a>

    <a href="sponsor_linked.php">
      <i class="fa-solid fa-hand-holding-dollar"></i> Sponsor Linked Account
    </a>

    <a href="?page=my_account">
      <i class="fa-solid fa-user"></i> My Account
    </a>

    <a href="activity_logs.php">
      <i class="fa-solid fa-clock-rotate-left"></i> Activity Logs
    </a>

    <div class="menu-divider"></div>

    <a href="logout.php" class="danger"
       onclick="return confirm('Logout?');">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
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
  <script>
  const profileBtn = document.getElementById("profileBtn");
  const profileMenu = document.getElementById("profileMenu");

  profileBtn.addEventListener("click", function(e){
    e.stopPropagation();
    profileMenu.classList.toggle("show");
  });

  document.addEventListener("click", function(){
    profileMenu.classList.remove("show");
  });
</script>


</html>
