<?php
// Admin Scheduler Oversight

// Handle delete scheduled payment
if (isset($_POST['delete_schedule'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM scheduled_payments WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>alert('Scheduled payment deleted successfully');</script>";
}

// Fetch all scheduled payments
$stmt = $conn->prepare("
    SELECT sp.id, sp.amount, sp.due_date, sp.payment_name, sp.due_status_id, u.fullname as user_name 
    FROM scheduled_payments sp 
    JOIN users u ON sp.user_id = u.id 
    ORDER BY sp.due_date ASC
");
$stmt->execute();
$scheduled_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="scheduler-oversight">
    <h2>Scheduler Oversight</h2>
    
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($scheduled_payments as $sp): ?>
            <tr>
                <td><?php echo htmlspecialchars($sp['user_name']); ?></td>
                <td>$<?php echo number_format($sp['amount'], 2); ?></td>
                <td><?php echo $sp['due_date']; ?></td>
                <td><?php echo htmlspecialchars($sp['payment_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($sp['due_status_id']); ?></td>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this scheduled payment?')">
                        <input type="hidden" name="id" value="<?php echo $sp['id']; ?>">
                        <button type="submit" name="delete_schedule" class="danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.scheduler-oversight { padding: 20px; }
table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
th { background: var(--bg-sidebar); font-weight: 600; }
button { background: var(--accent-purple); color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 14px; }
button:hover { background: #8b2fc9; }
button.danger { background: #dc3545; }
button.danger:hover { background: #c82333; }
</style>
