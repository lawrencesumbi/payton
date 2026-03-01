<?php
/* =====================================================
    DATABASE CONNECTION
===================================================== */
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =====================================================
    FETCH ALL USER BUDGETS
===================================================== */
$stmt = $pdo->prepare("
    SELECT *
    FROM budget
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$all_budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
    HELPER: PROGRESS CALCULATION
===================================================== */
function getBudgetProgress($start, $end) {
    $startDate = new DateTime($start);
    $endDate   = new DateTime($end);
    $today     = new DateTime();

    if ($today < $startDate) return 0;
    if ($today > $endDate) return 100;

    $totalDays  = $startDate->diff($endDate)->days ?: 1;
    $passedDays = $startDate->diff($today)->days;

    return min(100, max(0, round(($passedDays / $totalDays) * 100)));
}

/* =====================================================
    SEPARATE RECENT + HISTORY & CALCULATE DASHBOARD
===================================================== */
$recentBudgets = [];
$historyBudgets = [];
$total_budgeted = 0;

foreach ($all_budgets as $budget) {
    $progress = getBudgetProgress($budget['start_date'], $budget['end_date']);
    if ($progress >= 100) {
        $historyBudgets[] = $budget;
    } else {
        $recentBudgets[] = $budget;
        $total_budgeted += $budget['budget_amount']; // Sum for active dashboard
    }
}

/* =====================================================
    GET ACTIVE BUDGET (LATEST RUNNING TIMELINE)
===================================================== */
$activeBudget = !empty($recentBudgets) ? $recentBudgets[0] : null;

/* =====================================================
    FETCH TIMELINE DEDUCTIONS & DASHBOARD TOTALS
===================================================== */
$timelineExpenses = [];
$total_spent = 0;

if ($activeBudget) {
    $timelineStmt = $pdo->prepare("
        SELECT description, amount, expense_date
        FROM expenses
        WHERE user_id = ?
        AND expense_date BETWEEN ? AND ?
        ORDER BY expense_date DESC
    ");
    $timelineStmt->execute([
        $user_id,
        $activeBudget['start_date'],
        $activeBudget['end_date']
    ]);
    $timelineExpenses = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($timelineExpenses as $exp) {
        $total_spent += $exp['amount'];
    }
}
$remaining_balance = $total_budgeted - $total_spent;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Budget | Professional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_purple.css">
    
<style>
    :root {
        --brand-purple: #6f42c1;
        --brand-purple-light: #f3f0ff;
        --brand-purple-dark: #59359a;
        --bg-body: #f4f7fe;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: #2d3748; }

    .dashboard-container {
        max-width: 1300px;
        margin: 40px auto;
        display: grid;
        grid-template-columns: 1fr 500px; 
        gap: 30px;
        padding: 0 20px;
        align-items: start;
    }

    .panel { display: flex; flex-direction: column; gap: 24px; min-width: 0; }

    /* IMAGE-BASED CALENDAR DESIGN */
.card-custom {
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    padding: 24px;
}

/* Container stretch */
#calendar-inline { width: 100% !important; }

/* ALIGNMENT FIX: WEEKDAYS & DATES */
.flatpickr-calendar {
  
    width: 100% !important;
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
}

/* 1. Header & Weekday Row Alignment */
.flatpickr-weekdays {
    width: 100% !important;
    background: transparent !important;
    margin-bottom: 10px;
}

.flatpickr-weekday {
    flex: 1 !important; /* Forces 7 equal columns */
    text-align: center !important;
    color: #94a3b8 !important;
    font-weight: 500 !important;
    font-size: 0.8rem !important;
}

/* 2. Day Container Alignment */
.dayContainer {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    display: flex !important;
    flex-wrap: wrap !important;
    justify-content: start !important; /* Standardize starting point */
}

.flatpickr-day {
    flex: 1 0 14.28% !important; /* 100% divided by 7 days */
    max-width: 14.28% !important;
    height: 48px !important;
    line-height: 48px !important;
    margin: 4px 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    border: none !important;
    font-weight: 500;
    border-radius: 50% !important; /* Circle style from image */
}

/* 3. Image Theme Selection Colors */
.flatpickr-day.selected, 
.flatpickr-day.startRange, 
.flatpickr-day.endRange {
    background: #5d45d7 !important; /* Vibrant Purple */
    color: #fff !important;
}

.flatpickr-day.inRange {
    background: #eeebff !important; /* Light wash */
    color: #5d45d7 !important;
    border-radius: 0 !important; /* Connects the bar */
}

/* Remove default focus outlines for a cleaner look */
.flatpickr-day:focus {
    background: #f1f5f9;
}

/* Selection Box Footer */
.selection-box {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-clear-date {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    transition: 0.2s;
}

.btn-clear-date:hover { background: #fecaca; }

    

    /* REDUCED HEIGHT BUDGET AREA */
    .budget-scroll-area {
        height: 140px; 
        overflow-y: auto;
        padding-right: 8px;
    }
    .budget-scroll-area::-webkit-scrollbar { width: 4px; }
    .budget-scroll-area::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

    .budget-item {
        padding: 14px;
        border-radius: 12px;
        margin-bottom: 10px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }

    /* PROFESSIONAL KPI DASHBOARD */
    /* PROFESSIONAL KPI DASHBOARD - REFINED */
.kpi-grid { 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 12px; 
}

.kpi-card-mini {
    background: #ffffff;
    padding: 12px 16px; /* Balanced padding */
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02); /* Softer, modern shadow */
    border: 1px solid #f1f5f9; /* Subtle border instead of heavy left-bar */
    transition: transform 0.2s ease;
}

.kpi-label { 
    font-size: 0.62rem; 
    font-weight: 600; /* Medium weight instead of Ultra-Bold */
    color: #64748b; 
    text-transform: uppercase; 
    letter-spacing: 0.8px; /* Increased tracking for readability */
    margin-bottom: 2px;
    display: block;
}

.kpi-value { 
    font-size: 1rem; /* Slightly smaller for a cleaner look */
    font-weight: 500; /* Clean semi-bold */
    color: #0f172a; 
    display: block;
    letter-spacing: -0.3px;
}

/* Color accents using tiny subtle indicators */
.kpi-card-mini::before {
    content: "";
    display: block;
    width: 12px;
    height: 2px;
    border-radius: 10px;
    margin-bottom: 8px;
}

.kpi-budget::before { background: var(--brand-purple); }
.kpi-spent::before { background: #ef4444; }
.kpi-remaining::before { background: #22c55e; }

    .tab-header { display: flex; gap: 20px; border-bottom: 2px solid #edf2f7; margin-bottom: 20px; }
    .tab-btn { padding-bottom: 10px; cursor: pointer; font-weight: 700; color: #94a3b8; border-bottom: 3px solid transparent; transition: 0.3s; }
    .tab-btn.active { color: var(--brand-purple); border-bottom-color: var(--brand-purple); }

    .section-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .progress { height: 6px; background-color: #e9ecef; border-radius: 10px; margin-top: 10px; }
    .progress-bar { background-color: var(--brand-purple); }
    .amount-badge { background: var(--brand-purple-light); color: var(--brand-purple); font-weight: 700; padding: 4px 10px; border-radius: 8px; font-size: 0.9rem; }

    .form-control-custom { background: #f8fafc; border: 2px solid #edf2f7; border-radius: 10px; padding: 12px; }
    .btn-purple { background: var(--brand-purple); color: white; border: none; border-radius: 10px; padding: 14px; font-weight: 600; }
    .selection-box { background: #f8fafc; border-top: 1px dashed #dee2e6; padding: 20px; text-align: center; margin: 24px -30px -30px -30px; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; }

    .btn-clear-date { background: #fee2e2; color: #ef4444; border: none; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: none; }
</style>
</head>
<body>

<div class="dashboard-container">
    <div class="panel">
        <div class="card-custom">
            <div class="tab-header">
                <div class="tab-btn active" onclick="switchBudgetView('recent', this)">Recent Budgets</div>
                <div class="tab-btn" onclick="switchBudgetView('history', this)">Budget History</div>
            </div>
            
            <div class="budget-scroll-area">
                <div id="view-recent">
                    <?php if(!empty($recentBudgets)): foreach ($recentBudgets as $budget): 
                        $progress = getBudgetProgress($budget['start_date'], $budget['end_date']); ?>
                        <div class="budget-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold small"><?= htmlspecialchars($budget['budget_name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?= $progress ?>% Time Passed</div>
                                </div>
                                <div class="amount-badge">₱<?= number_format($budget['budget_amount'], 2) ?></div>
                            </div>
                            <div class="progress"><div class="progress-bar" style="width: <?= $progress ?>%"></div></div>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="text-center text-muted py-4">No active budgets.</p>
                    <?php endif; ?>
                </div>

                <div id="view-history" class="d-none">
                    <?php if(!empty($historyBudgets)): foreach ($historyBudgets as $budget): ?>
                        <div class="budget-item opacity-75">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-muted small"><?= htmlspecialchars($budget['budget_name']) ?></div>
                                <div class="fw-bold small">₱<?= number_format($budget['budget_amount'], 2) ?></div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="text-center text-muted py-4">No history found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

            <div class="kpi-grid">
                <div class="kpi-card-mini kpi-budget">
                    <span class="kpi-label">Active Budget</span>
                    <span class="kpi-value">₱<?= number_format($total_budgeted, 2) ?></span>
                </div>
                <div class="kpi-card-mini kpi-spent">
                    <span class="kpi-label">Current Spent</span>
                    <span class="kpi-value text-danger">-₱<?= number_format($total_spent, 2) ?></span>
                </div>
                <div class="kpi-card-mini kpi-remaining">
                    <span class="kpi-label">Available</span>
                    <span class="kpi-value text-success">₱<?= number_format($remaining_balance, 2) ?></span>
                </div>
            </div>

        <div class="card-custom">
            <div class="section-title"><i class="bi bi-list-check text-primary"></i> Timeline Deductions</div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr class="small text-muted" style="font-size: 0.75rem;">
                            <th>DEDUCTION NAME</th>
                            <th>AMOUNT</th>
                            <th>DATE ADDED</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($timelineExpenses)): foreach ($timelineExpenses as $exp): ?>
                            <tr>
                                <td class="fw-semibold small"><?= htmlspecialchars($exp['description']) ?></td>
                                <td class="text-danger fw-bold">-₱<?= number_format($exp['amount'], 2) ?></td>
                                <td class="text-muted small"><?= date("F j, Y", strtotime($exp['expense_date'])) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No deductions available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="card-custom">
            <div class="section-title"><i class="bi bi-plus-circle text-primary"></i> Create Budget</div>
            <form action="add_budget.php" method="POST" id="budgetForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">BUDGET NAME</label>
                    <input type="text" name="budget_name" class="form-control form-control-custom" placeholder="e.g. Monthly Savings" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted">TOTAL AMOUNT</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">₱</span>
                        <input type="number" step="0.01" name="budget_amount" class="form-control form-control-custom border-start-0" placeholder="0.00" required>
                    </div>
                </div>
                <input type="hidden" name="start_date" id="start_date">
                <input type="hidden" name="end_date" id="end_date">
                <button type="submit" name="add_budget" class="btn btn-purple w-100">Save Budget Record</button>
                <div id="date-error-msg" class="text-danger small fw-bold mt-2 text-center" style="display: none;"><i class="bi bi-exclamation-circle me-1"></i> Selection of Timeline Range is required!</div>
            </form>
        </div>

        <div class="card-custom">
            <div class="section-title"><i class="bi bi-calendar-range text-primary"></i> Timeline Range</div>
            <div id="calendar-inline"></div>
            <div class="selection-box">
                <span id="range-preview" class="fw-bold" style="color:var(--brand-purple)">No dates selected</span>
                <button type="button" id="clear-date-btn" class="btn-clear-date"><i class="bi bi-x"></i> Clear</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    function switchBudgetView(view, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('view-recent').classList.toggle('d-none', view !== 'recent');
        document.getElementById('view-history').classList.toggle('d-none', view !== 'history');
    }

const fp = flatpickr("#calendar-inline", {
    inline: true,
    mode: "range",
    dateFormat: "Y-m-d",
    onReady: function(s, d, instance) {
        instance.redraw();
    },
    onMonthChange: function(s, d, instance) {
        instance.redraw();
    },
    onChange: function(selectedDates, dateStr, instance) {
        const preview = document.getElementById('range-preview');
        const clearBtn = document.getElementById('clear-date-btn');
        
        if (selectedDates.length === 2) {
            // Update hidden inputs for your PHP form
            document.getElementById('start_date').value = instance.formatDate(selectedDates[0], "Y-m-d");
            document.getElementById('end_date').value = instance.formatDate(selectedDates[1], "Y-m-d");
            
            // Format preview like the image: "July 14 - 20"
            const month = instance.formatDate(selectedDates[0], "F");
            const startDay = instance.formatDate(selectedDates[0], "j");
            const endDay = instance.formatDate(selectedDates[1], "j");
            
            preview.innerText = `Range: ${month} ${startDay} - ${endDay}`;
            clearBtn.style.display = "inline-block";
        } else {
            clearBtn.style.display = "none";
            preview.innerText = "No dates selected";
        }
    }
});

    document.getElementById('clear-date-btn').addEventListener('click', function() {
        fp.clear();
        document.getElementById('start_date').value = "";
        document.getElementById('end_date').value = "";
        document.getElementById('range-preview').innerText = "No dates selected";
        this.style.display = "none";
    });

    document.getElementById('budgetForm').addEventListener('submit', function(e) {
        if (!document.getElementById('start_date').value || !document.getElementById('end_date').value) {
            e.preventDefault();
            document.getElementById('date-error-msg').style.display = "block";
        }
    });
</script>
</body>
</html>