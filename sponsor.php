<?php
require 'db.php';
session_start();

// Role protection
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'sponsor') {
    header("Location: login.php");
    exit;
}

// Get user
$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_pic, fullname, email FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Page routing
$page = $_GET['page'] ?? 'dashboard';
$allowed_pages = ['dashboard', 'manage_members', 'manage_allowance', 'monitoring_page', 'my_account'];

if (!in_array($page, $allowed_pages)) { $page = 'dashboard'; }

// Profile data
$profilePath = $user['profile_pic'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email'] = $user['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sponsor Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* ===== BASIC RESET ===== */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', Arial, sans-serif; }
    body { 
        background: #fcfcfc; 
        min-height: 100vh;
        /* Ensure Bootstrap doesn't add extra line-height or font-size shifts */
        line-height: 1.5; 
    }
    .app { display: flex; min-height: 100vh; }

    /* ===== SIDEBAR (FIXED) ===== */
    .sidebar {
      width: 200px;
      background: white; 
      padding: 10px 0; 
      display: flex; 
      flex-direction: column;
      height: 100vh; 
      position: sticky; 
      top: 0; 
      border-right: 1px solid #eee; 
      z-index: 1000;
    }
    
    .left-nav { 
      display: flex; 
      align-items: center; 
      padding: 15px 25px; 
      margin-bottom: 10px; 
      gap: 12px; 
    }
    
    .left-nav img { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
    .left-nav h3 { 
        font-size: 20px !important; 
        font-weight: 700 !important; 
        color: #222 !important; 
        margin-bottom: 0; /* Bootstrap adds bottom margins to headers */
    }

    .menu { flex: 1; display: flex; flex-direction: column; gap: 4px; padding-left: 10px; }
    
    .menu a { 
      text-decoration: none !important; 
      padding: 12px 20px; 
      color: #444; 
      font-weight: 600; 
      font-size: 14px; 
      display: flex; 
      align-items: center; 
      gap: 15px; 
      transition: all 0.2s ease; 
      border-radius: 25px 0 0 25px; 
    }
    
    .menu a i { font-size: 18px; min-width: 25px; text-align: center; color: #7f308f; }
    .menu a.active { background: #ebe0f7; color: #7f308f !important; }
    .menu a:hover:not(.active) { background: #f9f9f9; color: #7f308f; }

    /* ===== TOPBAR (TUNA BAY STYLE) ===== */
    .main { flex: 1; display: flex; flex-direction: column; }

    .topbar {
      height: 64px;
      background: #ffffff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 30px;
      border-bottom: 1px solid #f1f1f1;
      position: sticky;
      top: 0;
      z-index: 999;
    }

    .topbar-left { display: flex; align-items: center; gap: 12px; color: #6b7280; font-size: 13px; }
    .topbar-left i { cursor: pointer; transition: 0.2s; }
    .topbar-left span.sep { color: #e5e7eb; margin: 0 4px; }
    .topbar-left span.current-page { color: #111827; font-weight: 600; }

    .topbar-right { display: flex; align-items: center; gap: 24px; }

    .header-icons { display: flex; align-items: center; gap: 18px; color: #6b7280; font-size: 16px; }
    .header-icons i { cursor: pointer; }

    .profile-dropdown { position: relative; }
    .profile-btn {
      background: #7f308f; border: 2px solid #fff; padding: 0; cursor: pointer;
      width: 34px; height: 34px; border-radius: 50%;
      box-shadow: 0 0 0 1px #e5e7eb; overflow: hidden;
    }
    .profile-btn img { width: 100%; height: 100%; object-fit: cover; }

    .profile-menu {
      position: absolute; top: 45px; right: 0; width: 240px;
      background: white; border-radius: 10px; padding: 8px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      border: 1px solid #f1f5f9; display: none; z-index: 1000;
    }
    .profile-menu.show { display: block; }
    .profile-menu a {
      display: flex; align-items: center; gap: 10px; padding: 10px 12px;
      text-decoration: none; font-size: 14px; color: #374151; border-radius: 8px;
    }
    .profile-menu a:hover { background: #fff7ed; color: #7f308f; }
    .profile-menu a i { width: 18px; color: #94a3b8; }
    .menu-divider { height: 1px; background: #f1f5f9; margin: 6px 0; }
    .profile-menu a.danger { color: #ef4444; }

    .content { padding: 30px; }

    @media (max-width: 1000px) { 
        .sidebar { width: 80px; }
        .sidebar .left-nav h3, .sidebar .menu a span { display: none; }
        .sidebar .menu a { justify-content: center; padding: 15px; border-radius: 10px; margin: 0 10px; }
    }
  </style>
</head>
<body>

<div class="app">
  <aside class="sidebar">
    <div class="left-nav">
      <img src="img/logo.jpg" alt="logo">
      <h3>payton</h3>
    </div>
    <nav class="menu">
      <a href="?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>">
        <i class="fa-solid fa-house"></i> <span>Dashboard</span>
      </a>
      <a href="?page=manage_members" class="<?= $page=='manage_members'?'active':'' ?>">
        <i class="fa-solid fa-users"></i> <span>Members</span>
      </a>
      <a href="?page=manage_allowance" class="<?= $page=='manage_allowance'?'active':'' ?>">
        <i class="fa-solid fa-wallet"></i> <span>Allowance</span>
      </a>
      <a href="?page=monitoring_page" class="<?= $page=='monitoring_page'?'active':'' ?>">
        <i class="fa-solid fa-chart-line"></i> <span>Monitoring</span>
      </a>
    </nav>
  </aside>

  <main class="main">
    <header class="topbar">
      <div class="topbar-left">
        <i class="fa-solid fa-desktop"></i>
        <span class="sep">/</span>
        <i class="fa-solid fa-user-tie"></i>
        <span class="sep">/</span>
        <span class="current-page"><?= ucwords(str_replace('_',' ', $page)) ?></span>
      </div>

      <div class="topbar-right">
        <div class="header-icons">
          <i class="fa-solid fa-magnifying-glass"></i>
          <i class="fa-solid fa-bell"></i>
        </div>

        <div class="profile-dropdown">
          <button class="profile-btn" id="profileBtn">
            <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
          </button>

          <div class="profile-menu" id="profileMenu">
            <a href="create_group.php"><i class="fa-solid fa-users-rectangle"></i> Create Group</a>
            <a href="sponsor_linked.php"><i class="fa-solid fa-link"></i> Linked Accounts</a>
            <a href="?page=my_account"><i class="fa-solid fa-gears"></i> My Account</a>
            <a href="activity_logs.php"><i class="fa-solid fa-clock-rotate-left"></i> Activity Logs</a>
            <div class="menu-divider"></div>
            <a href="logout.php" class="danger" onclick="return confirm('Logout?');">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </header>

    <section class="content">
      <?php
        $file = "sponsor_pages/{$page}.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<h3>Page not found</h3><p>The requested module could not be loaded.</p>";
        }
      ?>
    </section>
  </main>
</div>

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

</body>
</html>