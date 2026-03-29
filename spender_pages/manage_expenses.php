<?php

require 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =====================================================
   FETCH ACTIVE BUDGET (INCLUDE budget_name)
===================================================== */
$budgetStmt = $conn->prepare("
    SELECT id, budget_amount, start_date, end_date, status, budget_name
    FROM budget
    WHERE user_id = :user_id
      AND status = 'Active'
      AND CURDATE() BETWEEN start_date AND end_date
    LIMIT 1
");
$budgetStmt->execute(['user_id' => $user_id]);
$activeBudget = $budgetStmt->fetch(PDO::FETCH_ASSOC);

/* =====================================================
   SAFE INITIALIZATION
===================================================== */
$expenses = [];
$totalExpenses = 0;       // Analytics total
$budgetExpenses = 0;      // Expenses inside current budget
$categoryBreakdown = [];
$categoryPercentages = [];
$thisMonthTotal = 0;
$prevMonthTotal = 0;
$budgetLeft = 0;
$budgetExpired = false;

$budgetId     = $activeBudget['id'] ?? null;
$budgetAmount = floatval($activeBudget['budget_amount'] ?? 0);
$budgetStart  = $activeBudget['start_date'] ?? null;
$budgetEnd    = $activeBudget['end_date'] ?? null;

/* =====================================================
   DEFINE TODAY DATE (MUST BE BEFORE BUDGET CHECK)
===================================================== */
$today = new DateTime();

/* =====================================================
   CHECK IF BUDGET EXPIRED & UPDATE STATUS AUTOMATICALLY
===================================================== */
if ($budgetId && $budgetEnd) {
    $budgetEndDate = new DateTime($budgetEnd);
    $todayDate = (clone $today)->setTime(0,0,0);
    
    if ($todayDate > $budgetEndDate) {
        // Budget has expired - update status to Inactive
        $updateStmt = $conn->prepare("
            UPDATE budget 
            SET status = 'Inactive' 
            WHERE id = ?
        ");
        $updateStmt->execute([$budgetId]);
        
        $budgetExpired = true;
        $budgetAmount = 0;
        $activeBudget = null;
    }
}
/* =====================================================
   FETCH EXPENSES WITHIN ACTIVE BUDGET PERIOD
===================================================== */
if ($budgetId && $budgetStart && $budgetEnd) {
    $stmt = $conn->prepare("
        SELECT e.id, e.description, e.amount, e.expense_date, e.receipt_upload,
               e.category_id, e.payment_method_id, c.category_name, pm.payment_method_name,
               e.budget_id
        FROM expenses e
        LEFT JOIN category c ON e.category_id = c.id
        LEFT JOIN payment_method pm ON e.payment_method_id = pm.id
        WHERE e.user_id = ?
          AND e.budget_id = ?
          AND e.expense_date BETWEEN ? AND ?
        ORDER BY id DESC
    ");
    $stmt->execute([$user_id, $budgetId, $budgetStart, $budgetEnd]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* =====================================================
   CALCULATE EXPENSES INSIDE CURRENT BUDGET
===================================================== */
$budgetExpenses = 0;
foreach ($expenses as $exp) {
    $budgetExpenses += floatval($exp['amount']);
}
// ✅ Calculate remaining allowance (never negative)
$budgetLeft = max(0, $budgetAmount - $budgetExpenses);

/* =====================================================
   TOTAL EXPENSES (ANALYTICS ONLY)
===================================================== */
$totalExpenses = ($budgetAmount - $budgetLeft);

/* =====================================================
   CATEGORY BREAKDOWN & PERCENTAGES
===================================================== */
$allCategoriesStmt = $conn->query("SELECT id, category_name FROM category");
$allCategories = $allCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize breakdown
foreach ($allCategories as $cat) {
    $categoryBreakdown[$cat['category_name']] = 0;
}

// Sum expenses inside budget
foreach ($expenses as $exp) {
    $categoryBreakdown[$exp['category_name']] += floatval($exp['amount']);
}

// Calculate percentages
foreach ($categoryBreakdown as $category => $amount) {
    $categoryPercentages[$category] = $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 2) : 0;
}

arsort($categoryBreakdown);

$analyticsStmt = $conn->prepare("
    SELECT amount, expense_date
    FROM expenses
    WHERE user_id = ?
");
$analyticsStmt->execute([$user_id]);
$allExpenses = $analyticsStmt->fetchAll(PDO::FETCH_ASSOC);


/* =====================================================
   MONTHLY ANALYTICS
===================================================== */
$now = new DateTime();
$startOfThisMonth = (clone $now)->modify('first day of this month')->setTime(0,0,0);
$startOfNextMonth = (clone $startOfThisMonth)->modify('+1 month');
$startOfPrevMonth = (clone $startOfThisMonth)->modify('-1 month');

foreach ($allExpenses as $exp) {
    $d = new DateTime($exp['expense_date']);
    $amt = floatval($exp['amount']);
    if ($d >= $startOfThisMonth && $d < $startOfNextMonth) {
        $thisMonthTotal += $amt;
    }
    if ($d >= $startOfPrevMonth && $d < $startOfThisMonth) {
        $prevMonthTotal += $amt;
    }
}

/* =====================================================
   MONTH CHANGE %
===================================================== */
if ($prevMonthTotal > 0) {
    $monthChangePct = round((($thisMonthTotal - $prevMonthTotal) / $prevMonthTotal) * 100, 1);
} elseif ($thisMonthTotal > 0) {
    $monthChangePct = 100.0;
} else {
    $monthChangePct = 0.0;
}

/* =====================================================
   READY: $budgetLeft WILL NEVER BE NEGATIVE
   Use $budgetLeft when adding new expense:
   if ($newExpenseAmount > $budgetLeft) => prevent insertion
===================================================== */
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Expense</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    /* ===== THEME VARIABLES ===== */
    :root {
      --bg-body: #f5f5f5;
      --bg-card: #ffffff;
      --bg-analytics: #ffffff;
      --bg-stat-card: #ffffff;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --text-analytics: #0f172a;
      --border-color: #eef1f6;
      --border-light: #dbe7ff;
      --accent-blue: #2f7cff;
      --accent-blue-light: #eaf2ff;
      --accent-purple: #7c3aed;
      --accent-purple-light: #f5f3ff;
      --accent-green: #10b981;
      --accent-green-light: #dcfce7;
      --accent-red: #ef4444;
      --accent-red-light: #fee2e2;
      --shadow: rgba(15, 23, 42, 0.06);
      --shadow-light: rgba(15, 23, 42, 0.05);
    }

    [data-theme="dark"] {
      --bg-body: #12141a;
      --bg-card: #191c24;
      --bg-analytics: #191c24;
      --bg-stat-card: #191c24;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --text-analytics: #f8fafc;
      --border-color: #2a2e39;
      --border-light: #374151;
      --accent-blue: #3b82f6;
      --accent-blue-light: #1e293b;
      --accent-purple: #a855f7;
      --accent-purple-light: #373250;
      --accent-green: #22c55e;
      --accent-green-light: #14532d;
      --accent-red: #ef4444;
      --accent-red-light: #7f1d1d;
      --shadow: rgba(0,0,0,0.2);
      --shadow-light: rgba(0,0,0,0.1);
    }

    * { margin:0; padding:0; box-sizing:border-box; }

    body { background: var(--bg-body); height: 100%; margin: 0; color: var(--text-main); transition: background 0.3s ease; }
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


    /* =========================
   ANALYTICS SECTION (CLEAN)
========================= */

.dashboard-top-row {
   display: flex;
    gap: 20px;
    margin-bottom: 20px;
    align-items: flex-start;
    height: 300px;
}

/* LEFT SIDE: ANALYTICS */
.analytics-container {
    flex: 1;
    min-width: 400px;
    background: var(--bg-analytics);
    padding: 22px;
    border-radius: 18px;
    border: 1px solid var(--border-color);
    box-shadow: 0 10px 35px var(--shadow);
    height: 300px; /* Ensure it matches the height of the left box */
    transition: background 0.3s ease;
}

.analytics-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.analytics-header i {
  font-size: 20px;
  color: var(--accent-blue);
  background: var(--accent-blue-light);
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  border: 1px solid var(--border-light);
}

.analytics-header h2 {
  margin: 0;
  color: var(--text-analytics);
  font-size: 22px;
  font-weight: 800;
}

/* UPDATED 2x2 GRID LAYOUT */
.analytics-grid {
  display: grid;
  /* This forces exactly 2 columns */
  grid-template-columns: repeat(2, 1fr); 
  gap: 15px; /* Increased gap slightly for a cleaner look */
  margin-bottom: 5px;
}

/* STAT CARD (PASTEL LOOK) */
.stat-card {
  background: var(--bg-stat-card);
  padding: 10px 10px;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  box-shadow: 0 4px 10px var(--shadow-light);
  transition: 0.18s ease;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* move icon to the right inside stat cards */
.stat-card { display:flex; align-items:center; gap:10px; }
.stat-card-content { order:1; flex:1; }
.stat-icon { order:2; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:10px; }
.stat-icon i { font-size:18px; }

/* place icon on the right and content on the left */
.stat-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.stat-card-content { flex: 1; order: 1; text-align: left; }

.stat-icon { 
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 18px;
  order: 2;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
}

/* Small colored top bar like dashboard cards */
.stat-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: var(--accent-blue);
}

/* CONTENT */
.stat-card-content {
  position: relative;
  z-index: 1;
  flex: 1 1 auto;
}

.stat-icon {
  font-size: 20px;
  color: #ffffff;
  background: var(--accent-purple);
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  border: none;
}

.trend-up { color: var(--accent-green); font-weight:800; }
.trend-down { color: var(--accent-red); font-weight:800; }

.stat-label {
  font-size: 10px;
  color: var(--text-muted);
  margin-bottom: 6px;
  font-weight: 700;
  letter-spacing: 0.2px;
  text-transform: uppercase;
}

.stat-value {
  font-size: 16px;
  font-weight: 900;
  color: var(--text-main);
  margin-bottom: 4px;
}

.stat-subtitle {
  font-size: 10px;
  color: var(--text-muted);
  font-weight: 600;
}

/* =========================
   COLOR VARIANTS (INSPO)
========================= */
.stat-blue {
  background: var(--accent-blue-light);
  border: 1px solid var(--border-light);
}
.stat-blue::before { background: var(--accent-blue); }

.stat-purple {
  background: var(--accent-purple-light);
  border: 1px solid var(--border-light);
}
.stat-purple::before { background: var(--accent-purple); }

.stat-green {
  background: var(--accent-green-light);
  border: 1px solid var(--border-light);
}
.stat-green::before { background: var(--accent-green); }

.stat-orange {
  background: #fff7ed;
  border: 1px solid #ffe2c3;
}
.stat-orange::before { background: #f8bf5c; }

/* =========================
   BREAKDOWN SECTION
========================= */
.categories-breakdown {
    flex: 1;
    min-width: 400px;
    background: var(--bg-card);
    padding: 22px;
    border-radius: 18px;
    border: 1px solid var(--border-color);
    box-shadow: 0 10px 35px var(--shadow);
    height: 300px; /* Ensure it matches the height of the left box */
    transition: background 0.3s ease;
}

/* THE SINGLE LINE CATEGORY STYLE */
.categories-list {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-top: 15px;
}

.category-row {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cat-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    min-width: 100px; /* Aligns the start of the lines */
}

.cat-line-container {
    flex: 1;
    height: 6px;
    background: var(--border-light);
    border-radius: 10px;
    position: relative;
    overflow: hidden;
}

.cat-line-fill {
    height: 100%;
    border-radius: 10px;
    /* Uses the purple from your theme */
    background: linear-gradient(90deg, var(--accent-purple), var(--accent-purple));
}

.cat-pct {
    font-size: 12px;
    font-weight: 800;
    color: var(--text-main);
    min-width: 45px;
    text-align: right;
}

/* Responsive fix */
@media (max-width: 1024px) {
    .dashboard-top-row {
        flex-direction: column;
    }
    .analytics-container, .categories-breakdown {
        width: 100%;
    }
}


.breakdown-title {
  font-weight: 900;
  color: var(--text-main);
  margin-bottom: 18px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.breakdown-title::before {
  content: "";
  width: 6px;
  height: 18px;
  background: var(--accent-purple);
  border-radius: 99px;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid var(--border-light);
  transition: 0.2s ease;
}

.category-item:last-child {
  border-bottom: none;
}

.category-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
}

.category-left { flex: 1; margin-right: 12px; }
.category-right { text-align: right; min-width: 120px; }

@media (max-width: 700px) {
  .stat-card { padding: 12px; }
}

.category-name {
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 6px;
  font-size: 11px;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: var(--border-light);
  border-radius: 99px;
  overflow: hidden;
  margin-bottom: 5px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent-blue), var(--accent-blue));
  transition: width 0.4s ease;
  border-radius: 99px;
}

.category-percent {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 700;
}

.category-amount {
  font-weight: 900;
  color: var(--accent-purple);
  min-width: 100px;
  text-align: right;
  font-size: 14px;
}

/* Remove global purple override */
h2 {
  color: var(--text-main);
}

.main-content {
  display: flex;
  flex-direction: column;
  height: 100%;
  
}


.table-container {
  flex: 1;
  max-height: calc(100vh - 450px); /* viewport - topbar - margin/padding */
  overflow-y: auto;
  overflow-x: auto;
  background: var(--bg-card);
  border-radius: 12px;
  padding: 0; /* important for sticky header alignment */
  margin-top: 5px;
  box-shadow: 0 6px 15px var(--shadow);
  border: 1px solid var(--border-color);
  transition: background 0.3s ease;
}

/* MODIFIED */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

/* SAME */
th, td { 
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

/* MODIFIED (Sticky Header Added) */
thead th {
  position: sticky;
  top: 0;
  background: var(--accent-purple); /* must have background */
  color: white;
  font-weight: 700;
  z-index: 5; /* keeps header above rows */
}

/* Keep rounded corners */
thead th:first-child {
  border-top-left-radius: 12px;
}

thead th:last-child {
  border-top-right-radius: 12px;
}

/* SAME */
tr:hover {
  background: var(--accent-purple-light);
}

.receipt-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.actions {
  gap: 8px;
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;     
}

.btn-edit,
.btn-delete {
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;

  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-edit {
  background: var(--accent-purple-light);
  color: var(--accent-purple);
}

.btn-edit:hover {
  background: var(--accent-purple);
  color: white;
}

/* Receipt View Button (inside table) */
.btn-view-receipt {
  border: none;
  cursor: pointer;

  padding: 6px 10px;
  border-radius: 8px;

  font-size: 13px;
  font-weight: 600;

  background: var(--accent-purple-light);
  color: var(--accent-purple);

  border: 1px solid var(--border-light);
  transition: 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 18px rgba(91, 33, 182, 0.08);
}

/* Hover */
.btn-view-receipt:hover {
  background: var(--accent-purple-light);
  color: var(--accent-purple);  
  border-color: var(--border-light);
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(91, 33, 182, 0.16);
}

/* Click */
.btn-view-receipt:active {
  transform: translateY(0);
  box-shadow: 0 8px 16px rgba(91, 33, 182, 0.12);
}

/* Focus (keyboard friendly) */
.btn-view-receipt:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.18);
}

.btn-delete {
  background: var(--accent-red-light);
  color: var(--accent-red);
}

.btn-delete:hover {
  background: var(--accent-red);
  color: white;
}

    /* FAB */
    .fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--accent-purple);
    color: white;
    font-size: 26px;
    border: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 4px 10px var(--shadow);
}

    .fab:hover { background: var(--accent-purple); transform: translateY(-3px); }

    /* MODAL */
    .modal-overlay {
      display: none; /* Controlled by JS */
      position: fixed; /* Stays in place even if you scroll */
      top: 0;
      left: 0;
      width: 100vw; /* 100% of the viewport width */
      height: 100vh; /* 100% of the viewport height */
      background: rgba(0, 0, 0, 0.6); /* This is the "gray/dim" effect */
      justify-content: center;
      align-items: center;
      z-index: 9999; /* Ensure this is higher than your sidebar's z-index */
  }

    .expense-area {
      background: var(--bg-card);
      padding: 22px;
      border-radius: 18px;
      max-width: 800px;
      width: 90%;
      box-shadow: 0px 6px 18px var(--shadow);
      position: relative;
      animation: pop 0.3s ease;
      transition: background 0.3s ease;
    }

    @keyframes pop {
      0% { transform: scale(0.7); opacity:0; }
      100% { transform: scale(1); opacity:1; }
    }

    .expense-area .close-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      background: var(--accent-red);
      color: white;
      border: none;
      font-size: 20px;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
    }

    .label{margin-bottom: 10px; font-weight:700; font-size:14px; color: var(--text-main);}
    .cat-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(120px,1fr)); gap: 12px; margin-bottom: 15px; }
    .cat-card { background: var(--bg-card); border-radius: 16px; padding: 16px; text-align:center; cursor:pointer; transition:0.2s; user-select:none; border: 2px solid transparent;}
    .cat-card.active { border:2px solid var(--accent-purple); background: var(--accent-purple-light);}
    .cat-card:hover { transform: translateY(-3px);}
    .cat-icon { font-size:24px; margin-bottom:6px; }
    .cat-name { font-weight:700; font-size:14px; color: var(--text-main); }

    .expense-form { margin-top: 12px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display:block; font-weight:700; margin-bottom:6px; font-size:14px; color: var(--text-main); }
    .form-group input { width:100%; padding:12px; border-radius:14px; border:1px solid var(--border-color); font-size:14px; background: var(--bg-card); color: var(--text-main); }
    .form-group input:focus { border-color: var(--accent-purple); outline:none; }
    .form-group select {
        width: 100%;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        font-size: 14px;
        outline: none;
        background: var(--bg-card);
        color: var(--text-main);
        cursor: pointer;
        transition: 0.2s ease;
    }
    .btn-save { width:100%; padding:14px; background: var(--accent-purple); color:white; border:none; border-radius:14px; font-weight:800; cursor:pointer; }
    .btn-save:hover { opacity:0.9; }

    /* =========================
       CUSTOM TOAST NOTIFICATIONS
    ========================= */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .custom-toast {
        display: flex;
        align-items: center;
        background: var(--bg-card);
        width: 380px;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 25px var(--shadow);
        gap: 18px;
        animation: slideInRight 0.4s ease forwards;
        position: relative;
        border: 1px solid var(--border-color);
        transition: background 0.3s ease;
    }

    .custom-toast.fade-out {
        animation: fadeOut 0.4s ease forwards;
    }

    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(120%); opacity: 0; }
    }

/* Container for the side-by-side layout */
.expense-flex-wrapper {
    display: flex;
    gap: 30px;
    align-items: flex-start;
    justify-content: center;
    perspective: 1000px; /* For a subtle 3D feel */
}

/* AI Prediction Card - The "Truly AI" Look */
.ai-prediction-card {
    flex: 0 0 340px;
    background: var(--bg-card);
    border-radius: 24px;
    padding: 24px;
    border: 1px solid var(--border-color);
    box-shadow: 0 20px 40px var(--shadow);
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    font-family: 'Inter', sans-serif;
}

/* Subtle Animated Background Pulse */
.ai-prediction-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(99, 0, 212, 0.03) 0%, transparent 70%);
    animation: rotate-bg 10s linear infinite;
    pointer-events: none;
}

@keyframes rotate-bg {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ============================================================
   AI PREDICTION CARD - PROFESSIONAL STYLING
   ============================================================ */

.ai-prediction-card {
    flex: 0 0 340px;
    background: var(--bg-card);
    border-radius: 24px;
    padding: 24px;
    border: 1px solid var(--border-color);
    box-shadow: 0 20px 40px var(--shadow);
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    font-family: 'Inter', sans-serif;
}

/* AI Header with Pulse */
.ai-header {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--accent-purple);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
}

.ai-header::before {
    content: '';
    width: 8px;
    height: 8px;
    background: var(--accent-purple);
    border-radius: 50%;
    box-shadow: 0 0 8px var(--accent-purple);
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

/* The Category Result */
#predictedCategoryName {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-main);
    margin: 10px 0 25px 0;
    text-align: center;
}

/* 1. The Container */
.gauge-box {
    position: relative;
    width: 180px;
    height: 90px;
    margin: 0 auto 35px;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: flex-end;
}

/* 2. The Static Grey Track */
.gauge-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    border: 12px solid var(--border-light);
    box-sizing: border-box;
    z-index: 1;
}

/* 3. The Circulation/Scanning Effect (NEW) */
/* This creates a light "sweep" that rotates constantly */
.gauge-box::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    /* A soft white/purple glow that sweeps around */
    background: conic-gradient(
        from 0deg, 
        transparent 0deg, 
        rgba(177, 0, 212, 0.1) 300deg, 
        rgba(177, 0, 212, 0.4) 360deg
    );
    z-index: 3;
    animation: circulate 2s linear infinite;
    pointer-events: none;
    /* Mask it so it only shows on the ring path */
    -webkit-mask: radial-gradient(circle, transparent 65%, black 66%);
    mask: radial-gradient(circle, transparent 65%, black 66%);
}

@keyframes circulate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* 4. The Multi-Color AI Fill */
.gauge-box .arc {
    position: absolute;
    top: 0;
    left: 0;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    box-sizing: border-box;
    z-index: 2;

    background: conic-gradient(
        from 270deg,
        #f172e7 0deg 171deg,      
        #ff9f43 171deg 176.4deg,  
        #b100d4 176.4deg 180deg,  
        transparent 180deg
    );

    -webkit-mask: radial-gradient(circle, transparent 65%, black 66%);
    mask: radial-gradient(circle, transparent 65%, black 66%);

    transform: rotate(0deg); 
    transition: transform 1.5s cubic-bezier(0.22, 1, 0.36, 1);
}

/* 5. Center Text & Label */
.dynamic-pct {
    position: absolute;
    bottom: 5px;
    z-index: 10;
    font-size: 30px; /* Slimmed down slightly */
    font-weight: 800;
    color: #1a202c;
    line-height: 1;
}

.gauge-box .label {
    position: absolute;
    bottom: -22px;
    width: 100%;
    text-align: center;
    font-size: 10px;
    color: #a0aec0;
    font-weight: 700;
    text-transform: uppercase;
}
/* Option Items (Confidence Breakdown) */
.ai-options-list {
    background: #f8fafc;
    border-radius: 16px;
    padding: 8px;
    margin-bottom: 24px;
    border: 1px solid #f1f5f9;
}

/* Container for the list */
.ai-options-list {
    background: #f8fafc;
    border-radius: 16px;
    padding: 8px;
    margin-bottom: 24px;
    border: 1px solid #f1f5f9;
}

/* Individual rows */
.option-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    font-size: 13px;
    color: #64748b;
    border-radius: 10px;
    margin-bottom: 2px;
}

/* Active/Primary prediction row */
.option-item.active {
    background: white;
    box-shadow: 0 4px 12px rgba(241, 114, 231, 0.15); /* Soft pink glow */
    color: #1a202c;
    font-weight: 700;
}

/* Grouping dot + text */
.label-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Color Dots */
.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.dot.pink { background-color: #f172e7; box-shadow: 0 0 6px rgba(241, 114, 231, 0.5); }
.dot.orange { background-color: #ff9f43; }
.dot.purple { background-color: #b100d4; }

/* Percentage alignment */
.dynamic-pct-small, .val {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
}

.dynamic-pct-small {
    color: #f172e7; /* Matches the 95% Pink */
}

/* Professional AI Footer */
.ai-footer {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

/* Primary AI Button: Clean, Sleek, Subtle Glow */
.btn-confirm {
    flex: 2;
    background: #6300d4; /* Solid color is often more professional than gradients */
    color: white;
    border: none;
    padding: 10px 16px; /* Reduced padding for a slimmer look */
    border-radius: 8px; /* Softer, modern radius */
    font-weight: 600;
    font-size: 13px; /* Smaller, cleaner text */
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(99, 0, 212, 0.2);
}

.btn-confirm:hover {
    background: #5200b0;
    box-shadow: 0 4px 12px rgba(99, 0, 212, 0.3);
    transform: translateY(-1px);
}

.btn-confirm:active {
    transform: translateY(0);
}

/* Secondary Button: Ghost style to reduce visual noise */
.btn-manual {
    flex: 1;
    background: transparent;
    border: 1px solid #e2e8f0;
    color: #64748b;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-manual:hover {
    background: #f8fafc;
    color: #1e293b;
    border-color: #cbd5e0;
}

/* Ensure the AI Body doesn't feel cramped */
.ai-body {
    padding-top: 5px;
}

.ma.manual-category-card {
    width: 320px; /* Slightly wider for better text fit */
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 20px;
    border: 1px solid #e2e8f0;
}

.manual-header {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.manual-body {
    display: grid;
    grid-template-columns: 1fr 1fr; /* Two columns */
    gap: 10px;
}

.cat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px 8px;
    border-radius: 10px;
    background: #fff;
    border: 1px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

/* Icon Styling */
.cat-card i {
    font-size: 18px;
    margin-bottom: 8px;
    color: #64748b; /* Professional slate color */
    transition: color 0.2s ease;
}

/* Label Styling */
.cat-card span {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    line-height: 1.2;
}

/* Professional Hover State */
.cat-card:hover {
    background: #f8fafc;
    border-color: #6300d4; /* Matches your AI Purple */
}

.cat-card:hover i {
    color: #6300d4;
}

.cat-card:hover span {
    color: #1e293b;
}

/* Active State for when a category is selected */
.cat-card.selected {
    background: #f5f0ff;
    border-color: #6300d4;
}

.cat-card.selected i, .cat-card.selected span {
    color: #6300d4;
}


/* Container for the AI Suggested Category */
/* This is the magic part: it shows the badge ONLY when the label has text */
.ai-suggested-container:has(#selectedCategoryLabel:not(:empty)) {
    display: flex !important;
}

/* Base Styles */
.ai-suggested-container {
    display: none; /* Hidden by default */
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(98deg, #f5f0ff 0%, #ffffff 100%);
    border: 1px solid #e9e0ff;
    border-left: 4px solid #6300d4;
    padding: 10px 14px;
    border-radius: 12px;
    margin: 15px 0;
    box-shadow: 0 4px 12px rgba(99, 0, 212, 0.05);
}

.ai-suggestion-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-sparkle-box {
    width: 32px;
    height: 32px;
    background: #6300d4;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.ai-text-wrapper {
    display: flex;
    flex-direction: column;
}

.ai-tiny-title {
    font-size: 9px;
    font-weight: 800;
    color: #8b5cf6;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

#selectedCategoryLabel {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}

.ai-status-pill {
    font-size: 9px;
    font-weight: 700;
    background: #ffffff;
    color: #6300d4;
    padding: 3px 8px;
    border-radius: 20px;
    border: 1px solid #e9e0ff;
    text-transform: uppercase;
}


    /* Success Theme */
    .toast-success { border: 3px solid #62C976; }
    .toast-success .toast-icon i { color: #62C976; font-size: 32px; }
    .toast-success .toast-title { color: #62C976; }
    .toast-success .toast-close { background: #62C976; color: white; }

    /* Error Theme */
    .toast-error { border: 3px solid #EB786C; }
    .toast-error .toast-icon i { color: #EB786C; font-size: 36px; }
    .toast-error .toast-title { color: #EB786C; }
    .toast-error .toast-close { background: #EB786C; color: white; }

    /* Content Styles */
    .toast-content { flex: 1; }
    .toast-title {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    .toast-message {
        color: #888;
        font-size: 14px;
        font-weight: 500;
    }

    /* Close Button */
    .toast-close {
        border: none;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        align-self: flex-start;
        margin-top: -5px;
        margin-right: -5px;
        transition: 0.2s;
    }
    .toast-close:hover { opacity: 0.8; transform: scale(1.1); }

  </style>
</head>
<body>

<div class="toast-container" id="toastContainer">
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="custom-toast toast-success">
            <div class="toast-icon"><i class="fa-solid fa-check"></i></div>
            <div class="toast-content">
                <div class="toast-title">SUCCESS!</div>
                <div class="toast-message"><?= htmlspecialchars($_SESSION['success_msg']) ?></div>
            </div>
            <button class="toast-close" onclick="closeToast(this)"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="custom-toast toast-error">
            <div class="toast-icon"><i class="fa-solid fa-xmark"></i></div>
            <div class="toast-content">
                <div class="toast-title">ERROR!</div>
                <div class="toast-message"><?= htmlspecialchars($_SESSION['error_msg']) ?></div>
            </div>
            <button class="toast-close" onclick="closeToast(this)"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
  </div>

<div class="main-content">

<!-- BUDGET EXPIRED WARNING -->
    <?php if ($budgetExpired): ?>
        <div style="padding:10px; background:#ffecec; border:1px solid #a30000; border-radius:6px; margin-bottom:12px;">
            ⚠ Your current budget has ended. Please set a new budget to continue adding expenses.
        </div>
    <?php endif; ?>

<div class="dashboard-top-row">

    <div class="analytics-container">
        <div class="analytics-header">
            <i class="fas fa-chart-pie"></i>
            <h2>Expense Analytics</h2>
        </div>

        <div class="analytics-grid">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                <div class="stat-card-content">
                    <div class="stat-label">Total Allowance Left</div>
                      <div class="stat-value">
                           ₱ <?= number_format($budgetLeft, 2) ?>
                      </div>
                    <div class="stat-subtitle">
                        Allowance: ₱ <?= number_format($budgetAmount, 2) ?>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div>
                <div class="stat-card-content">
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-value">₱ <?= number_format($totalExpenses, 2) ?></div>
                    <div class="stat-subtitle"><?= count($expenses) ?> transactions</div>
                </div>
            </div>
<div class="stat-card stat-green">
    <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>

    <div class="stat-card-content">
        <div class="stat-label">Allowance Period</div>

        

<?php if ($activeBudget && !$budgetExpired): ?>
    <div class="stat-value">
        <?= htmlspecialchars($activeBudget['budget_name']) ?>
    </div>

    <div class="stat-subtitle">
        <?= date("M d, Y", strtotime($budgetStart)) ?>
        -
        <?= date("M d, Y", strtotime($budgetEnd)) ?>
    </div>

<?php else: ?>
    <div class="stat-value">No active Allowance</div>
<?php endif; ?>


</div>

    </div>
  

            <div class="stat-card stat-orange">
                <div class="stat-icon"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="stat-card-content">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">₱ <?= number_format($thisMonthTotal, 2) ?></div>
                    <div class="stat-subtitle">
                        <?php if ($monthChangePct > 0): ?>
                            <span class="trend-up">↑ <?= abs($monthChangePct) ?>%</span>
                        <?php else: ?>
                            <span class="trend-down">↓ <?= abs($monthChangePct) ?>%</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="categories-breakdown">
        <div class="breakdown-title">Category Breakdown</div>
        <div class="categories-list">
            <?php foreach ($categoryBreakdown as $name => $amt):
                $pct = $categoryPercentages[$name];
            ?>
                <div class="category-row">
                    <div class="cat-label"><?= htmlspecialchars($name) ?></div>
                    <div class="cat-line-container">
                        <div class="cat-line-fill" style="width: <?= $pct ?>%;"></div>
                    </div>
                    <div class="cat-pct"><?= $pct ?>%</div>
                    <div class="cat-pct">₱ <?= number_format($amt, 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Description</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
                <th>Receipt</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($expenses)): ?>
                <?php foreach ($expenses as $index => $exp): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($exp['description']) ?></td>
                        <td><?= htmlspecialchars($exp['category_name']) ?></td>
                        <td>₱ <?= number_format($exp['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($exp['payment_method_name']) ?></td>
                        <td><?= date("M d, Y", strtotime($exp['expense_date'])) ?></td>
                        <td>
                            <?php if ($exp['receipt_upload']): ?>
                                <button class="btn-view-receipt" data-receipt="<?= htmlspecialchars($exp['receipt_upload']) ?>">👁 View</button>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="actions"> 
                            <a href="#" class="btn-edit"
                                data-id="<?= $exp['id'] ?>"
                                data-category="<?= $exp['category_id'] ?>"
                                data-description="<?= htmlspecialchars($exp['description']) ?>"
                                data-amount="<?= $exp['amount'] ?>"
                                data-payment="<?= $exp['payment_method_id'] ?>"
                                data-receipt="<?= htmlspecialchars($exp['receipt_upload']) ?>">
                                ✏️ Edit
                            </a>
                            <a href="delete_expense.php?id=<?= $exp['id'] ?>" class="btn-delete" onclick="return confirm('Delete this expense?');">🗑 Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;">No expenses recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<button class="fab" title="Add Expense">
    <i class="fa-solid fa-plus"></i>
</button>

<div class="modal-overlay" id="modalOverlay">
    <div class="expense-area">
        <button class="close-btn">&times;</button>

        <!-- FLEX WRAPPER: form + AI container -->
        <div class="expense-flex-wrapper" style="display: flex; gap: 24px; align-items: stretch; justify-content: center; padding: 10px;">

            <!-- ================= FORM ================= -->
            <form class="expense-form" 
                  action="add_expense_process.php" 
                  method="POST" 
                  enctype="multipart/form-data"
                  style="flex: 1;">
                
                <input type="hidden" name="category_id" id="categoryInput" value="1">
                <input type="hidden" name="expense_id" id="expenseId">

                <div class="form-group">
                    <label>Description</label>
                    <input type="text" id="descInput" name="description" placeholder="What did you buy?">

                </div>

                <div class="ai-suggested-container">
                  <div class="ai-suggestion-content">
                      <div class="ai-sparkle-box">
                          <i class="fa-solid fa-wand-magic-sparkles"></i>
                      </div>
                      <div class="ai-text-wrapper">
                          <span class="ai-tiny-title">AI Predicted</span>
                          <span id="selectedCategoryLabel"></span>
                      </div>
                  </div>
                  <div class="ai-status-pill">Smart Fill</div>
              </div>

                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" name="amount" id="amountInput" placeholder="₱" required min="1">
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method_id" id="paymentInput" required>
                        <option value="1">Cash</option>
                        <option value="2">Credit Card</option>
                        <option value="3">Debit Card</option>
                        <option value="4">GCash</option>
                        <option value="5">Maya / Paymaya</option>
                        <option value="6">Bank Transfer</option>
                        <option value="7">Online Payment</option>
                        <option value="8">Check</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Proof of Transaction</label>
                    <input type="file" name="receipt_upload" accept="image/*">
                    <p id="currentReceipt" style="font-size:12px; color:#555;">No receipt uploaded</p>
                </div>

                <button type="submit" class="btn-save" id="submitBtn">Add Expense</button>
            </form>

            <!-- ================= AI PREDICTION CARD ================= -->
            <div id="aiPredictionContainer" class="ai-prediction-card" style="display: block; min-width: 250px; min-height: 300px;">
                <div class="ai-header">AI Category Suggestion</div>
                
                <div class="ai-body">
                    <h2 id="predictedCategoryName">CATEGORY NAME</h2>

                    <div class="gauge-box">
                        <div class="arc"></div>
                        <div class="dynamic-pct">0%</div>
                    </div>
                     <div class="label">Confidence Level</div>

                    <div class="ai-options-list" id="aiOptionsList">
    <div class="option-item active" id="slot1" style="cursor: pointer;">
        <div class="label-group">
            <span class="dot pink"></span>
            <span id="label1">Food & Dining</span>
        </div>
        <span class="dynamic-pct-small" id="pct1">0%</span>
    </div>

    <div class="option-item" id="slot2" style="cursor: pointer;">
        <div class="label-group">
            <span class="dot orange"></span>
            <span id="label2">Miscellaneous</span>
        </div>
        <span class="val" id="pct2">0%</span>
    </div>

    <div class="option-item" id="slot3" style="cursor: pointer;">
        <div class="label-group">
            <span class="dot purple"></span>
            <span id="label3">Savings</span>
        </div>
        <span class="val" id="pct3">0%</span>
    </div>
</div>

                    <div class="ai-footer">
                        <button type="button" onclick="closeAI()" class="btn-manual">Change Manually</button>
                        <button type="button" id="confirmAI" class="btn-confirm">Confirm</button>
                    </div>
                </div>
            </div>
            <!-- ================= END AI CARD ================= -->
              <div id="manualCategoryContainer" class="manual-category-card" style="display:none;">
                 <div class="manual-header">Select Category</div>
                    <div class="manual-body">
                          <div class="cat-card" data-category-id="1">
                              <i class="fa-solid fa-utensils"></i>
                              <span>Food & Dining</span>
                          </div>
                          <div class="cat-card" data-category-id="2">
                              <i class="fa-solid fa-car"></i>
                              <span>Transportation</span>
                          </div>
                          <div class="cat-card" data-category-id="3">
                              <i class="fa-solid fa-house"></i>
                              <span>Housing / Rent</span>
                          </div>
                          <div class="cat-card" data-category-id="4">
                              <i class="fa-solid fa-file-invoice-dollar"></i>
                              <span>Bills & Utilities</span>
                          </div>
                          <div class="cat-card" data-category-id="5">
                              <i class="fa-solid fa-heart-pulse"></i>
                              <span>Health & Care</span>
                          </div>
                          <div class="cat-card" data-category-id="6">
                              <i class="fa-solid fa-graduation-cap"></i>
                              <span>Education</span>
                          </div>
                          <div class="cat-card" data-category-id="7">
                              <i class="fa-solid fa-clapperboard"></i>
                              <span>Entertainment</span>
                          </div>
                          <div class="cat-card" data-category-id="8">
                              <i class="fa-solid fa-bag-shopping"></i>
                              <span>Shopping</span>
                          </div>
                          <div class="cat-card" data-category-id="9">
                              <i class="fa-solid fa-piggy-bank"></i>
                              <span>Savings</span>
                          </div>
                          <div class="cat-card" data-category-id="10">
                              <i class="fa-solid fa-ellipsis"></i>
                              <span>Miscellaneous</span>
                          </div>
                      </div>
                  </div>
        </div> <!-- END FLEX WRAPPER -->

    </div> <!-- END expense-area -->
</div> <!-- END modal-overlay -->



<div class="modal-overlay" id="receiptModal">
  <div class="receipt-area">
    <button class="close-receipt-btn">&times;</button>
    <img id="receiptImage" src="" alt="Receipt" style="max-width:100%; max-height:80vh; display:block; margin:auto; border-radius:12px;">
  </div>
</div>

<style>
  #receiptModal {
    display: none;
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
    z-index: 10000;
  }
  .receipt-area {
    background: #fff;
    padding: 20px;
    border-radius: 18px;
    position: relative;
    max-width: 90%;
  }
  .close-receipt-btn {
    position: absolute;
    top:10px;
    right:10px;
    font-size:24px;
    background:#d9534f;
    color:white;
    border:none;
    border-radius:50%;
    width:40px;
    height:40px;
    cursor:pointer;
  }
</style>

<script>
/* Keep these outside so HTML onclick can see them */
function closeToast(button) {
    const toast = button.closest('.custom-toast');
    toast.classList.add('fade-out');
    setTimeout(() => toast.remove(), 400);
}

function closeAI() {
    document.getElementById('aiPredictionContainer').style.display = "none";
    const manual = document.getElementById('manualCategoryContainer');
    if (manual) manual.style.display = "block";
}

document.addEventListener("DOMContentLoaded", () => {
    /* ================= ELEMENTS ================= */
    const fab = document.querySelector('.fab');
    const modalOverlay = document.getElementById('modalOverlay');
    const closeBtn = document.querySelector('.close-btn');
    const catCards = document.querySelectorAll('.cat-card');
    const categoryInput = document.getElementById('categoryInput'); 
    const descInput = document.getElementById("descInput");
    const aiContainer = document.getElementById('aiPredictionContainer');
    const manualContainer = document.getElementById('manualCategoryContainer');
    const categoryLabel = document.getElementById('selectedCategoryLabel');
    const confirmBtn = document.getElementById('confirmAI');

    const categoryNames = {
        1: "Food & Dining", 2: "Transportation", 3: "Housing / Rent",
        4: "Bills & Utilities", 5: "Health & Personal Care", 6: "Education",
        7: "Entertainment & Leisure", 8: "Shopping", 9: "Savings & Investments",
        10: "Miscellaneous"
    };

    /* ================= SHARED FUNCTIONS ================= */
    function applyCategory(id, name) {
        categoryInput.value = id;
        if(categoryLabel) categoryLabel.innerText = name;
        
        // Update visual state of cards in the manual grid
        catCards.forEach(card => {
            card.classList.toggle('active', card.getAttribute('data-category-id') == id);
        });
    }

    function confirmAISelection(name) {
        const aiCard = document.getElementById('aiPredictionContainer');
        aiCard.style.opacity = "1";
        aiCard.style.border = "2px solid #28a745";
        
        confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> Confirmed';
        confirmBtn.style.backgroundColor = "#28a745";
        confirmBtn.disabled = true;

        // Update main text para sync sila sa gi-click
        document.getElementById('predictedCategoryName').innerText = name.toUpperCase();
    }

    /* ================= AI LOGIC ================= */
    let typingTimer;
    descInput?.addEventListener("input", () => {
        clearTimeout(typingTimer);
        const text = descInput.value.trim();

        // Reset the state to "Initial" when typing starts again
        aiContainer.style.opacity = "1";
        aiContainer.style.border = "none"; 
        confirmBtn.innerHTML = 'Confirm';
        confirmBtn.style.backgroundColor = ""; 
        confirmBtn.disabled = false;
        
        if (text.length < 3) {
            document.getElementById('predictedCategoryName').innerText = "Keep typing...";
            return;
        }

        typingTimer = setTimeout(async () => {
            document.getElementById('predictedCategoryName').innerText = "Analyzing...";
            
            try {
                let formData = new FormData();
                formData.append('description', text);

                const response = await fetch('local_categorize.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error("Server error");
                
                const data = await response.json();

                if(manualContainer) manualContainer.style.display = 'none';

                // 1. UPDATE MAIN DISPLAY (Gauge)
                document.getElementById('predictedCategoryName').innerText = data.category_name.toUpperCase();
                const rotation = (data.confidence / 100) * 180;
                document.querySelector('.gauge-box .arc').style.transform = `rotate(${rotation}deg)`;
                document.querySelectorAll('.dynamic-pct').forEach(el => el.innerText = data.confidence + "%");

                // 2. SETUP THE 3 SLOTS
                const suggestions = data.suggestions || [
                    { id: data.category_id, name: data.category_name, conf: data.confidence },
                    { id: 10, name: "Miscellaneous", conf: (100 - data.confidence) * 0.6 },
                    { id: 9, name: "Savings", conf: (100 - data.confidence) * 0.4 }
                ];

                suggestions.forEach((sug, index) => {
                    const slotNum = index + 1;
                    const labelEl = document.getElementById(`label${slotNum}`);
                    const pctEl = document.getElementById(`pct${slotNum}`);
                    const slotEl = document.getElementById(`slot${slotNum}`);

                    if (slotEl) {
                        if (labelEl) labelEl.innerText = sug.name;
                        if (pctEl) pctEl.innerText = parseFloat(sug.conf).toFixed(1) + "%";
                        
                        // Handle clicking a specific slot
                        slotEl.onclick = () => {
                            document.querySelectorAll('.option-item').forEach(opt => opt.classList.remove('active'));
                            slotEl.classList.add('active');
                            applyCategory(sug.id, sug.name);
                            confirmAISelection(sug.name);
                        };
                    }
                });

                // 3. SETUP THE MAIN CONFIRM BUTTON (Primary Category)
                confirmBtn.onclick = () => {
                    applyCategory(data.category_id, data.category_name);
                    confirmAISelection(data.category_name);
                };

            } catch (err) {
                console.error("AI Error:", err);
                document.getElementById('predictedCategoryName').innerText = "AI Offline (Limit Reached)";
                if(manualContainer) manualContainer.style.display = 'block';
            }
        }, 800);
    });

    /* ================= MANUAL SELECTION ================= */
    catCards.forEach(card => {
        card.addEventListener('click', () => {
            const id = card.getAttribute('data-category-id');
            applyCategory(id, categoryNames[id]);
        });
    });

    /* ================= MODAL CONTROLS ================= */
    fab?.addEventListener('click', () => {
        modalOverlay.style.display = 'flex';
        aiContainer.style.display = "block"; 

        // Reset Visuals
        aiContainer.style.opacity = "1";
        aiContainer.style.border = "none";
        confirmBtn.innerHTML = 'Confirm';
        confirmBtn.style.backgroundColor = "";
        confirmBtn.disabled = false;

        if(manualContainer) manualContainer.style.display = "none";
        
        document.getElementById('predictedCategoryName').innerText = "Waiting for description...";
        document.querySelector('.gauge-box .arc').style.transform = `rotate(0deg)`;
        document.querySelectorAll('.dynamic-pct').forEach(el => el.innerText = "0%");

        document.querySelector('.expense-form').reset();
        document.getElementById('submitBtn').textContent = 'Add Expense';
        document.querySelector('.expense-form').action = 'add_expense_process.php';
    });

    closeBtn?.addEventListener('click', () => { modalOverlay.style.display = 'none'; });

    /* ================= EDIT LOGIC ================= */
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const d = this.dataset;
            modalOverlay.style.display = 'flex';
            
            applyCategory(d.category, categoryNames[d.category]);
            
            document.querySelector('input[name="description"]').value = d.description;
            document.querySelector('input[name="amount"]').value = d.amount;
            document.querySelector('select[name="payment_method_id"]').value = d.payment;

            document.getElementById('submitBtn').textContent = 'Update Expense';
            document.querySelector('.expense-form').action = 'update_expense_process.php';
            document.getElementById('expenseId').value = d.id;
        });
    });
});
</script>

</body>
</html>