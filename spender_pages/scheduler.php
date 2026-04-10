<?php
// Start session for notifications
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");
$user_id = $_SESSION['user_id'] ?? 1;

/* ================= FETCH PAYMENTS ================= */
$stmt = $pdo->prepare("
    SELECT sp.payment_name, sp.amount, sp.due_date
    FROM scheduled_payments sp
    JOIN due_status ds ON sp.due_status_id = ds.id
    WHERE sp.user_id = ?
    AND LOWER(ds.due_status_name) != 'paid'
    ORDER BY sp.due_date ASC
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* GROUP BY DATE */
$groupedPayments = [];
foreach ($payments as $payment) {
    $date = $payment['due_date'];
    $groupedPayments[$date][] = [
        'name' => $payment['payment_name'],
        'amount' => $payment['amount']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Calendar</title>

<style>
/* ===== THEME VARIABLES ===== */
:root {
    --bg-body: #f4f6f9;
    --bg-card: #ffffff;
    --bg-day: #f1f1f1;
    --bg-day-hover: #dbeafe;
    --text-main: #333333;
    --text-muted: #666666;
    --border-color: #e0e0e0;
    --accent-purple: #8c15ec;
    --accent-purple-light: #aa6af3;
    --success: #22c55e;
    --error: #ef4444;
    --shadow: rgba(0,0,0,0.1);
    --modal-overlay: rgba(15, 23, 42, 0.6);
}

[data-theme="dark"] {
    --bg-body: #12141a;
    --bg-card: #191c24;
    --bg-day: #2a2e39;
    --bg-day-hover: #373250;
    --text-main: #f8fafc;
    --text-muted: #94a3b8;
    --border-color: #4b5563;
    --accent-purple: #a855f7;
    --accent-purple-light: #c084fc;
    --success: #22c55e;
    --error: #ef4444;
    --shadow: rgba(0,0,0,0.2);
    --modal-overlay: rgba(0,0,0,0.7);
}

body {
    font-family: Arial, sans-serif;
    background: var(--bg-body);
    height: 100%; 
    margin: 0;
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
.app-container {
            display: flex;
            width: 100%;
            border-radius: 10px;
            
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            
        }

.calendar {
    background-color: var(--bg-card);
    flex: 1;
    
    overflow-y: auto;
    overflow-x: auto;
    width: 80%;
    
    padding: 20px;
    border-radius: 10px;
    transition: background 0.3s ease;
    
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 22px;
    margin-bottom: 20px;
    font-weight: bold;
}

.header button {
    background: var(--accent-purple);
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.header button:hover {
    background: var(--accent-purple-light);
}


.grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
}

.day {
    background: var(--bg-day);
    height: 100px;
    border-radius: 8px;
    padding: 8px;
    cursor: pointer;
    overflow-y: auto;
    transition: 0.2s;
}


.day:hover {
    background: var(--bg-day-hover);
}

.date-number {
    font-weight: bold;
}



/* Modal Overlay */
.modal {
    display: none; /* Keep hidden by default */
    position: fixed;
    z-index: 1000;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: var(--modal-overlay); /* Modern slate overlay */
    backdrop-filter: blur(4px); /* Blurs the background content */
    transition: opacity 0.3s ease;
}

.scheduled{
    text-align: center;
    margin-bottom: 20px;
}

.field{
    margin-top: 10px;
}

/* Modal Box */
.modal-content {
    background: var(--bg-card);
    width: 90%;
    max-width: 420px;
    margin: 10vh auto;
    padding: 32px;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px var(--shadow), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    font-family: 'Inter', system-ui, sans-serif;
    border: 1px solid var(--border-color);
    transition: background 0.3s ease;
}

/* Typography & Inputs */
.modal-content h2 {
    margin-top: 0;
    color: var(--text-main);
    font-size: 1.5rem;
}

.modal-content input {
    width: 100%;
    padding: 12px;
    margin-top: 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-sizing: border-box; /* Prevents padding from breaking width */
    font-size: 1rem;
    background: var(--bg-card);
    color: var(--text-main);
    transition: border-color 0.2s;
}

.modal-content input:focus {
    outline: none;
    border-color: var(--accent-purple);
    box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
}

/* The Button */
.modal-content button {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    background: var(--accent-purple); /* Modern Blue */
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.modal-content button:hover {
    background: var(--accent-purple-light);
}

.today {
    background: var(--accent-purple) !important;
    color: white;
    
}

.today strong {
    color: white;
}

.weekday {
    text-align: center;
    font-weight: bold;
    padding: 8px;
    background: var(--bg-card);
    border-radius: 6px;
    color: var(--text-main);
    border: 1px solid var(--border-color);
}

.reminders-section {
            width: 20%;
            background: var(--bg-card);
            border-left: 1px solid var(--border-color);
            padding: 25px 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

 /* Right Panel UI */
        .reminder-card {
            background: var(--accent-purple);
            padding: 20px;
            border-radius: 20px;
            color: white;
            position: relative;
            
        }

        .reminder-card h3 { margin: 0 0 10px 0; font-size: 14px; }

        .info-box {
            background: var(--bg-card);
            border-radius: 15px;
            padding: 15px;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            transition: background 0.3s ease;
        }

        .info-box h4 { margin: 0 0 10px 0; font-size: 14px; color: var(--accent-purple); }
        .info-item { font-size: 13px; margin-bottom: 5px; display: flex; justify-content: space-between; }

/* TOAST STYLES */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10001;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.custom-toast {
    display: flex;
    align-items: center;
    background: var(--bg-card);
    width: 350px;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 8px 25px var(--shadow);
    gap: 15px;
    animation: slideIn 0.3s ease-out;
    border: 1px solid var(--border-color);
    transition: background 0.3s ease;
}
.toast-success { border-left: 5px solid var(--accent-green); }
.toast-error { border-left: 5px solid var(--accent-red); }
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
</head>

<body>

<div class="toast-container" id="toastContainer">
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="custom-toast toast-success">
            <div style="flex: 1;">
                <div style="font-weight: 800; color: var(--success);">SUCCESS</div>
                <div style="color: var(--text-muted); font-size: 13px;"><?= $_SESSION['success_msg'] ?></div>
            </div>
            <button onclick="this.parentElement.remove()" style="border:none; background:none; cursor:pointer; color: var(--text-muted);">✕</button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="custom-toast toast-error">
            <div style="flex: 1;">
                <div style="font-weight: 800; color: var(--error); font-size: 11px;">ERROR</div>
                <div style="color: var(--text-muted); font-size: 13px;"><?= $_SESSION['error_msg'] ?></div>
            </div>
            <button onclick="this.parentElement.remove()" style="border:none; background:none; cursor:pointer; color: var(--text-muted);">✕</button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>
</div>

<div class="app-container">

<div class="calendar">
    <div class="header">
        <button onclick="changeMonth(-1)">◀ Prev</button>
        <span id="monthYear"></span>
        <button onclick="changeMonth(1)">Next ▶</button>
    </div>
    <div class="grid" id="calendarGrid">
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
    </div>

</div>


<div class="reminders-section">
        <div class="reminder-card">
            <h3>Upcoming Payments</h3>
            <p style="font-size: 12px; opacity: 0.9;">Manage your schedules</p>
        </div>

        <div class="info-box">
            <h4>Overdue</h4>
            <div id="overdueList">
                <p style="font-size:12px; color:#999;">No overdue payments</p>
            </div>
        </div>

        <div class="info-box">
            <h4>Today's Due</h4>
            <div id="todayList">
                <p style="font-size:12px; color:#999;">No payments today</p>
            </div>
        </div>

        <div class="info-box">
            <h4>Tomorrow's Due</h4>
            <div id="tomorrowList">
                <p style="font-size:12px; color:#999;">Clear schedule</p>
            </div>
        </div>

       

</div>

<div class="modal" id="paymentModal">
    <div class="modal-content">
        <div class="scheduled">
            <h3>Add Scheduled Payment</h3>
        </div>
        <form method="POST" action="save_schedule.php">
            <input type="hidden" name="date" id="selectedDate">

        <div class="field">
            <label>Payment Name</label>
        </div>
            <input type="text" name="payment_name" required>

        <div class="field">
            <label>Amount</label>
        </div>
            <input type="number" step="0.01" name="amount" required>
            <button type="submit">Save Payment</button>
            <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
const grid = document.getElementById("calendarGrid");
const monthYear = document.getElementById("monthYear");

// Today's real date
const realToday = new Date();

// Active calendar date
let currentDate = new Date();

const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
];

// Payments from PHP
const payments = <?php echo json_encode($groupedPayments); ?>;

/* ================= RENDER CALENDAR ================= */
function renderCalendar() {

    // Keep weekday headers
    grid.innerHTML = `
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
    `;

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    monthYear.innerText = monthNames[month] + " " + year;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const todayDate = realToday.getDate();
    const todayMonth = realToday.getMonth();
    const todayYear = realToday.getFullYear();

    // Add empty boxes before month starts
    for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement("div");
        empty.classList.add("day");
        empty.style.background = "transparent";
        empty.style.cursor = "default";
        grid.appendChild(empty);
    }

    // Add actual days
    for (let i = 1; i <= daysInMonth; i++) {

        const formattedDate = year + "-" +
            String(month+1).padStart(2,'0') + "-" +
            String(i).padStart(2,'0');

        const day = document.createElement("div");
        day.classList.add("day");

        // Highlight today
        if (i === todayDate && month === todayMonth && year === todayYear) {
            day.classList.add("today");
        }

        let paymentList = "";

        if (payments[formattedDate]) {
    paymentList += "<div style='margin-top:5px; display:flex; flex-direction:column; gap:3px;'>";
    payments[formattedDate].forEach(function(payment){
        const formattedAmount = "₱" + Number(payment.amount).toLocaleString();
        paymentList += `
            <div style="
                color: #ff5656;      /* Violet text */
                font-size: 11px;
                font-weight: 500;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            ">
                ${payment.name} - ${formattedAmount}
            </div>`;
    });
    paymentList += "</div>";
}
        day.innerHTML = `
            <div class="date-number">${i}</div>
            ${paymentList}
        `;

        day.onclick = () => openModal(year, month, i);
        grid.appendChild(day);
    }
}


/* ================= CHANGE MONTH ================= */
function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset);
    renderCalendar();
}

/* ================= MODAL ================= */
function openModal(year, month, day) {
    const date = year + "-" +
        String(month+1).padStart(2,'0') + "-" +
        String(day).padStart(2,'0');

    document.getElementById("selectedDate").value = date;
    document.getElementById("paymentModal").style.display = "block";
}

function closeModal() {
    document.getElementById("paymentModal").style.display = "none";
}

// Initial render
renderCalendar();

/* ================= REMINDERS ================= */
function renderReminders() {
    const overdueListEl = document.getElementById("overdueList");
    const todayListEl = document.getElementById("todayList");
    const tomorrowListEl = document.getElementById("tomorrowList");

    // Clear previous content
    overdueListEl.innerHTML = '';
    todayListEl.innerHTML = '';
    tomorrowListEl.innerHTML = '';

    // Get today and tomorrow date strings
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2,'0');
    const dd = String(today.getDate()).padStart(2,'0');
    const todayStr = `${yyyy}-${mm}-${dd}`;

    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const t_dd = String(tomorrow.getDate()).padStart(2,'0');
    const t_mm = String(tomorrow.getMonth() + 1).padStart(2,'0');
    const t_yyyy = tomorrow.getFullYear();
    const tomorrowStr = `${t_yyyy}-${t_mm}-${t_dd}`;

    /* ================= OVERDUE ================= */
    let hasOverdue = false;

    Object.keys(payments).forEach(dateKey => {
        if (dateKey < todayStr) {

            payments[dateKey].forEach(p => {

                // If you store status, skip paid ones
                if (p.status && p.status.toLowerCase() === "paid") return;

                overdueListEl.innerHTML += `
                    <div class="info-item">
                        ${p.name} 
                        <span>₱${Number(p.amount).toLocaleString()}</span>
                    </div>
                `;
                hasOverdue = true;
            });
        }
    });

    if (!hasOverdue) {
        overdueListEl.innerHTML = `<p style="font-size:12px; color:#999;">No overdue payments</p>`;
    }

    /* ================= TODAY ================= */
    if(payments[todayStr]) {
        payments[todayStr].forEach(p => {
            todayListEl.innerHTML += `
                <div class="info-item">
                    ${p.name} 
                    <span>₱${Number(p.amount).toLocaleString()}</span>
                </div>
            `;
        });
    } else {
        todayListEl.innerHTML = `<p style="font-size:12px; color:#999;">No payments today</p>`;
    }

    /* ================= TOMORROW ================= */
    if(payments[tomorrowStr]) {
        payments[tomorrowStr].forEach(p => {
            tomorrowListEl.innerHTML += `
                <div class="info-item">
                    ${p.name} 
                    <span>₱${Number(p.amount).toLocaleString()}</span>
                </div>
            `;
        });
    } else {
        tomorrowListEl.innerHTML = `<p style="font-size:12px; color:#999;">No payments tomorrow</p>`;
    }
}

// Call after rendering calendar
renderReminders();

// AUTO-DISMISS TOASTS
document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
        const toasts = document.querySelectorAll('.custom-toast');
        toasts.forEach(t => t.remove());
    }, 5000);
});
</script>


</body>
</html>