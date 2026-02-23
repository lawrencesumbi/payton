<?php
// ... [Your existing PHP connection and fetch logic] ...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Budget</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_purple.css">
    
    <style>
        :root {
            --primary-purple: #6f42c1;
            --light-purple: #f3f0ff;
            --dark-purple: #59359a;
            --bg-gray: #f8f9fa;
        }

        body { background-color: var(--bg-gray); font-family: 'Inter', sans-serif; }

        .budget-container {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        .card-custom {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 25px;
        }

        .btn-purple {
            background-color: var(--primary-purple);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            border: none;
            width: 100%;
            transition: 0.3s;
        }

        .btn-purple:hover { background-color: var(--dark-purple); color: white; }

        /* Style for the calendar sidebar */
        #calendar-inline { border: none; width: 100%; }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        
        .recent-item:hover { background: var(--light-purple); border-radius: 10px; }
        
        .status-badge {
            background: var(--light-purple);
            color: var(--primary-purple);
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="budget-container">
        
        <div class="left-panel">
            <div class="card-custom mb-4">
                <h4 class="mb-4">Create New Budget</h4>
                <form action="add_budget.php" method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">Budget Name</label>
                            <input type="text" name="budget_name" class="form-control bg-light border-0" placeholder="e.g. Monthly Groceries" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">Budget Amount</label>
                            <input type="number" step="0.01" name="budget_amount" class="form-control bg-light border-0" placeholder="0.00" required>
                        </div>
                        <input type="hidden" name="start_date" id="start_date">
                        <input type="hidden" name="end_date" id="end_date">
                        
                        <div class="col-md-12">
                            <button type="submit" name="add_budget" class="btn btn-purple">Create Budget</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-custom">
                <h5 class="mb-3">Recent Budgets</h5>
                <?php foreach ($budgets as $budget): ?>
                <div class="recent-item">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($budget['budget_name']) ?></div>
                        <small class="text-muted"><?= $budget['start_date'] ?> to <?= $budget['end_date'] ?></small>
                    </div>
                    <div class="status-badge">$<?= number_view($budget['budget_amount']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="right-panel">
            <div class="card-custom">
                <h5 class="mb-3">Select Budget Range</h5>
                <p class="small text-muted">Pick the start and end dates on the calendar below.</p>
                <div id="calendar-inline"></div>
            </div>
        </div>

    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="successToast" class="toast align-items-center text-white bg-purple border-0" style="background-color: var(--primary-purple);" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        ✅ Budget added successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Initialize Inline Calendar for Date Range
    flatpickr("#calendar-inline", {
        inline: true,
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                // Formatting for MySQL
                const start = instance.formatDate(selectedDates[0], "Y-m-d");
                const end = instance.formatDate(selectedDates[1], "Y-m-d");
                document.getElementById('start_date').value = start;
                document.getElementById('end_date').value = end;
            }
        }
    });

    // Show Toast if URL has success parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const toastEl = document.getElementById('successToast');
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
</script>

</body>
</html>