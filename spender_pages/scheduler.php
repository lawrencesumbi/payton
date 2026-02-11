<?php
require 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$rp = [
  ["title"=>"Spotify Premium", "category"=>"Subscription", "amount"=>149, "pay_day"=>15, "status"=>"active"],
  ["title"=>"Netflix", "category"=>"Entertainment", "amount"=>549, "pay_day"=>22, "status"=>"active"],
  ["title"=>"Electric Bill", "category"=>"Utilities", "amount"=>2200, "pay_day"=>10, "status"=>"paused"],
  ["title"=>"WiFi Plan", "category"=>"Utilities", "amount"=>1699, "pay_day"=>5, "status"=>"active"],
  ["title"=>"Gym Membership", "category"=>"Health", "amount"=>900, "pay_day"=>1, "status"=>"active"],
  ["title"=>"Savings Auto-Transfer", "category"=>"Finance", "amount"=>1500, "pay_day"=>30, "status"=>"active"],
];

// Dummy today tasks
$tasksToday = [
  ["title"=>"Pay Spotify Premium", "type"=>"payment", "due_date"=>date("Y-m-d"), "due_time"=>"09:00", "status"=>"pending"],
  ["title"=>"Set Budget for Food", "type"=>"budget", "due_date"=>date("Y-m-d"), "due_time"=>"11:30", "status"=>"pending"],
  ["title"=>"Reminder: Tuition Fee", "type"=>"reminder", "due_date"=>date("Y-m-d"), "due_time"=>"14:00", "status"=>"done"],
  ["title"=>"Review Monthly Expenses", "type"=>"other", "due_date"=>date("Y-m-d"), "due_time"=>"18:30", "status"=>"pending"],
];

// Dummy upcoming
$upcoming = [
  ["title"=>"Netflix Payment", "type"=>"payment", "due_date"=>date("Y-m-d", strtotime("+2 days")), "due_time"=>"10:00"],
  ["title"=>"Electric Bill", "type"=>"payment", "due_date"=>date("Y-m-d", strtotime("+4 days")), "due_time"=>""],
  ["title"=>"Budget Check", "type"=>"budget", "due_date"=>date("Y-m-d", strtotime("+6 days")), "due_time"=>"15:00"],
  ["title"=>"Savings Transfer", "type"=>"payment", "due_date"=>date("Y-m-d", strtotime("+8 days")), "due_time"=>"09:00"],
];

// ---------------- Calendar Logic ----------------
$month = $_GET['m'] ?? date("m");
$year  = $_GET['y'] ?? date("Y");

$month = (int)$month;
$year  = (int)$year;

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date("t", $firstDay);
$startDayOfWeek = date("N", $firstDay); // 1=Mon, 7=Sun

$monthName = date("F", $firstDay);

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scheduler & Recurring Payments</title>

  <style>
    *{
      margin:0;
      padding:0;
      box-sizing:border-box;
      font-family: Arial, sans-serif;
    }


    /* MAIN WRAPPER */
    .scheduler-wrapper{
      max-width: 1250px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 22px;
    }

    /* LEFT AREA */
    .main-area{
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .section-card{
      background: #fff;
      border-radius: 18px;
      padding: 18px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }

    /* RECURRING PAYMENTS CARDS */
    .rp-grid{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }

    .rp-card{
      border-radius: 16px;
      padding: 16px;
      background: #f7f8ff;
      border: 1px solid #eef2ff;
      transition: 0.2s ease;
    }

    .rp-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(0,0,0,0.08);
    }

    .rp-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      margin-bottom: 10px;
    }

    .rp-icon{
      width: 42px;
      height: 42px;
      border-radius: 14px;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size: 18px;
      background: #ede9fe;
      color:#6d28d9;
      font-weight: 800;
    }

    .rp-status{
      font-size: 12px;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 700;
      background: #ecfdf5;
      color:#047857;
    }

    .rp-status.paused{
      background:#fff7ed;
      color:#c2410c;
    }

    .rp-title{
      font-size: 15px;
      font-weight: 800;
      margin-bottom: 4px;
    }

    .rp-sub{
      font-size: 13px;
      color:#6b7280;
      margin-bottom: 10px;
    }

    .rp-bottom{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      margin-top: 12px;
    }

    .rp-amount{
      font-size: 16px;
      font-weight: 900;
      color:#111827;
    }

    .rp-day{
      font-size: 13px;
      color:#6b7280;
      font-weight: 700;
    }

    /* TASK LIST */
    .task-tabs{
      display:flex;
      gap: 14px;
      font-size: 13px;
      font-weight: 800;
      margin-bottom: 12px;
    }

    .task-tabs span{
      color:#6b7280;
      cursor:pointer;
    }

    .task-tabs span.active{
      color:#6d28d9;
      border-bottom: 2px solid #6d28d9;
      padding-bottom: 6px;
    }

    .task-list{
      display:flex;
      flex-direction:column;
      gap: 10px;
    }

    .task-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding: 12px 14px;
      border-radius: 14px;
      border: 1px solid #f1f5f9;
      background: #fff;
    }

    .task-left{
      display:flex;
      align-items:center;
      gap: 12px;
    }

    .task-dot{
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #a78bfa;
    }

    .task-dot.payment{ background:#22c55e; }
    .task-dot.reminder{ background:#a78bfa; }
    .task-dot.budget{ background:#f59e0b; }
    .task-dot.other{ background:#60a5fa; }

    .task-name{
      font-size: 14px;
      font-weight: 800;
    }

    .task-meta{
      font-size: 12px;
      color:#6b7280;
      margin-top: 2px;
    }

    .task-right{
      display:flex;
      align-items:center;
      gap: 10px;
    }

    .task-date{
      font-size: 12px;
      color:#6b7280;
      font-weight: 700;
    }

    .task-btn{
      border:none;
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      cursor:pointer;
      background: #6d28d9;
      color:white;
    }

    .task-btn.done{
      background:#16a34a;
    }

    /* RIGHT SIDE PANEL */
    .side-panel{
      display:flex;
      flex-direction:column;
      gap: 18px;
    }

    /* CALENDAR */
    .calendar-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom: 12px;
    }

    .calendar-header h3{
      font-size: 16px;
      font-weight: 900;
    }

    .cal-nav{
      display:flex;
      gap: 10px;
    }

    .cal-nav a{
      text-decoration:none;
      font-weight: 900;
      color:#6d28d9;
      background:#f3e8ff;
      padding: 6px 10px;
      border-radius: 10px;
    }

    .calendar-grid{
      display:grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 8px;
      margin-top: 10px;
    }

    .cal-day-name{
      font-size: 11px;
      font-weight: 800;
      color:#6b7280;
      text-align:center;
      padding-bottom: 4px;
    }

    .cal-day{
      text-align:center;
      padding: 10px 0;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 800;
      background:#f9fafb;
      color:#111827;
    }

    .cal-day.empty{
      background: transparent;
    }

    .cal-day.today{
      background:#6d28d9;
      color:white;
    }

    /* UPCOMING */
    .upcoming-list{
      display:flex;
      flex-direction:column;
      gap: 12px;
    }

    .upcoming-item{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
      padding: 12px;
      border-radius: 14px;
      border: 1px solid #f1f5f9;
      background: #fff;
    }

    .up-left{
      display:flex;
      gap: 10px;
    }

    .up-icon{
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#ede9fe;
      color:#6d28d9;
      font-weight: 900;
    }

    .up-title{
      font-size: 14px;
      font-weight: 900;
    }

    .up-sub{
      font-size: 12px;
      color:#6b7280;
      margin-top: 2px;
    }

    .up-date{
      font-size: 12px;
      font-weight: 900;
      color:#6b7280;
      white-space: nowrap;
    }

    /* RESPONSIVE */
    @media(max-width: 1050px){
      .scheduler-wrapper{
        grid-template-columns: 1fr;
      }
      .rp-grid{
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media(max-width: 650px){
      body{ padding: 16px; }
      .rp-grid{
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

<div class="scheduler-wrapper">

  <!-- LEFT MAIN -->
  <div class="main-area">

    <!-- Recurring Payments -->
    <div class="section-card">
      <div class="rp-grid">
        <?php if(count($rp) > 0): ?>
          <?php foreach($rp as $x): ?>
            <?php
              $statusClass = ($x['status'] === "paused") ? "paused" : "";
              $iconLetter = strtoupper(substr($x['title'], 0, 1));
            ?>
            <div class="rp-card">
              <div class="rp-top">
                <div class="rp-icon"><?= $iconLetter ?></div>
                <div class="rp-status <?= $statusClass ?>">
                  <?= htmlspecialchars(ucfirst($x['status'])) ?>
                </div>
              </div>

              <div class="rp-title"><?= htmlspecialchars($x['title']) ?></div>
              <div class="rp-sub"><?= htmlspecialchars($x['category']) ?></div>

              <div class="rp-bottom">
                <div class="rp-amount">₱ <?= number_format($x['amount'], 2) ?></div>
                <div class="rp-day">Every <?= (int)$x['pay_day'] ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:#6b7280; font-size:14px;">No recurring payments yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Today Tasks -->
    <div class="section-card">
      <div class="section-title">
        <div>
          <h2>Today Tasks</h2>
          <p><?= date("F d, Y") ?></p>
        </div>
      </div>

      <div class="task-tabs">
        <span class="active">To-do</span>
        <span>Done</span>
      </div>

      <div class="task-list">
        <?php if(count($tasksToday) > 0): ?>
          <?php foreach($tasksToday as $t): ?>
            <?php
              $dotClass = $t['type'] ?? "other";
              $btnClass = ($t['status'] === "done") ? "done" : "";
              $btnText  = ($t['status'] === "done") ? "Completed" : "Mark as Done";
              $time = $t['due_time'] ? date("h:i A", strtotime($t['due_time'])) : "No time";
            ?>
            <div class="task-item">
              <div class="task-left">
                <div class="task-dot <?= htmlspecialchars($dotClass) ?>"></div>
                <div>
                  <div class="task-name"><?= htmlspecialchars($t['title']) ?></div>
                  <div class="task-meta"><?= ucfirst(htmlspecialchars($dotClass)) ?> • <?= $time ?></div>
                </div>
              </div>

              <div class="task-right">
                <div class="task-date"><?= date("M d", strtotime($t['due_date'])) ?></div>
                <a class="task-btn <?= $btnClass ?>" href="mark_done.php?id=<?= (int)$t['s_id'] ?>" style="text-decoration:none;">
                  <?= $btnText ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:#6b7280; font-size:14px;">No tasks for today.</p>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- RIGHT PANEL -->
  <div class="side-panel">

    <!-- Calendar -->
    <div class="section-card">
      <div class="calendar-header">
        <h3><?= $monthName . " " . $year ?></h3>
        <div class="cal-nav">
          <a href="?m=<?= $prevMonth ?>&y=<?= $prevYear ?>">&lt;</a>
          <a href="?m=<?= $nextMonth ?>&y=<?= $nextYear ?>">&gt;</a>
        </div>
      </div>

      <div class="calendar-grid">
        <?php
          $dayNames = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
          foreach($dayNames as $dn){
            echo "<div class='cal-day-name'>$dn</div>";
          }

          // empty cells
          for($i = 1; $i < $startDayOfWeek; $i++){
            echo "<div class='cal-day empty'></div>";
          }

          // days
          for($d = 1; $d <= $daysInMonth; $d++){
            $isToday = ($year == date("Y") && $month == date("m") && $d == date("d"));
            $cls = $isToday ? "cal-day today" : "cal-day";
            echo "<div class='$cls'>$d</div>";
          }
        ?>
      </div>
    </div>

    <!-- Upcoming -->
    <div class="section-card">
      <div class="section-title">
        <div>
          <h2>Upcoming</h2>
          <p>Next reminders & payments</p>
        </div>
      </div>

      <div class="upcoming-list">
        <?php if(count($upcoming) > 0): ?>
          <?php foreach($upcoming as $u): ?>
            <?php
              $type = $u['type'] ?? "other";
              $icon = "⏰";
              if($type === "payment") $icon = "💸";
              if($type === "budget") $icon = "📌";
              if($type === "reminder") $icon = "🔔";

              $dateText = date("M d", strtotime($u['due_date']));
              $timeText = $u['due_time'] ? date("h:i A", strtotime($u['due_time'])) : "";
            ?>
            <div class="upcoming-item">
              <div class="up-left">
                <div class="up-icon"><?= $icon ?></div>
                <div>
                  <div class="up-title"><?= htmlspecialchars($u['title']) ?></div>
                  <div class="up-sub"><?= ucfirst(htmlspecialchars($type)) ?> <?= $timeText ? "• $timeText" : "" ?></div>
                </div>
              </div>

              <div class="up-date"><?= $dateText ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:#6b7280; font-size:14px;">No upcoming schedules.</p>
        <?php endif; ?>
      </div>
    </div>

  </div>

</div>

</body>
</html>