<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =====================================================
    AUTO UPDATE BUDGET STATUS BASED ON DATE (FIXED)
===================================================== */
$updateStatus = $conn->prepare("
    UPDATE budget
    SET status = 
        CASE
            WHEN CURDATE() BETWEEN start_date AND end_date
            THEN 'Active'
            ELSE 'Inactive'
        END
");
$updateStatus->execute();

/* =====================================================
    FETCH ALL BUDGETS (Unified Table Query)
===================================================== */
$stmt = $conn->prepare("
    SELECT b.*, u.fullname as spender_name 
    FROM budget b
    LEFT JOIN users u ON b.user_id = u.id
    WHERE (b.user_id = ? OR b.sponsor_id = ?) 
    AND b.status = 'Active'
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$allBudgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($status) {
    $class = ($status == 'Active') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
    return "<span class='badge $class' style='font-weight:600; padding: 6px 12px; border-radius: 6px;'>$status</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management | Payton</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* ===== 1. THEME VARIABLES ===== */
    :root {
        --brand-purple: #6f42c1;
        --brand-purple-light: #f3f0ff;
        --brand-purple-dark: #59359a;
        --bg-body: #f8f9fa;
        --card-bg: #ffffff;
        --text-main: #1a202c;
        --text-muted: #64748b;
        --border-color: #edf2f7;
        --input-bg: #f8fafc;
        --header-bg: #f8fafc;
    }

    [data-theme="dark"] {
        --bg-body: #0f111a;
        --card-bg: #191c24;
        --text-main: #f8fafc;
        --text-muted: #cbd5e1; 
        --border-color: #2a2e39;
        --input-bg: #12141a;
        --header-bg: #242833;
        --brand-purple-light: rgba(111, 66, 193, 0.2);
    }

    /* ===== 2. GLOBAL & SCROLLBAR ===== */
    body { 
        background-color: var(--bg-body); 
        font-family: 'Inter', sans-serif; 
        color: var(--text-main); 
        transition: background 0.3s ease, color 0.3s ease;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        -ms-overflow-style: none;  
        scrollbar-width: none;  
    }

    html::-webkit-scrollbar, body::-webkit-scrollbar {
        display: none;
    }

    .main-content {
        padding: 0 20px;
        width: 100%;
        margin: 0 auto;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header-section h1 {
        font-weight: 800;
        font-size: 1.75rem;
        letter-spacing: -0.5px;
        margin: 0;
        color: var(--text-main);
    }

    .header-section p {
        font-size: 15px;
        letter-spacing: -0.5px;
        margin: 0;
        color: var(--text-muted) !important;
    }

    /* ===== 3. TABLE & CONTAINERS ===== */
    .table-container {
        background: var(--card-bg) !important;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 25px;
        border: 1px solid var(--border-color);
    }

    .table { color: var(--text-main) !important; margin-bottom: 0; }

    .table thead th {
        background: var(--header-bg) !important;
        color: var(--text-muted) !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 15px 20px;
        border: none;
    }

    .table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color) !important;
        background: transparent !important;
        color: var(--text-main) !important;
    }

    .budget-name { font-weight: 700; color: var(--text-main); display: block; }
    .date-range { font-size: 0.8rem; color: var(--text-muted); }
    .amount-text { font-weight: 700; color: var(--brand-purple); }

    /* ===== 4. FORMS & MODALS ===== */
    .modal-content {
        background-color: var(--card-bg) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 24px !important;
    }

    .form-label, label {
        color: var(--text-muted) !important;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .custom-input, .form-select {
        background: var(--input-bg) !important;
        border: 2px solid var(--border-color) !important;
        color: var(--text-main) !important;
        border-radius: 12px;
        padding: 12px 15px;
    }

    .custom-input:focus {
        border-color: var(--brand-purple);
        box-shadow: 0 0 0 4px rgba(111, 66, 193, 0.1);
        outline: none;
    }

    /* ===== 5. CALENDAR FIXES ===== */
    #calendar { padding: 10px; background: transparent; color: var(--text-main); }
    
    .weekdays, .days { 
        display: grid !important; 
        grid-template-columns: repeat(7, 1fr) !important; 
        text-align: center; 
    }

    .day { 
        height: 38px; 
        line-height: 38px; 
        margin: 2px auto; 
        border-radius: 50%; 
        cursor: pointer; 
        font-size: 13px;
        color: var(--text-main);
        width: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .day:hover { background: var(--header-bg); }
    .day.selected { background: var(--brand-purple) !important; color: #fff !important; }
    .day.in-range { background: var(--brand-purple-light) !important; border-radius: 0; color: var(--brand-purple); }

    .bg-white { background-color: var(--card-bg) !important; }
    .bg-light { background-color: var(--header-bg) !important; color: var(--text-main) !important; }

    /* ===== 6. ACTIONS & FAB ===== */
    .btn-action {
        width: 36px; height: 36px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; border: 1px solid var(--border-color);
        background: var(--card-bg); color: var(--text-main);
        transition: 0.2s;
        cursor: pointer;
    }

    .btn-action:hover {
        background: var(--brand-purple);
        color: white !important;
        border-color: var(--brand-purple);
    }

    /* ===== 7. CUSTOM TOAST DESIGN ===== */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .custom-toast {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: var(--card-bg);
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 10px;
        min-width: 280px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        animation: slideInToast 0.3s ease forwards;
        border: 1px solid var(--border-color);
    }

    @keyframes slideInToast {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .toast-success { border-left: 5px solid #22c55e; }
    .toast-error { border-left: 5px solid #ef4444; }

    .toast-title { font-weight: 800; font-size: 12px; letter-spacing: 0.5px; margin-bottom: 2px; }
    .toast-message { font-size: 14px; color: var(--text-muted); }

    .toast-close {
        margin-left: auto;
        cursor: pointer;
        border: none;
        background: none;
        color: var(--text-muted);
        font-size: 16px;
        padding: 0;
        line-height: 1;
    }
</style>
</head>
<body>

<div class="toast-container">
    <?php 
    $success = $_SESSION['success_msg'] ?? $_SESSION['toast_success'] ?? null;
    $error = $_SESSION['error_msg'] ?? $_SESSION['toast_error'] ?? null;
    ?>

    <?php if($success): ?>
        <div class="custom-toast toast-success">
            <div>
                <div class="toast-title text-success">SUCCESS!</div>
                <div class="toast-message"><?= htmlspecialchars($success) ?></div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">✖</button>
        </div>
        <?php unset($_SESSION['success_msg'], $_SESSION['toast_success']); ?>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="custom-toast toast-error">
            <div>
                <div class="toast-title text-danger">ERROR</div>
                <div class="toast-message"><?= htmlspecialchars($error) ?></div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">✖</button>
        </div>
        <?php unset($_SESSION['error_msg'], $_SESSION['toast_error']); ?>
    <?php endif; ?>
</div>

<div class="main-content">
    <div class="header-section mt-4">
        <div>
            <h1>Allowance Overview</h1>
            <p>Tracking all active allowance.</p>
        </div>
        <button class="btn px-4 py-2" 
            style="border-radius: 10px; background-color: #6f42c1; color: white;"
            data-bs-toggle="modal" 
            data-bs-target="#createBudgetModal">
            <i class="bi bi-plus-lg me-2"></i> New Allowance
        </button>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Spender</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($allBudgets)): foreach($allBudgets as $budget): ?>
                    <tr>
                        <td>
                            <span class="budget-name"><?= htmlspecialchars($budget['budget_name']) ?></span>
                            <span class="date-range"><?= date("M d", strtotime($budget['start_date'])) ?> — <?= date("M d, Y", strtotime($budget['end_date'])) ?></span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.7rem; font-weight: 800; color: var(--brand-purple);">
                                    <?= strtoupper(substr($budget['spender_name'] ?? 'NA', 0, 2)) ?>
                                </div>
                                <span class="fw-medium"><?= htmlspecialchars($budget['spender_name'] ?? 'N/A') ?></span>
                            </div>
                        </td>
                        <td><?= getStatusBadge($budget['status']) ?></td>
                        <td><span class="amount-text">₱<?= number_format($budget['budget_amount'], 2) ?></span></td>
                        <td class="text-end">
                            <a href="sponsor.php?page=monitoring_page&spender_id=<?= $budget['user_id'] ?>&allowance_id=<?= $budget['id'] ?>" class="btn-action">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <button class="btn-action" onclick="editBudget(
                                <?= $budget['id'] ?>,
                                '<?= addslashes($budget['budget_name']) ?>',
                                <?= $budget['budget_amount'] ?>,
                                '<?= $budget['start_date'] ?>',
                                '<?= $budget['end_date'] ?>',
                                <?= $budget['user_id'] ?>
                            )"><i class="bi bi-pencil"></i></button>

                            <form method="POST" action="allowance_process.php" style="display:inline;">
                                <input type="hidden" name="budget_id" value="<?= $budget['id'] ?>">
                                <button type="submit" name="delete_budget" class="btn-action" onclick="return confirm('Delete this allowance?');">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No allowance found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; overflow:hidden;">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title fw-800" id="modalTitle">Create New Allowance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form action="allowance_process.php" method="POST" id="budgetForm">
                    <input type="hidden" name="budget_id" id="budget_id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-700 text-muted">SELECT SPENDER</label>
                            <select name="spender_id" id="spender_id" class="form-select custom-input" required>
                                <option value="">-- Choose Spender --</option>
                                <?php
                                $stmtS = $conn->prepare("SELECT u.id, u.fullname FROM users u INNER JOIN sponsor_spender ss ON u.id = ss.spender_id WHERE ss.sponsor_id = ?");
                                $stmtS->execute([$user_id]);
                                while($s = $stmtS->fetch(PDO::FETCH_ASSOC)) echo "<option value='{$s['id']}'>".htmlspecialchars($s['fullname'])."</option>";
                                ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-700 text-muted">ALLOWANCE NAME</label>
                            <input type="text" name="budget_name" id="budget_name" class="form-control custom-input" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-700 text-muted">AMOUNT</label>
                            <input type="number" step="0.01" name="budget_amount" id="budget_amount" class="form-control custom-input" required>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="form-label small fw-700 text-muted">TIMELINE RANGE</label>
                            <div class="border rounded-4 p-2 bg-white"><div id="calendar"></div></div>
                            <div class="d-flex justify-content-between align-items-center mt-2 p-3 bg-light rounded-3">
                                <span id="range-preview" class="small fw-700 text-primary">No dates selected</span>
                                <button type="button" id="clear-date-btn" class="btn btn-sm btn-link text-danger text-decoration-none fw-700">Clear</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="start_date" id="start_date">
                    <input type="hidden" name="end_date" id="end_date">
                    <button type="submit" id="budgetSubmitBtn" name="add_budget" class="btn w-100 py-3 mt-4 fw-700" style="border-radius:14px; background: var(--brand-purple); color: white;">Create Budget Record</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide custom toast logic
setTimeout(() => {
    document.querySelectorAll('.custom-toast').forEach(t => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(20px)';
        t.style.transition = 'all 0.3s ease';
        setTimeout(() => t.remove(), 300);
    });
}, 3000);

let startDate = null, endDate = null;
let current = new Date(), year = current.getFullYear(), month = current.getMonth();
const calendar = document.getElementById("calendar"), preview = document.getElementById("range-preview");
const startInput = document.getElementById("start_date"), endInput = document.getElementById("end_date");

function renderCalendar() {
    calendar.innerHTML = "";
    const header = document.createElement("div");
    header.className = "calendar-header d-flex justify-content-between px-2";
    header.innerHTML = `<span onclick="changeMonth(-1)" style="cursor:pointer">←</span><span>${new Date(year, month).toLocaleString('default', { month: 'long', year: 'numeric' })}</span><span onclick="changeMonth(1)" style="cursor:pointer">→</span>`;
    calendar.appendChild(header);

    const weekdays = document.createElement("div");
    weekdays.className = "weekdays";
    ["S","M","T","W","T","F","S"].forEach(d => weekdays.innerHTML += `<div>${d}</div>`);
    calendar.appendChild(weekdays);

    const daysGrid = document.createElement("div");
    daysGrid.className = "days";
    const firstDay = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();

    for(let i=0; i<firstDay; i++) daysGrid.appendChild(document.createElement("div"));
    for(let d=1; d<=totalDays; d++) {
        const date = new Date(year, month, d);
        const dayEl = document.createElement("div");
        dayEl.className = "day";
        dayEl.textContent = d;
        dayEl.onclick = () => {
            if(!startDate || (startDate && endDate)) { startDate = date; endDate = null; }
            else { endDate = date; if(endDate < startDate) [startDate, endDate] = [endDate, startDate]; }
            updateRange();
        };
        daysGrid.appendChild(dayEl);
    }
    calendar.appendChild(daysGrid);
    highlightDays();
}

function changeMonth(dir) { month += dir; if(month<0){month=11; year--;} if(month>11){month=0; year++;} renderCalendar(); }
function highlightDays() {
    document.querySelectorAll(".day").forEach(el => {
        const d = new Date(year, month, parseInt(el.textContent));
        el.classList.remove("selected", "in-range");
        if(startDate && d.getTime() === startDate.getTime()) el.classList.add("selected");
        if(endDate && d.getTime() === endDate.getTime()) el.classList.add("selected");
        if(startDate && endDate && d > startDate && d < endDate) el.classList.add("in-range");
    });
}
function formatLocalDate(date){
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2,'0');
    const d = String(date.getDate()).padStart(2,'0');
    return `${y}-${m}-${d}`;
}
function updateRange() {
    highlightDays();
    if(startDate && endDate) {
        startInput.value = formatLocalDate(startDate);
        endInput.value = formatLocalDate(endDate);
        preview.textContent = startDate.toDateString() + " - " + endDate.toDateString();
    }
}

document.getElementById("clear-date-btn").onclick = () => { startDate = null; endDate = null; preview.textContent = "No dates selected"; renderCalendar(); };
function parseLocalDate(dateString) {
    const parts = dateString.split("-");
    return new Date(parts[0], parts[1]-1, parts[2]);
}

function editBudget(id, name, amount, start, end, spender_id) {
    const modal = new bootstrap.Modal(document.getElementById('createBudgetModal'));
    document.getElementById('budget_id').value = id;
    document.getElementById('budget_name').value = name;
    document.getElementById('budget_amount').value = amount;
    document.getElementById('spender_id').value = spender_id;
    startDate = parseLocalDate(start);
    endDate = parseLocalDate(end);
    updateRange();
    renderCalendar();
    document.getElementById('modalTitle').textContent = "Update Allowance Record";
    document.getElementById('budgetSubmitBtn').textContent = "Update Budget";
    document.getElementById('budgetSubmitBtn').name = "update_budget";
    modal.show();
}

document.getElementById('createBudgetModal').addEventListener('shown.bs.modal', renderCalendar);
document.getElementById('createBudgetModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('budgetForm').reset();
    document.getElementById('modalTitle').textContent = "Create New Allowance";
    document.getElementById('budgetSubmitBtn').textContent = "Create Budget Record";
    document.getElementById('budgetSubmitBtn').name = "add_budget";
    startDate = null; endDate = null;
    updateRange();
});

renderCalendar();
</script>
</body>
</html>