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
$allowed_pages = ['dashboard', 'manage_members', 'manage_allowance', 'monitoring_page', 'my_account', 'archive', 'notifications', 'activity_logs'];

if (!in_array($page, $allowed_pages)) { $page = 'dashboard'; }

// Profile data fallback logic
$dbProfile = $user['profile_pic'];
if (!empty($dbProfile) && file_exists($dbProfile)) {
    $profilePath = $dbProfile;
} else {
    $profilePath = "profile/default.jpg"; 
}

$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email'] = $user['email'];

// Fetch Unread Notification Count
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
$stmtCount->execute([$id]);
$unreadCount = $stmtCount->fetchColumn();

// Get current search term for persistence
$searchTerm = $_GET['search'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sponsor Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* ===== THEME VARIABLES ===== */
    :root {
        --bg-body: #fcfcfc;
        --bg-sidebar: #ffffff;
        --bg-topbar: #ffffff;
        --bg-card: #ffffff;
        --text-main: #111827;
        --text-muted: #6b7280;
        --border-color: #eeeeee;
        --hover-bg: #f9f9f9;
        --sidebar-active: #ebe0f7;
        --accent-purple: #9f3abe;
    }

    [data-theme="dark"] {
        --bg-body: #12141a;
        --bg-sidebar: #191c24;
        --bg-topbar: #191c24;
        --bg-card: #191c24;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #2a2e39;
        --hover-bg: #242833;
        --sidebar-active: #242833;
        --accent-purple: #a855f7;
    }

    /* ADD THIS RIGHT BELOW IT */
    [data-theme="dark"] .content {
        background-color: var(--bg-body); /* Forces the 'white block' to be dark slate */
        min-height: calc(100vh - 64px);  /* Ensures it covers the whole screen */
    }

    /* ===== BASIC RESET ===== */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', Arial, sans-serif; }
    body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; line-height: 1.5; transition: background 0.3s ease; }
    .app { display: flex; min-height: 100vh; }

    /* ===== SIDEBAR ===== */
    .sidebar {
      width: 200px;
      background: var(--bg-sidebar); 
      padding: 10px 0; 
      display: flex; 
      flex-direction: column;
      height: 100vh; 
      position: sticky; 
      top: 0; 
      border-right: 1px solid var(--border-color); 
      z-index: 1000;
      transition: width 0.3s ease, background 0.3s ease;
    }
    
    .left-nav { display: flex; align-items: center; padding: 15px 25px; margin-bottom: 10px; gap: 12px; }
    .left-nav img { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
    .left-nav h3 { font-size: 20px !important; font-weight: 700 !important; color: var(--text-main) !important; margin-bottom: 0; }

    .menu { flex: 1; display: flex; flex-direction: column; gap: 4px; padding-left: 10px; }
    .menu a { 
      text-decoration: none !important; padding: 12px 20px; color: var(--text-muted); font-weight: 600; 
      font-size: 14px; display: flex; align-items: center; gap: 15px; transition: all 0.2s ease; 
      border-radius: 25px 0 0 25px; 
    }
    .menu a i { font-size: 18px; min-width: 25px; text-align: center; color: var(--accent-purple); }
    .menu a.active { background: var(--sidebar-active); color: var(--accent-purple) !important; }
    .menu a:hover:not(.active) { background: var(--hover-bg); color: var(--accent-purple); }

    /* ===== COLLAPSED STATES ===== */
    .sidebar-is-collapsed .sidebar { width: 80px; }
    .sidebar-is-collapsed .sidebar .left-nav h3, 
    .sidebar-is-collapsed .sidebar .menu a span,
    .sidebar-is-collapsed .sidebar .sidebar-footer .user-name,
    .sidebar-is-collapsed .sidebar .sidebar-footer .user-email,
    .sidebar-is-collapsed .sidebar .sidebar-footer .user-role {
        display: none;
    }
    .sidebar-is-collapsed .sidebar .menu a { justify-content: center; padding: 15px; margin: 0 10px; border-radius: 10px; }
    .sidebar-is-collapsed .footer-avatar { display: block; }
    .sidebar-is-collapsed .sidebar-footer { align-items: center; padding: 15px 0; }

    /* ===== TOPBAR ===== */
    .main { flex: 1; display: flex; flex-direction: column; }
    .topbar {
      height: 64px; background: var(--bg-topbar); display: flex; justify-content: space-between;
      align-items: center; padding: 0 30px; border-bottom: 1px solid var(--border-color);
      position: sticky; top: 0; z-index: 999;
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 13px; }
    .topbar-left i { cursor: pointer; transition: 0.2s; }
    .topbar-left a { color: inherit; text-decoration: none; display: flex; align-items: center; }
    .topbar-left a:hover { color: var(--accent-purple); }
    .topbar-left span.sep { color: var(--border-color); margin: 0 4px; }
    .topbar-left span.current-page { color: var(--text-main); font-weight: 600; }

    .topbar-right { display: flex; align-items: center; gap: 24px; }
    .header-icons { display: flex; align-items: center; gap: 18px; color: var(--text-muted); font-size: 16px; }
    .header-icons i:hover { color: var(--accent-purple); }

    /* ===== SEARCH BAR STYLES ===== */
    .search-wrapper {
      display: flex;
      align-items: center;
      background: var(--bg-input, #f3f4f6);
      padding: 6px 15px;
      border-radius: 20px;
      border: 1px solid transparent;
      transition: all 0.3s ease;
    }
    .search-wrapper:focus-within {
      background: var(--bg-topbar);
      border-color: var(--accent-purple);
      box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
    }
    .search-wrapper input {
      border: none;
      background: transparent;
      outline: none;
      font-size: 14px;
      color: var(--text-main);
      width: 140px;
      transition: width 0.3s ease;
    }
    .search-wrapper input:focus { width: 220px; }
    .search-wrapper button {
      background: none; border: none; padding: 0; cursor: pointer;
      color: var(--text-muted); display: flex; align-items: center;
    }
    .search-wrapper button:hover { color: var(--accent-purple); }

    .profile-dropdown { position: relative; }
    .profile-btn {
      background: var(--accent-purple); border: 2px solid #fff; padding: 0; cursor: pointer;
      width: 34px; height: 34px; border-radius: 50%; box-shadow: 0 0 0 1px var(--border-color); overflow: hidden;
    }
    .profile-btn img { width: 100%; height: 100%; object-fit: cover; }

    .profile-menu {
      position: absolute; top: 45px; right: 0; width: 240px; background: var(--bg-sidebar); 
      border-radius: 10px; padding: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      border: 1px solid var(--border-color); display: none; z-index: 1000;
    }
    .profile-menu.show { display: block; }
    .profile-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; text-decoration: none; font-size: 14px; color: var(--text-main); border-radius: 8px; }
    .profile-menu a:hover { background: var(--hover-bg); color: var(--accent-purple); }
    .profile-menu a i { width: 18px; color: var(--text-muted); }
    .menu-divider { height: 1px; background: var(--border-color); margin: 6px 0; }
    .profile-menu a.danger { color: #ef4444; }

    .content {
        padding: 30px;
        transition: background 0.3s ease; /* Smooth transition when toggling */
    }

    /* ===== SIDEBAR FOOTER ===== */
    .sidebar-footer { padding: 20px; border-top: 1px solid var(--border-color); margin-top: auto; display: flex; flex-direction: column; gap: 4px; }
    .footer-avatar { display: none; width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-purple); margin: 0 auto; }
    .sidebar-footer .user-name { font-size: 14px; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-footer .user-email { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-footer .user-role { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--accent-purple); background: var(--sidebar-active); padding: 2px 8px; border-radius: 10px; width: fit-content; margin-top: 5px; }

    @media (max-width: 1000px) { 
        .sidebar { width: 80px; }
        .sidebar .left-nav h3, .sidebar .menu a span, .sidebar-footer span { display: none; }
        .sidebar .menu a { justify-content: center; padding: 15px; border-radius: 10px; margin: 0 10px; }
        .footer-avatar { display: block; }
    }

    /* Notification Badge */
.notif-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.notif-badge {
    position: absolute;
    top: -6px;
    right: -10px;
    background: #ef4444; /* Bright Red */
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 10px;
    border: 2px solid var(--bg-topbar); /* Matches theme background */
    min-width: 18px;
    text-align: center;
    line-height: 1;
}

  </style>
</head>
<body>

<script>
    // PRE-LOAD CHECK: Prevent "flashes" of light/large sidebar
    if (localStorage.getItem("sidebarStatus") === "collapsed") {
        document.documentElement.classList.add('sidebar-is-collapsed');
    }
    if (localStorage.getItem("theme") === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
    }
</script>

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
      <a href="?page=archive" class="<?= $page=='archive'?'active':'' ?>">
        <i class="fa-solid fa-box-archive"></i> <span>Archive</span>
      </a>
      <a href="?page=monitoring_page" class="<?= $page=='monitoring_page'?'active':'' ?>">
        <i class="fa-solid fa-chart-line"></i> <span>Monitoring</span>
      </a>
    </nav>
    <div class="sidebar-footer">
        <img src="<?= htmlspecialchars($profilePath) ?>" class="footer-avatar" alt="User Profile">
        <span class="user-name"><?= htmlspecialchars($user['fullname']) ?></span>
        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
        <span class="user-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <div class="topbar-left">
        <i class="fa-solid fa-bars" id="sidebarToggle" title="Toggle Sidebar"></i>
        <span class="sep">/</span>
        <a href="?page=dashboard">
            <i class="fa-solid fa-house"></i>
        </a>
        <span class="sep">/</span>
        <span class="current-page"><?= ucwords(str_replace('_',' ', $page)) ?></span>
      </div>

      <div class="topbar-right">
        <div class="header-icons">
          <form action="" method="GET" class="search-wrapper" id="searchForm">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <input type="text" name="search" id="globalSearch" placeholder="Search <?= str_replace('_',' ', $page) ?>..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </form>

          <i class="fa-solid fa-sun" id="themeToggle" style="cursor: pointer;"></i>
          <a href="?page=notifications" class="notif-wrapper" style="color: inherit; text-decoration: none;">
              <i class="fa-solid fa-bell"></i>
              <?php if ($unreadCount > 0): ?>
                  <span class="notif-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
              <?php endif; ?>
          </a>
        </div>

        <div class="profile-dropdown">
          <button class="profile-btn" id="profileBtn">
            <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile">
          </button>
          <div class="profile-menu" id="profileMenu">
            <a href="?page=my_account"><i class="fa-solid fa-gears"></i> My Account</a>
            <a href="?page=activity_logs"><i class="fa-solid fa-clock-rotate-left"></i> Activity Logs</a>
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
  const sidebarToggle = document.getElementById("sidebarToggle");
  const themeToggle = document.getElementById("themeToggle");

  // --- Theme Toggle Logic ---
  themeToggle.addEventListener("click", () => {
    const isDark = document.documentElement.getAttribute("data-theme") === "dark";
    if (isDark) {
        document.documentElement.removeAttribute("data-theme");
        localStorage.setItem("theme", "light");
        themeToggle.classList.replace("fa-moon", "fa-sun");
    } else {
        document.documentElement.setAttribute("data-theme", "dark");
        localStorage.setItem("theme", "dark");
        themeToggle.classList.replace("fa-sun", "fa-moon");
    }
  });

  // Init theme icon on load
  if (localStorage.getItem("theme") === "dark") {
      themeToggle.classList.replace("fa-sun", "fa-moon");
  }

  // --- Sidebar Toggle Logic ---
  sidebarToggle.addEventListener("click", function() {
    document.documentElement.classList.toggle("sidebar-is-collapsed");
    localStorage.setItem("sidebarStatus", 
        document.documentElement.classList.contains("sidebar-is-collapsed") ? "collapsed" : "expanded"
    );
  });

  // --- Profile Dropdown Logic ---
  profileBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    profileMenu.classList.toggle("show");
  });

  document.addEventListener("click", () => profileMenu.classList.remove("show"));
</script>

</body>
</html>