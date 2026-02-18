<?php


$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

// fake fallback if not logged in
$user_id = $_SESSION['user_id'] ?? 1;

/* ================= FETCH PAYMENTS ================= */
$stmt = $pdo->prepare("
    SELECT payment_name, amount, payment_date
    FROM scheduled_payments
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* GROUP BY DATE */
$groupedPayments = [];
foreach ($payments as $payment) {
    $date = $payment['payment_date'];
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
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

.calendar {
    width: 90%;
    margin: 30px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
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
    background: #3b82f6;
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.header button:hover {
    background: #1d4ed8;
}


.grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
}

.day {
    background: #f1f1f1;
    height: 130px;
    border-radius: 8px;
    padding: 8px;
    cursor: pointer;
    overflow-y: auto;
    transition: 0.2s;
}


.day:hover {
    background: #dbeafe;
}

.date-number {
    font-weight: bold;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    background: rgba(0,0,0,0.5);
    top: 0; left: 0;
    width: 100%; height: 100%;
}

.modal-content {
    background: white;
    width: 400px;
    margin: 10% auto;
    padding: 20px;
    border-radius: 10px;
}

.modal-content input{
    width: 100%;
    padding: 10px;
    margin-top: 10px;
}

.modal-content button{
    width: 100%;
    padding: 10px;
    margin-top: 10px;
}

.today {
    background: #793bf6 !important;
    color: white;
    
}

.today strong {
    color: #fff;
}

.weekday {
    text-align: center;
    font-weight: bold;
    padding: 8px;
    background: #e5e7eb;
    border-radius: 6px;
}


</style>
</head>

<body>

<div class="calendar">
    <div class="header">
        <button onclick="changeMonth(-1)">◀ Prev</button>
        <span id="monthYear"></span>
        <button onclick="changeMonth(1)">Next ▶</button>
    </div>
    <div class="grid" id="calendarGrid">
        <!-- Weekday Headers -->
        <div class="weekday">Sun</div>
        <div class="weekday">Mon</div>
        <div class="weekday">Tue</div>
        <div class="weekday">Wed</div>
        <div class="weekday">Thu</div>
        <div class="weekday">Fri</div>
        <div class="weekday">Sat</div>
    </div>

</div>

<!-- Modal -->
<div class="modal" id="paymentModal">
    <div class="modal-content">
        <h3>Add Scheduled Payment</h3>
        <form method="POST" action="save_schedule.php">
            <input type="hidden" name="date" id="selectedDate">

            <label>Payment Name</label>
            <input type="text" name="payment_name" required>

            <label>Amount</label>
            <input type="number" step="0.01" name="amount" required>

            <button type="submit">Save Payment</button>
            <button type="button" onclick="closeModal()">Cancel</button>
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
            paymentList += "<ul style='padding-left:15px; margin-top:5px;'>";

            payments[formattedDate].forEach(function(payment){
                const formattedAmount = "₱" + Number(payment.amount).toLocaleString();

                paymentList += `
                    <li style="font-size:12px;">
                        ${payment.name} - <strong>${formattedAmount}</strong>
                    </li>`;
            });

            paymentList += "</ul>";
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
</script>


</body>
</html>
