<?php
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");
$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $pdo->prepare("SELECT payment_name, amount, due_date FROM scheduled_payments WHERE user_id = ?");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RemindMe - Payment Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #793bf6;
            --light-purple: #f3e8ff;
            --bg-color: #f0f0ff;
            --text-dark: #2d2d2d;
            --accent-pink: #ffb5d8;
            --white: #ffffff;
        }


        /* Main Container Layout */
        .app-container {
            display: flex;
            width: 95%;
            max-width: 1200px;
            background: var(--white);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            min-height: 80vh;
        }

        /* Left Side: Calendar */
        .calendar-section {
            flex: 2;
            padding: 40px;
        }

        /* Right Side: Reminders */
        .reminders-section {
            flex: 0.8;
            background: #fffafa;
            border-left: 1px solid #eee;
            padding: 40px 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Header Styles */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .month-label {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .nav-btn {
            background: var(--primary-purple);
            border: none;
            color: var(--white);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
        }

        /* Grid Styles */
        .grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .weekday {
            text-align: center;
            font-size: 13px;
            color: #888;
            padding-bottom: 10px;
        }

        .day {
            background: #fff;
            border: 1px solid #f0f0f0;
            height: 100px;
            border-radius: 12px;
            padding: 8px;
            font-size: 14px;
            transition: 0.3s;
            cursor: pointer;
        }

        .day:hover {
            border-color: var(--primary-purple);
            transform: translateY(-2px);
        }

        .today {
            background: var(--primary-purple) !important;
            color: white !important;
            box-shadow: 0 5px 15px rgba(121, 59, 246, 0.3);
        }

    /* Container for each payment entry */
.payment-item {
    margin-top: 6px;
    padding-left: 8px;
    border-left: 3px solid var(--primary-purple); /* Purple accent line */
    display: flex;
    flex-direction: column;
    gap: 1px;
}

/* Payment Name - Bold & Dark Purple */
.item-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--primary-purple); /* Using your theme's purple */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Amount - Softer Pink/Magenta */
.item-amount {
    font-size: 10px;
    font-weight: 500;
    color: #d14d8d; /* A darker, readable pink */
}

/* Ensure today's text remains readable if the background is purple */
.today .item-name, 
.today .item-amount {
    color: #ffffff !important;
}

.today .payment-item {
    border-left-color: #ffffff;
}

.day {
    padding: 8px;
    height: 115px;
    display: flex;
    flex-direction: column;
    background: #fff;
}
        /* Right Panel UI */
        .reminder-card {
            background: var(--accent-pink);
            padding: 20px;
            border-radius: 20px;
            color: #fff;
            position: relative;
        }

        .reminder-card h3 { margin: 0 0 10px 0; font-size: 16px; }

        .info-box {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .info-box h4 { margin: 0 0 10px 0; font-size: 14px; color: var(--primary-purple); }
        .info-item { font-size: 13px; margin-bottom: 5px; display: flex; justify-content: space-between; }

        /* Container to keep things aligned */
.participants {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

/* The Blue Circle Design */
.add-participant-circle {
    width: 36px;
    height: 36px;
    background-color: #3b82f6; /* Vibrant Blue */
    color: #ffffff;            /* White Plus Sign */
    border-radius: 50%;        /* Perfect Circle */
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    border: none;
    line-height: 1; /* Ensures plus sign is centered vertically */
}

/* Hover Effect */
.add-participant-circle:hover {
    background-color: #2563eb; /* Slightly darker blue */
    transform: scale(1.1);
    box-shadow: 0 6px 14px rgba(59, 130, 246, 0.4);
}

/* Small version if you want to show it next to existing avatars */
.add-participant-circle.small {
    width: 28px;
    height: 28px;
    font-size: 18px;
    margin-left: 5px;
}

        /* Modal styling remains same as your original with better padding */
        .modal { display: none; position: fixed; background: rgba(0,0,0,0.3); top: 0; left: 0; width: 100%; height: 100%; z-index: 100; }
        .modal-content { background: white; width: 350px; margin: 10% auto; padding: 30px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="app-container">
    <div class="calendar-section">
        <div class="calendar-header">
            <div>
                <span class="month-label" id="monthYear">January 2022</span>
                <button class="nav-btn" onclick="changeMonth(-1)">‹</button>
                <button class="nav-btn" onclick="changeMonth(1)">›</button>
            </div>
        </div>
        
        <div class="grid" id="calendarGrid">
            </div>
    </div>

    <div class="reminders-section">
        <div class="reminder-card">
            <h3>Upcoming Payments</h3>
            <p style="font-size: 12px; opacity: 0.9;">Manage your schedules</p>
        </div>

        <div class="info-box">
            <h4>Today's Due</h4>
            <div id="todayList">
                <p style="font-size:12px; color:#999;">No payments today</p>
            </div>
        </div>

        <div class="info-box">
            <h4>Tomorrow</h4>
            <div id="tomorrowList">
                <p style="font-size:12px; color:#999;">Clear schedule</p>
            </div>
        </div>

       <div class="info-box">
    <h4>Participants</h4>
    <div class="participants">
        <?php 
        // Replace with your actual database results variable
        $participants = []; 

        if (empty($participants)): ?>
            <div class="add-participant-circle" title="Add Participant">+</div>
        <?php else: ?>
            <?php foreach ($participants as $p): ?>
                <div class="avatar" style="background-color: #793bf6;"></div>
            <?php endforeach; ?>
            <div class="add-participant-circle small">+</div>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>

<div class="modal" id="paymentModal">
    <div class="modal-content">
        <h3>Schedule Payment</h3>
        <form method="POST" action="save_schedule.php">
            <input type="hidden" name="date" id="selectedDate">
            <input type="text" name="payment_name" placeholder="Payment Name" required style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:8px;">
            <input type="number" step="0.01" name="amount" placeholder="Amount" required style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:8px;">
            <button type="submit" style="width:100%; background:var(--primary-purple); color:white; border:none; padding:12px; border-radius:10px; cursor:pointer;">Save</button>
            <button type="button" onclick="closeModal()" style="width:100%; background:none; border:none; color:#888; margin-top:10px; cursor:pointer;">Cancel</button>
        </form>
    </div>
</div>

<script>
const grid = document.getElementById("calendarGrid");
const monthYear = document.getElementById("monthYear");
const todayList = document.getElementById("todayList");
const tomorrowList = document.getElementById("tomorrowList");

const realToday = new Date();
let currentDate = new Date();

const payments = <?php echo json_encode($groupedPayments); ?>;

function renderCalendar() {
    grid.innerHTML = `
        <div class="weekday">Sun</div><div class="weekday">Mon</div><div class="weekday">Tue</div>
        <div class="weekday">Wed</div><div class="weekday">Thu</div><div class="weekday">Fri</div><div class="weekday">Sat</div>
    `;

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    monthYear.innerText = currentDate.toLocaleString('default', { month: 'long' }) + " " + year;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Fill empty start
    for (let i = 0; i < firstDay; i++) {
        grid.appendChild(document.createElement("div")).classList.add("day");
    }

    // Fill days
    for (let i = 1; i <= daysInMonth; i++) {
        const dayDiv = document.createElement("div");
        dayDiv.classList.add("day");
        
        const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
        
        if (i === realToday.getDate() && month === realToday.getMonth() && year === realToday.getFullYear()) {
            dayDiv.classList.add("today");
        }

        dayDiv.innerHTML = `<strong>${i}</strong>`;
        
       if (payments[dateStr]) {
    payments[dateStr].forEach(p => {
        const formattedAmount = Number(p.amount).toLocaleString();
        
        dayDiv.innerHTML += `
            <div class="payment-item">
                <div class="item-name">${p.name}</div>
                <div class="item-amount">₱${formattedAmount}</div>
            </div>`;
    });
}

        dayDiv.onclick = () => openModal(year, month, i);
        grid.appendChild(dayDiv);
    }

    updateReminders();
}

function updateReminders() {
    const formatDate = (date) => date.toISOString().split('T')[0];
    
    const todayStr = formatDate(realToday);
    const tomorrow = new Date(realToday);
    tomorrow.setDate(realToday.getDate() + 1);
    const tomorrowStr = formatDate(tomorrow);

    const renderList = (dateStr, element) => {
        if (payments[dateStr]) {
            element.innerHTML = payments[dateStr].map(p => 
                `<div class="info-item"><span>${p.name}</span> <strong>$${p.amount}</strong></div>`
            ).join('');
        }
    };

    renderList(todayStr, todayList);
    renderList(tomorrowStr, tomorrowList);
}

function changeMonth(offset) { currentDate.setMonth(currentDate.getMonth() + offset); renderCalendar(); }
function openModal(y, m, d) { 
    document.getElementById("selectedDate").value = `${y}-${m+1}-${d}`;
    document.getElementById("paymentModal").style.display = "block"; 
}
function closeModal() { document.getElementById("paymentModal").style.display = "none"; }

renderCalendar();
</script>

</body>
</html>