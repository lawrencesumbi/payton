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
$allowed_pages = ['dashboard', 'manage_groups', 'manage_expenses', 'manage_payments', 'scheduler', 'manage_reminders', 'notifications', 'my_account', 'split_expense', 'view_split_expense', 'friends', 'archive', 'activity_logs'];

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
  <title>Spender Dashboard - Payton</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* ===== THEME VARIABLES ===== */
    :root {
      --bg-body: #fcfcfc;
      --bg-sidebar: #ffffff;
      --bg-topbar: #ffffff;
      --bg-card: #ffffff;
      --bg-input: #f3f4f6;
      --text-main: #111827;
      --text-muted: #6b7280;
      --border-color: #eeeeee;
      --hover-bg: #f9f9f9;
      --sidebar-active: #ebe0f7;
      --accent-purple: #7f308f;
    }

    [data-theme="dark"] {
      --bg-body: #12141a;
      --bg-sidebar: #191c24;
      --bg-topbar: #191c24;
      --bg-card: #191c24;
      --bg-input: #1f2431;
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
    body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; line-height: 1.5; transition: background 0.3s ease, color 0.3s ease; }
    .app { display: flex; min-height: 100vh; }

    /* ===== SIDEBAR ===== */
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
      transition: width 0.3s ease;
    }
    
    .left-nav { display: flex; align-items: center; padding: 15px 25px; margin-bottom: 10px; gap: 12px; white-space: nowrap; }
    .left-nav img { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
    .left-nav h3 { font-size: 20px; font-weight: 700; color: #222; }

    .menu { flex: 1; display: flex; flex-direction: column; gap: 4px; padding-left: 10px; }
    .menu a { 
      text-decoration: none; padding: 12px 20px; color: #444; font-weight: 600; 
      font-size: 14px; display: flex; align-items: center; gap: 15px; transition: all 0.2s ease; 
      border-radius: 25px 0 0 25px; white-space: nowrap;
    }
    .menu a i { font-size: 18px; min-width: 25px; text-align: center; color: var(--accent-purple); }
    .menu a.active { background: var(--sidebar-active); color: var(--accent-purple) !important; }
    .menu a:hover:not(.active) { background: var(--hover-bg); color: var(--accent-purple); }

    /* ===== SIDEBAR COLLAPSED LOGIC ===== */
    .sidebar-is-collapsed .sidebar { width: 80px; }
    .sidebar-is-collapsed .sidebar .left-nav h3, 
    .sidebar-is-collapsed .sidebar .menu a span,
    .sidebar-is-collapsed .sidebar .sidebar-footer .user-name,
    .sidebar-is-collapsed .sidebar .sidebar-footer .user-email,
    .sidebar-is-collapsed .sidebar .sidebar-footer .user-role {
        display: none;
    }
    .sidebar-is-collapsed .sidebar .menu a { justify-content: center; padding: 15px; margin: 0 10px; border-radius: 10px; }
    .sidebar-is-collapsed .sidebar .menu a i { margin: 0; }
    .sidebar-is-collapsed .footer-avatar { display: block; }
    .sidebar-is-collapsed .sidebar-footer { align-items: center; padding: 15px 0; }

    /* ===== TOPBAR ===== */
    .main { flex: 1; display: flex; flex-direction: column; }
    .topbar {
      height: 64px; background: #ffffff; display: flex; justify-content: space-between;
      align-items: center; padding: 0 30px; border-bottom: 1px solid #f1f1f1;
      position: sticky; top: 0; z-index: 999;
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; color: #6b7280; font-size: 13px; }
    .topbar-left i { cursor: pointer; transition: 0.2s; }
    .topbar-left i:hover { color: #7f308f; }
    .topbar-left span.sep { color: #e5e7eb; margin: 0 4px; }
    .topbar-left span.current-page { color: #111827; font-weight: 600; }

    .topbar-right { display: flex; align-items: center; gap: 24px; }
    .header-icons { display: flex; align-items: center; gap: 18px; color: #6b7280; font-size: 16px; }

    /* ===== SEARCH BAR STYLES ===== */
    .search-wrapper {
      display: flex;
      align-items: center;
      background: #f3f4f6;
      padding: 6px 15px;
      border-radius: 20px;
      border: 1px solid transparent;
      transition: all 0.3s ease;
    }
    .search-wrapper:focus-within {
      background: #fff;
      border-color: #7f308f;
      box-shadow: 0 0 0 3px rgba(127, 48, 143, 0.1);
    }
    .search-wrapper input {
      border: none;
      background: transparent;
      outline: none;
      font-size: 14px;
      color: #374151;
      width: 140px;
      transition: width 0.3s ease;
    }
    .search-wrapper input:focus { width: 220px; }
    .search-wrapper button {
      background: none; border: none; padding: 0; cursor: pointer;
      color: #9ca3af; display: flex; align-items: center;
    }
    .search-wrapper button:hover { color: #7f308f; }

    .header-icons i { cursor: pointer; transition: 0.2s; }
    .header-icons i:hover { color: #7f308f; }

    /* ===== PROFILE DROPDOWN ===== */
    .profile-dropdown { position: relative; }
    .profile-btn {
      background: none; border: none; padding: 0; cursor: pointer;
      width: 34px; height: 34px; border-radius: 50%; background: #7f308f; 
      border: 2px solid #fff; box-shadow: 0 0 0 1px #e5e7eb; overflow: hidden;
    }
    .profile-btn img { width: 100%; height: 100%; object-fit: cover; }

    .profile-menu {
      position: absolute; top: 45px; right: 0; width: 220px; background: white; 
      border-radius: 10px; padding: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      border: 1px solid #f1f5f9; display: none; z-index: 1000;
    }
    .profile-menu.show { display: block; }
    .profile-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; text-decoration: none; font-size: 14px; color: #374151; border-radius: 8px; transition: 0.2s; }
    .profile-menu a:hover { background: #fff7ed; color: #7f308f; }
    .profile-menu a i { width: 18px; color: #94a3b8; }
    .menu-divider { height: 1px; background: #f1f5f9; margin: 6px 0; }
    .profile-menu a.danger { color: #ef4444; }

    .content { padding: 30px; }

    /* ===== SIDEBAR FOOTER ===== */
    .sidebar-footer { padding: 20px; border-top: 1px solid #f1f1f1; margin-top: auto; display: flex; flex-direction: column; gap: 4px; }
    .footer-avatar { display: none; width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #7f308f; margin: 0 auto; }
    .sidebar-footer .user-name { font-size: 14px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-footer .user-email { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-footer .user-role { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #7f308f; background: #ebe0f7; padding: 2px 8px; border-radius: 10px; width: fit-content; margin-top: 5px; }

    @media (max-width: 1000px) { 
        .sidebar { width: 80px; }
        .sidebar .left-nav h3, .sidebar .menu a span, .sidebar-footer span { display: none; }
        .sidebar .menu a { justify-content: center; padding: 15px; border-radius: 10px; margin: 0 10px; }
        .footer-avatar { display: block; }
        .search-wrapper input { width: 80px; }
    }

    /* Notification Badge */
.notif-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.notif-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: #ef4444; /* Red */
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 10px;
    border: 2px solid #fff;
    min-width: 18px;
    text-align: center;
}

    /* ===== THEME-AWARE OVERRIDES (KEEP EXISTING LAYOUT) ===== */
    .sidebar { background: var(--bg-sidebar); border-right: 1px solid var(--border-color); }
    .left-nav h3 { color: var(--text-main); }
    .menu a { color: var(--text-muted); }
    .menu a.active { background: var(--sidebar-active); color: var(--accent-purple) !important; }
    .menu a:hover:not(.active) { background: var(--hover-bg); color: var(--accent-purple); }
    .topbar { background: var(--bg-topbar); border-bottom: 1px solid var(--border-color); }
    .topbar-left { color: var(--text-muted); }
    .topbar-left span.current-page { color: var(--text-main); }
    .header-icons { color: var(--text-muted); }
    .search-wrapper { background: var(--bg-input); }
    .search-wrapper:focus-within { background: var(--bg-topbar); border-color: var(--accent-purple); box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1); }
    .search-wrapper input { color: var(--text-main); }
    .search-wrapper button { color: var(--text-muted); }
    .search-wrapper button:hover { color: var(--accent-purple); }
    .profile-btn { border: 2px solid var(--bg-topbar); box-shadow: 0 0 0 1px var(--border-color); }
    .profile-menu { background: var(--bg-sidebar); border: 1px solid var(--border-color); }
    .profile-menu a { color: var(--text-main); }
    .profile-menu a:hover { background: var(--hover-bg); color: var(--accent-purple); }
    .menu-divider { background: var(--border-color); }
    .content { background: var(--bg-card); min-height: calc(100vh - 64px); transition: background 0.3s ease; }
    .sidebar-footer { border-top: 1px solid var(--border-color); }
    .footer-avatar { border: 2px solid var(--accent-purple); }
    .sidebar-footer .user-name { color: var(--text-main); }
    .sidebar-footer .user-email { color: var(--text-muted); }
    .sidebar-footer .user-role { color: var(--accent-purple); background: var(--sidebar-active); }
    .notif-badge { border-color: var(--bg-topbar); }

  </style>
</head>
<body>

<script>
    // PRE-LOAD FIX
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
      <a href="?page=manage_expenses" class="<?= $page=='manage_expenses'?'active':'' ?>">
        <i class="fa-solid fa-file-invoice-dollar"></i> <span>Expenses</span>
      </a>
      <a href="?page=manage_payments" class="<?= $page=='manage_payments'?'active':'' ?>">
        <i class="fa-solid fa-money-bill-transfer"></i> <span>Payments</span>
      </a>
      <a href="?page=scheduler" class="<?= $page=='scheduler'?'active':'' ?>">
        <i class="fa-solid fa-calendar-days"></i> <span>Scheduler</span>
      </a>
      <a href="?page=friends" class="<?= $page=='friends'?'active':'' ?>">
        <i class="fa-solid fa-user-group"></i> <span>Friends</span>
      </a>
      <a href="?page=split_expense" class="<?= $page=='split_expense'?'active':'' ?>">
        <i class="fa-solid fa-layer-group"></i> <span>Split Expense</span>
      </a>
      <a href="?page=view_split_expense" class="<?= $page=='view_split_expense'?'active':'' ?>">
        <i class="fa-solid fa-diagram-predecessor"></i> <span>Breakdown</span>
      </a>
      <a href="?page=archive" class="<?= $page=='archive'?'active':'' ?>">
        <i class="fa-solid fa-box-archive"></i> <span>Archive</span>
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
        <i class="fa-solid fa-bars" id="sidebarToggle" style="cursor: pointer;"></i>
        <span class="sep">/</span>
        <a href="?page=dashboard" style="color: inherit; text-decoration: none;">
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

          <i class="fa-solid fa-sun" id="themeToggle" style="cursor: pointer;" title="Toggle Theme"></i>
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
            <a href="?page=my_account"><i class="fa-solid fa-user"></i> My Account</a>
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

<script>
  const profileBtn = document.getElementById("profileBtn");
  const profileMenu = document.getElementById("profileMenu");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const themeToggle = document.getElementById("themeToggle");
  const globalSearch = document.getElementById("globalSearch");

  // --- Theme Toggle ---
  themeToggle.addEventListener("click", function() {
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

  // Set correct icon on load
  if (localStorage.getItem("theme") === "dark") {
    themeToggle.classList.replace("fa-sun", "fa-moon");
  }

  // --- Sidebar Toggle ---
  sidebarToggle.addEventListener("click", function() {
    document.documentElement.classList.toggle("sidebar-is-collapsed");
    localStorage.setItem("sidebarStatus", document.documentElement.classList.contains("sidebar-is-collapsed") ? "collapsed" : "expanded");
  });

  // --- Profile Dropdown ---
  profileBtn.addEventListener("click", function(e){
    e.stopPropagation();
    profileMenu.classList.toggle("show");
  });

  document.addEventListener("click", function(){
    profileMenu.classList.remove("show");
  });

  // --- Live Table Filter (Optional) ---
  // This automatically hides table rows as you type, even before pressing Enter
  globalSearch.addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let tableRows = document.querySelectorAll(".content table tbody tr");
    
    tableRows.forEach(row => {
      let text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });
</script>

</body>
</html>