<?php
// Admin Relationship Management

// Handle add relationship
if (isset($_POST['add_relationship'])) {
    $sponsor_id = $_POST['sponsor_id'];
    $spender_id = $_POST['spender_id'];
    // Check if relationship already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sponsor_spender WHERE sponsor_id = ? AND spender_id = ?");
    $stmt->execute([$sponsor_id, $spender_id]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $conn->prepare("INSERT INTO sponsor_spender (sponsor_id, spender_id) VALUES (?, ?)");
        $stmt->execute([$sponsor_id, $spender_id]);
        echo "<script>alert('Relationship added successfully');</script>";
    } else {
        echo "<script>alert('Relationship already exists');</script>";
    }
}

// Handle delete relationship
if (isset($_POST['delete_relationship'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM sponsor_spender WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>alert('Relationship deleted successfully');</script>";
}

// Fetch all relationships
$stmt = $conn->prepare("
    SELECT ss.id, s.fullname as sponsor_name, sp.fullname as spender_name, ss.created_at 
    FROM sponsor_spender ss 
    JOIN users s ON ss.sponsor_id = s.id 
    JOIN users sp ON ss.spender_id = sp.id 
    ORDER BY ss.created_at DESC
");
$stmt->execute();
$relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sponsors and spenders for dropdown
$stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'sponsor'");
$stmt->execute();
$sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'spender'");
$stmt->execute();
$spenders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="relationship-management">
    <h2>Relationship Management</h2>
    
    <!-- Add Relationship Form -->
    <div class="add-form">
        <h3>Add New Relationship</h3>
        <form method="post">
            <label>Sponsor:</label>
            <select name="sponsor_id" required>
                <option value="">Select Sponsor</option>
                <?php foreach($sponsors as $sponsor): ?>
                <option value="<?php echo $sponsor['id']; ?>"><?php echo htmlspecialchars($sponsor['fullname']); ?></option>
                <?php endforeach; ?>
            </select>
            <label>Spender:</label>
            <select name="spender_id" required>
                <option value="">Select Spender</option>
                <?php foreach($spenders as $spender): ?>
                <option value="<?php echo $spender['id']; ?>"><?php echo htmlspecialchars($spender['fullname']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_relationship">Add Relationship</button>
        </form>
    </div>
    
    <!-- Relationships Table -->
    <table>
        <thead>
            <tr>
                <th>Sponsor</th>
                <th>Spender</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($relationships as $rel): ?>
            <tr>
                <td><?php echo htmlspecialchars($rel['sponsor_name']); ?></td>
                <td><?php echo htmlspecialchars($rel['spender_name']); ?></td>
                <td><?php echo $rel['created_at']; ?></td>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this relationship?')">
                        <input type="hidden" name="id" value="<?php echo $rel['id']; ?>">
                        <button type="submit" name="delete_relationship" class="danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.relationship-management { padding: 20px; }
.add-form select, .add-form button { padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; }
table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
th { background: var(--bg-sidebar); font-weight: 600; }
button { background: var(--accent-purple); color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 14px; }
button:hover { background: #8b2fc9; }
button.danger { background: #dc3545; }
button.danger:hover { background: #c82333; }
</style>