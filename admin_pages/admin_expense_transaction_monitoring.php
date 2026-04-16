<?php
// Admin Expense Transaction Monitoring

// Fetch all expenses with details
$stmt = $conn->prepare("
    SELECT e.id, e.amount, e.description, e.created_at as date, e.receipt_upload, u.fullname as user_name, c.category_name 
    FROM expenses e 
    JOIN users u ON e.user_id = u.id 
    JOIN category c ON e.category_id = c.id 
    ORDER BY e.created_at DESC
");
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total expenses
$total_expenses = array_sum(array_column($expenses, 'amount'));
?>

<div class="expense-monitoring">
    <h2>Expense Transaction Monitoring</h2>
    
    <div class="summary">
        <p>Total Expenses: <strong>$<?php echo number_format($total_expenses, 2); ?></strong></p>
        <p>Total Transactions: <strong><?php echo count($expenses); ?></strong></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Category</th>
                <th>Date</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($expenses as $expense): ?>
            <tr>
                <td><?php echo htmlspecialchars($expense['user_name']); ?></td>
                <td>$<?php echo number_format($expense['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                <td><?php echo $expense['date']; ?></td>
                <td>
                    <?php if ($expense['receipt_upload']): ?>
                    <a href="<?php echo htmlspecialchars($expense['receipt_upload']); ?>" target="_blank">View</a>
                    <?php else: ?>
                    N/A
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.expense-monitoring { padding: 20px; }
.summary { background: var(--bg-card); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 20px; }
.summary p { margin: 5px 0; }
table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
th { background: var(--bg-sidebar); font-weight: 600; }
a { color: var(--accent-purple); text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
