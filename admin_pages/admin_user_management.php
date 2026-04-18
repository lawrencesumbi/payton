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
$searchTerm = $_GET['search'] ?? '';

$query = "SELECT id, fullname, email, role, created_at FROM users WHERE 1=1";

if (!empty($searchTerm)) {
    $query .= " AND (fullname LIKE ? OR email LIKE ? OR role LIKE ?)";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$params = [];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard];
}

$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="user-management">
    <header class="page-header">
        <div class="header-left">
            <span class="badge-accent">User Management</span>
            <h1>Platform Users</h1>
            <p>Manage user accounts, roles, and access permissions across the system.</p>
        </div>
        <div class="header-right">
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>
    </header>

    <div class="glass-card table-container">
        <div class="table-header">
            <h3>All Users</h3>
            <span class="record-count"><?php echo count($users); ?> users</span>
        </div>
        
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo strtoupper(substr($user['fullname'], 0, 1)); ?></div>
                                <span><?php echo htmlspecialchars($user['fullname']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['fullname'])); ?>', '<?php echo htmlspecialchars(addslashes($user['email'])); ?>', '<?php echo $user['role']; ?>')" class="btn-edit">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(empty($users)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-users-slash"></i>
            <h4>No users found</h4>
            <p>The platform currently has no registered users.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User Account</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="post" class="edit-form">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div class="form-group">
                <label for="edit_fullname">Full Name</label>
                <input type="text" name="fullname" id="edit_fullname" required>
            </div>
            
            <div class="form-group">
                <label for="edit_email">Email Address</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            
            <div class="form-group">
                <label for="edit_role">User Role</label>
                <select name="role" id="edit_role">
                    <option value="spender">Spender</option>
                    <option value="sponsor">Sponsor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn-cancel">Cancel</button>
                <button type="submit" name="edit_user" class="btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<style>
:root {
    --bg-main: #f8fafc;
    --card-bg: #ffffff;
    --primary: #6366f1;
    --text-dark: #0f172a;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --radius-lg: 24px;
    --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.04);
    --danger: #dc2626;
    --success: #10b981;
    --warning: #f59e0b;
}

[data-theme="dark"] {
    --bg-main: #0f111a;
    --card-bg: #191c24;
    --text-dark: #f8fafc;
    --text-muted: #94a3b8;
    --border: #2a2e39;
}

.user-management { 
    font-family: 'Inter', sans-serif;
    background: var(--bg-main);
    padding: 32px;
    color: var(--text-dark);
    min-height: 100vh;
}

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.badge-accent { 
    background: #e0e7ff; color: var(--primary); 
    padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
}
.page-header h1 { font-size: 32px; margin: 8px 0; font-weight: 800; letter-spacing: -0.03em; }
.page-header p { color: var(--text-muted); margin: 0; font-size: 15px; }

.btn-refresh {
    background: var(--card-bg); border: 1px solid var(--border);
    padding: 12px 20px; border-radius: 14px; font-weight: 600; cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;
}
.btn-refresh:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Table Container */
.glass-card { 
    background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); overflow: hidden;
}

.table-container { margin-bottom: 32px; }
.table-header { 
    padding: 24px; border-bottom: 1px solid var(--border); 
    display: flex; justify-content: space-between; align-items: center;
}
.table-header h3 { font-size: 17px; margin: 0; font-weight: 700; }
.record-count { font-size: 13px; color: var(--text-muted); font-weight: 500; }

.table-wrapper { overflow-x: auto; }
.modern-table { 
    width: 100%; border-collapse: collapse; 
}
.modern-table thead th { 
    background: var(--card-bg); color: var(--text-dark); font-weight: 600; font-size: 14px;
    padding: 16px 24px; text-align: left; border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 10;
}
.modern-table tbody td { 
    padding: 16px 24px; border-bottom: 1px solid var(--border); 
    vertical-align: middle;
}
.modern-table tbody tr:hover { background: var(--bg-main); }

.modern-table code { 
    background: var(--bg-main); color: var(--text-muted); padding: 2px 6px; 
    border-radius: 4px; font-size: 12px; font-weight: 500;
}

/* User Cell */
.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar { 
    width: 32px; height: 32px; background: var(--bg-main); border-radius: 8px;
    display: grid; place-items: center; font-weight: 700; font-size: 12px; color: var(--primary);
    border: 1px solid var(--border);
}

/* Role Badges */
.role-badge { 
    padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 600; 
    text-transform: uppercase; letter-spacing: 0.05em;
}
.role-admin { background: #fef3c7; color: var(--warning); }
.role-sponsor { background: #ecfdf5; color: var(--success); }
.role-spender { background: #e0e7ff; color: var(--primary); }

/* Action Buttons */
.action-buttons { display: flex; gap: 8px; }
.btn-edit, .btn-danger {
    border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
}
.btn-edit { background: var(--primary); color: white; }
.btn-edit:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
.btn-danger { background: var(--danger); color: white; }
.btn-danger:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Modal */
.modal-overlay { 
    display: none; position: fixed; z-index: 1000; left: 0; top: 0; 
    width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); 
    backdrop-filter: blur(4px);
}
.modal-content { 
    background: var(--card-bg); margin: 5% auto; border-radius: var(--radius-lg);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 90%; max-width: 500px;
    border: 1px solid var(--border);
}
.modal-header { 
    padding: 24px; border-bottom: 1px solid var(--border); 
    display: flex; justify-content: space-between; align-items: center;
}
.modal-header h3 { margin: 0; font-size: 18px; font-weight: 700; }
.modal-close { 
    background: none; border: none; font-size: 24px; cursor: pointer; 
    color: var(--text-muted); padding: 0; width: 32px; height: 32px; 
    display: grid; place-items: center; border-radius: 8px;
}
.modal-close:hover { background: var(--bg-main); color: var(--text-dark); }

.edit-form { padding: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { 
    display: block; margin-bottom: 6px; font-weight: 600; 
    color: var(--text-dark); font-size: 14px;
}
.form-group input, .form-group select { 
    width: 100%; padding: 12px; border: 1px solid var(--border); 
    border-radius: 8px; font-size: 14px; transition: border-color 0.2s;
}
.form-group input:focus, .form-group select:focus { 
    outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.form-actions { 
    display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px;
}
.btn-cancel, .btn-primary {
    padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;
    transition: all 0.2s; border: none; font-size: 14px;
}
.btn-cancel { 
    background: var(--bg-main); color: var(--text-muted); 
}
.btn-cancel:hover { background: var(--border); color: var(--text-dark); }
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Empty State */
.empty-state { 
    text-align: center; padding: 64px 24px; color: var(--text-muted);
}
.empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h4 { font-size: 18px; margin: 0 0 8px 0; color: var(--text-dark); }
.empty-state p { margin: 0; font-size: 14px; }

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .user-management { padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .table-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .modern-table thead th, .modern-table tbody td { padding: 12px 16px; }
    .user-cell { flex-direction: column; align-items: flex-start; gap: 4px; }
    .action-buttons { flex-direction: column; }
    .form-actions { flex-direction: column; }
    .modal-content { margin: 10% auto; width: 95%; }
}
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
