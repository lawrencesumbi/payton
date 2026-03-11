<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =====================================================
    FETCH ALL BUDGETS (Unified Table Query)
===================================================== */
$stmt = $conn->prepare("
    SELECT b.*, u.fullname as spender_name 
    FROM budget b
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.user_id = ? OR b.sponsor_id = ?
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
        :root {
            --brand-purple: #6f42c1;
            --brand-purple-light: #f3f0ff;
            --brand-purple-dark: #59359a;
            --bg-body: #f8f9fa;
        }

        body { 
            background-color: var(--bg-body); 
            font-family: 'Inter', sans-serif; 
            color: #1a202c; 
        }

        .main-content {
            padding: 40px 20px;
            max-width: 1200px;
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
        }

        /* --- TABLE STYLING --- */
        .table-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            padding: 25px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 20px;
            border: none;
        }

        .table tbody td {
            padding: 18px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .budget-name { font-weight: 700; color: #2d3748; display: block; }
        .date-range { font-size: 0.8rem; color: #94a3b8; }
        .amount-text { font-weight: 700; color: var(--brand-purple); }

        /* --- BUTTONS & FAB --- */
        .btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 1px solid #edf2f7;
            background: white;
            transition: 0.2s;
        }

        .btn-action:hover {
            background: var(--brand-purple-light);
            color: var(--brand-purple);
            border-color: var(--brand-purple);
        }

        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--brand-purple);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 10px 20px rgba(111, 66, 193, 0.3);
            border: none;
            transition: 0.3s;
            z-index: 1050;
        }

        .fab:hover { transform: translateY(-5px); color: white; }

        /* --- MODAL & FORM --- */
        .custom-input {
            background: #f8fafc;
            border: 2px solid #edf2f7;
            border-radius: 12px;
            padding: 12px 15px;
            font-weight: 500;
        }

        .custom-input:focus {
            border-color: var(--brand-purple);
            box-shadow: 0 0 0 4px rgba(111, 66, 193, 0.1);
            outline: none;
        }

        /* CALENDAR CUSTOMIZATION */
        #calendar { padding: 10px; background: #fff; }
        .calendar-header { font-weight: 700; margin-bottom: 10px; }
        .weekdays, .days { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; }
        .weekdays div { font-size: 11px; color: #94a3b8; padding: 5px 0; }
        .day { height: 38px; line-height: 38px; margin: 2px; border-radius: 50%; cursor: pointer; font-size: 13px; }
        .day:hover { background: #f1f5f9; }
        .day.selected { background: var(--brand-purple); color: #fff; }
        .day.in-range { background: var(--brand-purple-light); border-radius: 0; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="header-section">
        <div>
            <h1>Budget Overview</h1>
            <p class="text-muted small m-0">Tracking all active and historical budgets.</p>
        </div>
        <button class="btn btn-dark px-4 py-2" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#createBudgetModal">
            <i class="bi bi-plus-lg me-2"></i> New Budget
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
                                    <?= strtoupper(substr($budget['spender_name'], 0, 2)) ?>
                                </div>
                                <span class="fw-medium"><?= htmlspecialchars($budget['spender_name'] ?? 'N/A') ?></span>
                            </div>
                        </td>
                        <td><?= getStatusBadge($budget['status']) ?></td>
                        <td><span class="amount-text">₱<?= number_format($budget['budget_amount'], 2) ?></span></td>
                        <td class="text-end">
                            <button class="btn-action" onclick="viewBudgetExpenses(<?= $budget['id'] ?>)"><i class="bi bi-eye"></i></button>
                            <button class="btn-action" onclick="editBudget(<?= $budget['id'] ?>, '<?= addslashes($budget['budget_name']) ?>', <?= $budget['budget_amount'] ?>, '<?= $budget['start_date'] ?>', '<?= $budget['end_date'] ?>')"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="delete_budget.php" style="display:inline;">
                                <input type="hidden" name="budget_id" value="<?= $budget['id'] ?>">
                                <button type="submit" class="btn-action" onclick="return confirm('Delete this budget?');"><i class="bi bi-trash text-danger"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No budgets found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<button class="fab" data-bs-toggle="modal" data-bs-target="#createBudgetModal">
    <i class="bi bi-plus-lg"></i>
</button>

<div class="modal fade" id="createBudgetModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; overflow:hidden;">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title fw-800" id="modalTitle">Configure Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form action="add_budget.php" method="POST" id="budgetForm">
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
                            <label class="form-label small fw-700 text-muted">BUDGET NAME</label>
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
                    <button type="submit" id="budgetSubmitBtn" name="add_budget" class="btn btn-dark w-100 py-3 mt-4 fw-700" style="border-radius:14px; background: var(--brand-purple);">Create Budget Record</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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

function updateRange() {
    highlightDays();
    if(startDate && endDate) {
        startInput.value = startDate.toISOString().split('T')[0];
        endInput.value = endDate.toISOString().split('T')[0];
        preview.textContent = startDate.toDateString() + " - " + endDate.toDateString();
    }
}

document.getElementById("clear-date-btn").onclick = () => { startDate = null; endDate = null; preview.textContent = "No dates selected"; renderCalendar(); };

function editBudget(id, name, amount, start, end) {
    const m = new bootstrap.Modal(document.getElementById('createBudgetModal'));
    document.getElementById('budgetForm').action = 'update_budget.php';
    document.getElementById('budget_id').value = id;
    document.getElementById('budget_name').value = name;
    document.getElementById('budget_amount').value = amount;
    startDate = new Date(start); endDate = new Date(end);
    updateRange(); renderCalendar();
    document.getElementById('budgetSubmitBtn').textContent = "Update Budget";
    document.getElementById('budgetSubmitBtn').name = "update_budget";
    m.show();
}

document.getElementById('createBudgetModal').addEventListener('shown.bs.modal', renderCalendar);
document.getElementById('createBudgetModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('budgetForm').reset();
    document.getElementById('budgetForm').action = 'add_budget.php';
    document.getElementById('budgetSubmitBtn').textContent = "Create Budget Record";
});

renderCalendar();
</script>
</body>
</html>