<?php
require 'db.php';
session_start();

// 1. ROLE PROTECTION (Admin only)
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_pic, fullname, email FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. ADMIN PAGE ROUTING
$page = $_GET['page'] ?? 'admin_dashboard';
$allowed_pages = [
    'admin_dashboard', 
    'admin_user_management', 
    'admin_relationship_management', 
    'admin_scheduler_oversight', 
    'admin_expense_transaction_monitoring', 
    'admin_activity_logs',
    'admin_notifications',
    'admin_my_account'
];

if (!in_array($page, $allowed_pages)) { $page = 'admin_dashboard'; }

// Profile fallback
$dbProfile = $user['profile_pic'];
$profilePath = (!empty($dbProfile) && file_exists($dbProfile)) ? $dbProfile : "profile/default.jpg";

$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email'] = $user['email'];

// Unread Notifications
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND status = 'unread'");
$stmtCount->execute([$id]);
$unreadCount = $stmtCount->fetchColumn();

$searchTerm = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* All CSS remains identical to the Sponsor dashboard */
        :root {
            --bg-body: #fcfcfc;
            --bg-sidebar: #ffffff;
            --bg-topbar: #ffffff;
            --bg-card: #ffffff;
            --bg-input: #ffffff;
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
            --bg-input: #1f2431;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #2a2e39;
            --hover-bg: #242833;
            --sidebar-active: #242833;
            --accent-purple: #a855f7;
        }

        [data-theme="dark"] .content { background-color: var(--bg-body); min-height: calc(100vh - 64px); }

        /* Dark mode specific styles */
        [data-theme="dark"] .search-wrapper { background: var(--bg-input); }
        [data-theme="dark"] .search-wrapper:focus-within { background: var(--bg-topbar); border-color: var(--accent-purple); box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1); }
        [data-theme="dark"] .search-wrapper input { color: var(--text-main); }
        [data-theme="dark"] .search-wrapper button { color: var(--text-muted); }
        [data-theme="dark"] .search-wrapper button:hover { color: var(--accent-purple); }

        /* Table dark mode styles */
        [data-theme="dark"] .content table { color: var(--text-main); border-color: var(--border-color); }
        [data-theme="dark"] .content thead th { background: var(--bg-topbar); color: var(--text-muted); border-color: var(--border-color); }
        [data-theme="dark"] .content tbody td { color: var(--text-main); border-color: var(--border-color); }
        [data-theme="dark"] .content tbody tr:hover { background: var(--hover-bg); }

        /* Form dark mode styles */
        [data-theme="dark"] .content input[type="text"], 
        [data-theme="dark"] .content input[type="email"], 
        [data-theme="dark"] .content input[type="password"], 
        [data-theme="dark"] .content input[type="date"], 
        [data-theme="dark"] .content select, 
        [data-theme="dark"] .content textarea { 
            background: var(--bg-input); 
            color: var(--text-main); 
            border-color: var(--border-color); 
        }
        [data-theme="dark"] .content input:focus, 
        [data-theme="dark"] .content select:focus, 
        [data-theme="dark"] .content textarea:focus { 
            border-color: var(--accent-purple); 
            box-shadow: 0 0 0 4px rgba(124,58,237,0.10); 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', Arial, sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); min-height: 100vh; line-height: 1.5; transition: background 0.3s ease; }
        .app { display: flex; min-height: 100vh; }

        .sidebar {
          width: 200px; background: var(--bg-sidebar); padding: 10px 0; display: flex; flex-direction: column;
          height: 100vh; position: sticky; top: 0; border-right: 1px solid var(--border-color); z-index: 1000;
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

        .sidebar-is-collapsed .sidebar { width: 80px; }
        .sidebar-is-collapsed .sidebar .left-nav h3, 
        .sidebar-is-collapsed .sidebar .menu a span,
        .sidebar-is-collapsed .sidebar .sidebar-footer .user-name,
        .sidebar-is-collapsed .sidebar .sidebar-footer .user-email,
        .sidebar-is-collapsed .sidebar .sidebar-footer .user-role { display: none; }

        .main { flex: 1; display: flex; flex-direction: column; }
        .topbar {
          height: 64px; background: var(--bg-topbar); display: flex; justify-content: space-between;
          align-items: center; padding: 0 30px; border-bottom: 1px solid var(--border-color);
          position: sticky; top: 0; z-index: 999;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; color: var(--text-muted); font-size: 13px; }
        .topbar-right { display: flex; align-items: center; gap: 24px; }
        .header-icons { display: flex; align-items: center; gap: 18px; color: var(--text-muted); }

        .search-wrapper { display: flex; align-items: center; background: var(--bg-input); padding: 6px 15px; border-radius: 20px; border: 1px solid var(--border-color); transition: all 0.3s ease; }
        .search-wrapper:focus-within { background: var(--bg-topbar); border-color: var(--accent-purple); box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1); }
        .search-wrapper input { border: none; background: transparent; outline: none; font-size: 14px; color: var(--text-main); width: 140px; transition: width 0.3s ease; }
        .search-wrapper input:focus { width: 220px; }
        .search-wrapper button { background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; transition: color 0.2s; }
        .search-wrapper button:hover { color: var(--accent-purple); }

        .profile-btn { background: var(--accent-purple); border: 2px solid #fff; width: 34px; height: 34px; border-radius: 50%; overflow: hidden; cursor: pointer; }
        .profile-btn img { width: 100%; height: 100%; object-fit: cover; }

        .profile-menu {
          position: absolute; top: 45px; right: 0; width: 240px; background: var(--bg-sidebar); 
          border-radius: 10px; padding: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
          border: 1px solid var(--border-color); display: none; z-index: 1000;
        }
        .profile-menu.show { display: block; }
        .profile-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; text-decoration: none; font-size: 14px; color: var(--text-main); border-radius: 8px; }
        .profile-menu a:hover { background: var(--hover-bg); color: var(--accent-purple); }

        html, body { height: 100%; scrollbar-width: none; -ms-overflow-style: none; }
        html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; width: 0; height: 0; }

        .content { padding: 30px; min-height: calc(100vh - 64px); transition: background 0.3s ease; }

        .content h2, .content h3 { color: var(--text-main); margin-bottom: 20px; font-weight: 700; }
        .content p { color: var(--text-muted); }

        .section-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 24px; padding: 24px; box-shadow: 0 16px 40px rgba(15,23,42,0.08); margin-bottom: 24px; }
        .section-card.small { padding: 18px; }
        .content .table-wrapper { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 24px; overflow: hidden; box-shadow: 0 14px 34px rgba(15,23,42,0.06); margin-bottom: 24px; }
        .content table { width: 100%; border-collapse: collapse; min-width: 640px; }
        .content thead th { padding: 16px 18px; text-align: left; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; font-size: 12px; border-bottom: 1px solid var(--border-color); background: var(--bg-topbar); }
        .content tbody td { padding: 16px 18px; border-bottom: 1px solid var(--border-color); color: var(--text-main); }
        .content tbody tr:hover { background: var(--hover-bg); }
        .content a { color: var(--accent-purple); text-decoration: none; }
        .content a:hover { text-decoration: underline; }
        .content button, .content .btn, .content input[type="submit"] { border: none; border-radius: 12px; padding: 10px 18px; background: var(--accent-purple); color: #fff; font-weight: 700; cursor: pointer; transition: background 0.25s ease, transform 0.25s ease; }
        .content button:hover, .content .btn:hover, .content input[type="submit"]:hover { background: #8f2fd4; transform: translateY(-1px); }
        .content .danger { background: #ef4444; }
        .content .danger:hover { background: #dc2626; }
        .content label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; }
        .content input[type="text"], .content input[type="email"], .content input[type="password"], .content input[type="date"], .content select, .content textarea { width: 100%; padding: 12px 16px; border-radius: 14px; border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-main); font-size: 14px; margin-bottom: 16px; transition: border-color .25s ease, box-shadow .25s ease; }
        .content input:focus, .content select:focus, .content textarea:focus { outline: none; border-color: var(--accent-purple); box-shadow: 0 0 0 4px rgba(124,58,237,0.10); }
        .content .modal-content { border-radius: 20px; padding: 24px; }
        .content .modal .close { color: var(--text-muted); }
        .content .modal .close:hover { color: var(--text-main); }

        .sidebar-footer { padding: 20px; border-top: 1px solid var(--border-color); margin-top: auto; display: flex; flex-direction: column; gap: 4px; }
        .user-role { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--accent-purple); background: var(--sidebar-active); padding: 2px 8px; border-radius: 10px; width: fit-content; }

        .notif-badge { position: absolute; top: -6px; right: -10px; background: #ef4444; color: white; font-size: 10px; font-weight: 700; padding: 2px 5px; border-radius: 10px; border: 2px solid var(--bg-topbar); }

        /* Mobile Optimized Queries (Identical to your provided code) */
        @media (max-width: 768px) {
            .app { flex-direction: column; }
            .sidebar {
                width: 100% !important; height: 65px !important; position: fixed; bottom: 0; top: auto;
                flex-direction: row !important; padding: 0 !important; z-index: 2000;
            }
            .left-nav, .sidebar-footer { display: none !important; }
            .menu { flex-direction: row !important; padding: 0 !important; }
            .menu a { flex-direction: column !important; padding: 8px 0 !important; font-size: 10px !important; flex: 1; border-radius: 0 !important; }
            .menu a span { display: block !important; }
            .menu a.active { border-top: 3px solid var(--accent-purple); margin-top: -3px; }
            .main { margin-bottom: 65px; }
            .topbar { padding: 0 10px; }
            .search-wrapper input { width: 60px; }
            .search-wrapper input:focus { width: 110px; }
        }
    </style>
</head>
<body>

<script>
    if (localStorage.getItem("sidebarStatus") === "collapsed") { document.documentElement.classList.add('sidebar-is-collapsed'); }
    if (localStorage.getItem("theme") === "dark") { document.documentElement.setAttribute("data-theme", "dark"); }
</script>

<div class="app">
  <aside class="sidebar">
    <div class="left-nav">
      <img src="img/logo.jpg" alt="logo">
      <h3>payton</h3>
    </div>
    <nav class="menu">
      <a href="?page=admin_dashboard" class="<?= $page=='admin_dashboard'?'active':'' ?>">
        <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
      </a>
      <a href="?page=admin_user_management" class="<?= $page=='admin_user_management'?'active':'' ?>">
        <i class="fa-solid fa-user-shield"></i> <span>Users</span>
      </a>
      <a href="?page=admin_relationship_management" class="<?= $page=='admin_relationship_management'?'active':'' ?>">
        <i class="fa-solid fa-people-group"></i> <span>Relationships</span>
      </a>
      <a href="?page=admin_scheduler_oversight" class="<?= $page=='admin_scheduler_oversight'?'active':'' ?>">
        <i class="fa-solid fa-calendar-check"></i> <span>Scheduler</span>
      </a>
      <a href="?page=admin_expense_transaction_monitoring" class="<?= $page=='admin_expense_transaction_monitoring'?'active':'' ?>">
        <i class="fa-solid fa-chart-pie"></i> <span>Expenses</span>
      </a>
      <a href="?page=admin_activity_logs" class="<?= $page=='admin_activity_logs'?'active':'' ?>">
        <i class="fa-solid fa-clock-rotate-left"></i> <span>Logs</span>
      </a>
    </nav>
    <div class="sidebar-footer">
        <span class="user-name"><?= htmlspecialchars($user['fullname']) ?></span>
        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
        <span class="user-role">System Admin</span>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <div class="topbar-left">
        <i class="fa-solid fa-bars" id="sidebarToggle"></i>
        <span class="sep">/</span>
        <a href="?page=admin_dashboard"><i class="fa-solid fa-house"></i></a>
        <span class="sep">/</span>
        <span class="current-page"><?= ucwords(str_replace(['admin_', '_'], ['', ' '], $page)) ?></span>
      </div>

      <div class="topbar-right">
        <div class="header-icons">
          <form action="" method="GET" class="search-wrapper">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--text-muted);">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </form>
          <i class="fa-solid fa-sun" id="themeToggle" style="cursor: pointer;"></i>
          <a href="?page=admin_notifications" class="notif-wrapper" style="color: inherit; position:relative;">
              <i class="fa-solid fa-bell"></i>
              <?php if ($unreadCount > 0): ?><span class="notif-badge"><?= $unreadCount ?></span><?php endif; ?>
          </a>
        </div>
        <div class="profile-dropdown">
          <button class="profile-btn" id="profileBtn"><img src="<?= htmlspecialchars($profilePath) ?>"></button>
          <div class="profile-menu" id="profileMenu">
            <a href="?page=admin_my_account"><i class="fa-solid fa-gears"></i> Account</a>
            <div class="menu-divider" style="height:1px; background:var(--border-color); margin:5px 0;"></div>
            <a href="logout.php" class="danger" style="color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
          </div>
        </div>
      </div>
    </header>

    <section class="content">
      <?php
        $file = "admin_pages/{$page}.php";
        if (file_exists($file)) { include $file; } 
        else { echo "<h3>Module Missing</h3><p>Please create <b>admin_pages/{$page}.php</b></p>"; }
      ?>
    </section>
  </main>
</div>

<script>
  // Theme, Sidebar, and Dropdown JS (Same as Sponsor)
  const themeToggle = document.getElementById("themeToggle");
  themeToggle.addEventListener("click", () => {
    const isDark = document.documentElement.getAttribute("data-theme") === "dark";
    document.documentElement.setAttribute("data-theme", isDark ? "light" : "dark");
    localStorage.setItem("theme", isDark ? "light" : "dark");
    themeToggle.classList.toggle("fa-moon");
    themeToggle.classList.toggle("fa-sun");
  });

  document.getElementById("sidebarToggle").addEventListener("click", () => {
    document.documentElement.classList.toggle("sidebar-is-collapsed");
    localStorage.setItem("sidebarStatus", document.documentElement.classList.contains("sidebar-is-collapsed") ? "collapsed" : "expanded");
  });

  const profileBtn = document.getElementById("profileBtn");
  const profileMenu = document.getElementById("profileMenu");
  profileBtn.addEventListener("click", (e) => { e.stopPropagation(); profileMenu.classList.toggle("show"); });
  document.addEventListener("click", () => profileMenu.classList.remove("show"));
</script>

</body>
</html>