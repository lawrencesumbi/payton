<?php
// Admin User Management

// Handle delete user
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    // Prevent deleting self
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        // Also delete related data if needed
        $stmt = $conn->prepare("DELETE FROM budget WHERE user_id = ? OR sponsor_id = ?");
        $stmt->execute([$user_id, $user_id]);
        $stmt = $conn->prepare("DELETE FROM expenses WHERE user_id = ?");
        $stmt->execute([$user_id]);
        // Add more deletions as needed
        echo "<script>alert('User deleted successfully');</script>";
    } else {
        echo "<script>alert('Cannot delete your own account');</script>";
    }
}

// Handle edit user
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$fullname, $email, $role, $user_id]);
    echo "<script>alert('User updated successfully');</script>";
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, fullname, email, role, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="user-management">
    <h2>User Management</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo $user['created_at']; ?></td>
                <td>
                    <button onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['fullname']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo $user['role']; ?>')">Edit</button>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user" class="danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Edit User</h3>
        <form method="post">
            <input type="hidden" name="user_id" id="edit_user_id">
            <label>Full Name:</label>
            <input type="text" name="fullname" id="edit_fullname" required>
            <label>Email:</label>
            <input type="email" name="email" id="edit_email" required>
            <label>Role:</label>
            <select name="role" id="edit_role">
                <option value="spender">Spender</option>
                <option value="sponsor">Sponsor</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="edit_user">Update</button>
        </form>
    </div>
</div>

<style>
.user-management { padding: 20px; }
table { width: 100%; border-collapse: collapse; background: var(--bg-card); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
th { background: var(--bg-sidebar); font-weight: 600; }
button { background: var(--accent-purple); color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 14px; }
button:hover { background: #8b2fc9; }
button.danger { background: #dc3545; }
button.danger:hover { background: #c82333; }
.modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
.modal-content { background: var(--bg-card); margin: 15% auto; padding: 20px; border: 1px solid var(--border-color); width: 80%; max-width: 500px; border-radius: 8px; }
.close { color: var(--text-muted); float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
.close:hover { color: var(--text-main); }
form label { display: block; margin-top: 10px; }
form input, form select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid var(--border-color); border-radius: 4px; }
</style>

<script>
function editUser(id, fullname, email, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_fullname').value = fullname;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
