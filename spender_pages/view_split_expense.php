    <?php
    require_once "db.php";

    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    if(!isset($_SESSION['user_id'])){
        die("Please login first.");
    }

    $user_id = $_SESSION['user_id'];

    // 1. Fetch all expenses belonging to this user that have splits
    $stmt = $conn->prepare("
        SELECT e.id, e.description, e.amount, e.expense_date
        FROM expenses e
        WHERE e.user_id = ? AND EXISTS (
            SELECT 1 FROM expense_shares es WHERE es.expense_id = e.id
        )
        ORDER BY e.expense_date DESC
    ");
    $stmt->execute([$user_id]);
    $all_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Determine which expense to display
    $expense_id = $_GET['expense_id'] ?? ($all_expenses[0]['id'] ?? null);

    if(!$expense_id){
        die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>
                <h2>No split expenses found.</h2>
                <p>Go to the Split Expenses page to create one!</p>
            </div>");
    }

    // 3. Fetch general expense details
    $stmt = $conn->prepare("
        SELECT e.description, e.amount, e.expense_date, c.category_name, pm.payment_method_name
        FROM expenses e
        JOIN category c ON c.id = e.category_id
        LEFT JOIN payment_method pm ON pm.id = e.payment_method_id
        WHERE e.id = ? AND e.user_id = ?
    ");
    $stmt->execute([$expense_id, $user_id]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Fetch participants and their specific saved shares
    $stmt = $conn->prepare("
        SELECT p.id as person_id, p.name, es.amount_owed, es.status
        FROM expense_shares es
        JOIN people p ON p.id = es.people_id
        WHERE es.expense_id = ?
    ");
    $stmt->execute([$expense_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_others_owe = 0;
    foreach($participants as $p) {
        $total_others_owe += $p['amount_owed'];
    }
    $your_share = $expense['amount'] - $total_others_owe;
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Expense Breakdown</title>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary: #7c3aed; --bg: #f8fafc; --card: #ffffff;
                --text-main: #1e293b; --text-muted: #64748b;
                --success: #22c55e; --danger: #ef4444; --border: #e2e8f0;
            }

            [data-theme="dark"] {
                --bg: #12141a;
                --card: #191c24;
                --text-main: #f8fafc;
                --text-muted: #94a3b8;
                --border: #2a2e39;
            }
            body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); margin: 0; }
            
            html, body { height: 100%; margin: 0; padding: 0; -ms-overflow-style: none; scrollbar-width: none; }
            html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; width: 0 !important; height: 0 !important; }

            .container { width: 100%; padding: 0 20px; box-sizing: border-box; padding-top: 20px; }
            .selector { width: 100%; padding: 14px; border-radius: 12px; border: 2px solid var(--border); background: var(--card); font-weight: 600; font-family: inherit; cursor: pointer; margin-bottom: 24px; }
            .split-container { display: flex; background: var(--card); border-radius: 24px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden; min-height: 460px; }
            .left-col { flex: 1; padding: 40px; background: linear-gradient(145deg, var(--primary), #4f46e5); color: white; display: flex; flex-direction: column; justify-content: center; }
            .left-col h1 { margin: 10px 0; font-size: 2rem; font-weight: 800; }
            .tag { background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
            .detail-item { margin-bottom: 24px; }
            .detail-item label { display: block; font-size: 0.75rem; text-transform: uppercase; opacity: 0.8; letter-spacing: 0.05em; margin-bottom: 4px; }
            .detail-item span { font-size: 1.4rem; font-weight: 600; }
            .right-col { flex: 1.6; padding: 40px; background: var(--card); }
            table { width: 100%; border-collapse: collapse; }
            th { text-align: left; padding: 12px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid var(--border); }
            td { padding: 18px 12px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
            .badge { padding: 6px 12px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
            .badge-paid { background: #dcfce7; color: var(--success); }
            .badge-unpaid { background: #fee2e2; color: var(--danger); }
            .btn-pay { background: #fff; color: var(--primary); border: 1.5px solid var(--primary); padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; cursor: pointer; text-transform: uppercase; transition: 0.2s; }
            .btn-pay:hover { background: var(--primary); color: white; }

            /* TOAST STYLES */
            .toast-container{ position:fixed; top:20px; right:20px; z-index:9999; }
            .custom-toast{ 
                display:flex; align-items:flex-start; gap:10px; background: var(--card); padding:15px; 
                border-radius:10px; margin-bottom:10px; min-width:280px; box-shadow:0 5px 15px rgba(0,0,0,0.1); 
                animation: slideIn 0.3s ease; transition: opacity 0.3s ease; border: 1px solid var(--border);
            }
            @keyframes slideIn{ from{ transform: translateX(100%); opacity:0; } to{ transform: translateX(0); opacity:1; } }
            .toast-success{ border-left:5px solid #22c55e; }
            .toast-error{ border-left:5px solid #ef4444; }
            .toast-title{ font-weight:700; }
            .toast-message{ font-size:14px; color:#555; }
            .toast-close{ margin-left:auto; cursor:pointer; border:none; background:none; font-size:16px; color:#999; }

            @media (max-width: 850px) { .split-container { flex-direction: column; } }

            /* Base styles for the selector */
.selector {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: 2px solid var(--border);
    background-color: var(--card); /* Uses your variable */
    color: var(--text-main);       /* This fixes the visibility */
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    margin-bottom: 24px;
    outline: none;
    transition: all 0.2s ease;
    appearance: none; /* Removes default browser arrow to allow custom styling if desired */
}

/* Fix for the dropdown options specifically */
.selector option {
    background-color: var(--card); /* Ensures dropdown list is dark */
    color: var(--text-main);       /* Ensures text is light */
    padding: 10px;
}

/* Hover/Focus effect to make it feel interactive */
.selector:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

/* Data theme specific override just to be safe */
[data-theme="dark"] .selector {
    background-color: #191c24; /* Explicit dark color if var is acting up */
    color: #f8fafc;
    border-color: #2a2e39;
}
        </style>
    </head>
    <body>

    <div class="toast-container">
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="custom-toast toast-success">
                <div>
                    <div class="toast-title">SUCCESS!</div>
                    <div class="toast-message"><?= $_SESSION['success_msg'] ?></div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">✖</button>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="custom-toast toast-error">
                <div>
                    <div class="toast-title">ERROR</div>
                    <div class="toast-message"><?= $_SESSION['error_msg'] ?></div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">✖</button>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
    </div>

    <div class="container">
        <form method="GET">
            <input type="hidden" name="page" value="view_split_expense">
            <select name="expense_id" class="selector" onchange="this.form.submit()">
                <?php foreach($all_expenses as $ex): ?>
                    <option value="<?= $ex['id'] ?>" <?= $ex['id']==$expense_id?'selected':'' ?>>
                        <?= htmlspecialchars($ex['description']) ?> (₱<?= number_format($ex['amount'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="split-container">
            <div class="left-col">
                <div class="tag"><?= htmlspecialchars($expense['category_name']) ?></div>
                <h1><?= htmlspecialchars($expense['description']) ?></h1>
                
                <div style="margin-top: 30px;">
                    <div class="detail-item">
                        <label>Total Bill</label>
                        <span>₱<?= number_format($expense['amount'], 2) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Date Spent</label>
                        <span><?= date("F d, Y", strtotime($expense['expense_date'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Payment Method</label>
                        <span><?= htmlspecialchars($expense['payment_method_name'] ?? 'Cash') ?></span>
                    </div>
                </div>
            </div>

            <div class="right-col">
                <h3 style="margin-top: 0; margin-bottom: 24px;">Breakdown</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Owe</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">You</td>
                            <td style="font-weight: 700;">₱0.00</td>
                            <td><span class="badge" style="background: #e0e7ff; color: #4338ca;">Paid</span></td>
                            <td style="text-align: right; color: #cbd5e1;">—</td>
                        </tr>

                        <?php foreach($participants as $p): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></td>
                            <td>₱<?= number_format($p['amount_owed'], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($p['status']) ?>">
                                    <?= $p['status'] ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if (strtolower($p['status']) === 'unpaid'): ?>
                                    <button type="button" class="btn-pay" 
                                        onclick="openSettleModal(<?= $p['person_id'] ?>, '<?= htmlspecialchars($p['name']) ?>', <?= $p['amount_owed'] ?>)">
                                        Settle
                                    </button>
                                <?php else: ?>
                                    <span style="color: var(--success); font-weight: bold;">✓ Settled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="settleModal" style="display:none; position:fixed; z-index:10000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:var(--card); padding:30px; border-radius:16px; width:90%; max-width:400px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;">Settle Payment</h2>
            <p id="modalParticipantName" style="color:var(--text-muted);"></p>
            
            <form method="POST" action="process_split.php">
                <input type="hidden" name="expense_id" value="<?= $expense_id ?>">
                <input type="hidden" name="person_id" id="modalPersonId">
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size:0.8rem; margin-bottom:8px;">Amount to Pay (Max: ₱<span id="maxAmountText"></span>)</label>
                    <input type="number" name="settle_amount" id="settleAmountInput" step="0.01" min="0.01" required 
                        style="width:100%; padding:12px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text-main); font-size:1rem;">
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="closeModal()" style="flex:1; padding:12px; border-radius:8px; border:1px solid var(--border); background:none; cursor:pointer; color:var(--text-muted);">Cancel</button>
                    <button type="submit" name="partial_settle" style="flex:1; padding:12px; border-radius:8px; background:var(--primary); color:white; border:none; font-weight:600; cursor:pointer;">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-hide toast logic
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(t => {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            });
        }, 4000);

        function openSettleModal(id, name, remaining) {
            const modal = document.getElementById('settleModal');
            document.getElementById('modalPersonId').value = id;
            document.getElementById('modalParticipantName').innerText = "Settling for " + name;
            document.getElementById('maxAmountText').innerText = remaining.toFixed(2);
            
            const input = document.getElementById('settleAmountInput');
            input.max = remaining;
            input.value = remaining; // Default to full amount
            
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('settleModal').style.display = 'none';
        }

        // Close modal if user clicks outside of the box
        window.onclick = function(event) {
            const modal = document.getElementById('settleModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    </body>
    </html>