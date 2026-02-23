<?php
// ... [Your existing PDO connection and fetch logic] ...
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM budget WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for progress calculation
function getBudgetProgress($start, $end) {
    $startDate = new DateTime($start);
    $endDate = new DateTime($end);
    $today = new DateTime();

    if ($today < $startDate) return 0;
    if ($today > $endDate) return 100;

    $totalDays = $startDate->diff($endDate)->days ?: 1;
    $passedDays = $startDate->diff($today)->days;
    
    return min(100, max(0, round(($passedDays / $totalDays) * 100)));
}
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

    body { 
        background-color: var(--bg-body); 
        font-family: 'Inter', sans-serif; 
        color: #2d3748;
    }

    .dashboard-container {
        max-width: 1300px;
        margin: 40px auto;
        display: grid;
        grid-template-columns: 1fr 500px; 
        gap: 30px;
        padding: 0 20px;
        align-items: start;
    }

    .panel {
        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0; 
    }

    .card-custom {
        background: #ffffff;
        border-radius: 16px;
        border: none;
        box-shadow: var(--card-shadow);
        padding: 30px;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: #1a202c;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Fixed 500px Calendar & Preview Width */
    #calendar-inline, .selection-box-wrapper { width: 500px; max-width: 100%; border: none; }
    
    .flatpickr-calendar { 
        box-shadow: none !important; 
        width: 500px !important; 
        border: none !important;
        background: transparent !important;
    }
    
    .flatpickr-innerContainer, .flatpickr-rContainer, .flatpickr-days, .dayContainer {
        width: 500px !important;
        min-width: 500px !important;
        max-width: 500px !important;
    }

    .form-control-custom {
        background: #f8fafc;
        border: 2px solid #edf2f7;
        border-radius: 10px;
        padding: 12px;
    }

    .btn-purple {
        background: var(--brand-purple);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-weight: 600;
        transition: 0.3s ease;
    }

    .btn-purple:hover { background: var(--brand-purple-dark); color: white; }

    /* Recent Budget List & Progress Bars */
    .budget-item {
        padding: 18px;
        border-radius: 12px;
        margin-bottom: 12px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
    }

    .progress {
        height: 6px;
        background-color: #e9ecef;
        border-radius: 10px;
        margin-top: 10px;
    }

    .progress-bar { background-color: var(--brand-purple); }

    .amount-badge {
        background: var(--brand-purple-light);
        color: var(--brand-purple);
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 10px;
    }

    .date-error {
        color: #dc3545;
        font-size: 0.85rem;
        font-weight: 500;
        margin-top: 15px;
        padding: 10px;
        background: #fff5f5;
        border-radius: 8px;
        display: none;
    }

    @media (max-width: 1100px) {
        .dashboard-container { grid-template-columns: 1fr; }
        .flatpickr-calendar, .dayContainer, #calendar-inline, .selection-box-wrapper { width: 100% !important; }
    }

    /* Container that forces the box to the edges of the 500px card */
.selection-container {
    margin: 24px -30px -30px -30px; /* Negative margins match the .card-custom padding */
}

.selection-box {
    background: #f8fafc;
    border-top: 1px dashed #dee2e6; /* Separates it from the calendar */
    padding: 20px;
    text-align: center;
    border-bottom-left-radius: 16px; /* Matches card corners */
    border-bottom-right-radius: 16px;
}

.selection-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.selection-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

#range-preview {
    font-weight: 700;
    color: var(--brand-purple);
    font-size: 1rem;
}

#clearDates {
    background: none;
    border: none;
    color: #ef4444;
    padding: 0;
    font-size: 1.2rem;
    cursor: pointer;
    line-height: 1;
    transition: transform 0.2s;
}

#clearDates:hover {
    transform: scale(1.1);
}

</style>
</head>
<body>

<div class="dashboard-container">
    
    <div class="panel">
        <div class="card-custom">
            <div class="section-title">
                <i class="bi bi-plus-circle text-primary"></i> Create Budget
            </div>
            <form action="add_budget.php" method="POST" id="budgetForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">BUDGET NAME</label>
                    <input type="text" name="budget_name" class="form-control form-control-custom" placeholder="e.g. Marketing Q1" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted">TOTAL AMOUNT</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">₱</span>
                        <input type="number" step="0.01" name="budget_amount" class="form-control form-control-custom border-start-0" placeholder="0.00" required>
                    </div>
                </div>

                <input type="hidden" name="start_date" id="start_date">
                <input type="hidden" name="end_date" id="end_date">

                <button type="submit" name="add_budget" class="btn btn-purple w-100">
                    Save Budget Record
                </button>
                <div id="dateErrorMessage" class="date-error">
                    <i class="bi bi-exclamation-circle me-2"></i> Please select a start and end date on the calendar.
                </div>
            </form>
        </div>

        <div class="card-custom">
            <div class="section-title">
                <i class="bi bi-clock-history text-primary"></i> Recent Budgets
            </div>
            <div class="budget-list">
                <?php if(!empty($budgets)): ?>
                    <?php foreach ($budgets as $budget): 
                        $progress = getBudgetProgress($budget['start_date'], $budget['end_date']);
                    ?>
                    <div class="budget-item">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($budget['budget_name']) ?></div>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar3 me-1"></i> <?= $budget['start_date'] ?> — <?= $budget['end_date'] ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="amount-badge mb-1">₱<?= number_format($budget['budget_amount'], 2) ?></div>
                                <div class="small text-muted" style="font-size: 0.7rem;"><?= $progress ?>% Time Elapsed</div>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted py-4">No budgets recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="panel">
    <div class="card-custom">
        <div class="section-title">
            <i class="bi bi-calendar-range text-primary"></i> Timeline Range
        </div>
        <p class="small text-muted mb-4">Select the duration for this budget on the interactive calendar below.</p>
        
        <div id="calendar-inline"></div>

        <div class="selection-container">
            <div class="selection-box">
                <span class="selection-label">SELECTED DURATION</span>
                <div class="selection-content">
                    <span id="range-preview">No dates selected</span>
                    <button type="button" id="clearDates" title="Clear Selection" style="display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px;">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill text-success me-2"></i> Budget successfully added.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    const fp = flatpickr("#calendar-inline", {
        inline: true,
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
            const preview = document.getElementById('range-preview');
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const clearBtn = document.getElementById('clearDates');

            if (selectedDates.length === 2) {
                const start = instance.formatDate(selectedDates[0], "Y-m-d");
                const end = instance.formatDate(selectedDates[1], "Y-m-d");
                
                startInput.value = start;
                endInput.value = end;

                const startDisp = instance.formatDate(selectedDates[0], "M d, Y");
                const endDisp = instance.formatDate(selectedDates[1], "M d, Y");
                preview.innerText = `${startDisp} — ${endDisp}`;
                
                clearBtn.style.display = 'inline-block';
                document.getElementById('dateErrorMessage').style.display = 'none';
            } else {
                startInput.value = "";
                endInput.value = "";
                preview.innerText = "Selecting range...";
                clearBtn.style.display = 'none';
            }
        }
    });

    // Clear Selection logic
    document.getElementById('clearDates').addEventListener('click', function() {
        fp.clear();
        document.getElementById('start_date').value = "";
        document.getElementById('end_date').value = "";
        document.getElementById('range-preview').innerText = "No dates selected";
        this.style.display = 'none';
    });

    document.getElementById('budgetForm').addEventListener('submit', function(e) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (!startDate || !endDate) {
            e.preventDefault();
            document.getElementById('dateErrorMessage').style.display = 'block';
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        new bootstrap.Toast(document.getElementById('successToast')).show();
    }
</script>

</body>
</html>