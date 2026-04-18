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
$searchTerm = $_GET['search'] ?? '';

$query = "
    SELECT ss.id, s.fullname as sponsor_name, sp.fullname as spender_name, ss.created_at 
    FROM sponsor_spender ss 
    JOIN users s ON ss.sponsor_id = s.id 
    JOIN users sp ON ss.spender_id = sp.id 
    WHERE 1=1
";

if (!empty($searchTerm)) {
    $query .= " AND (s.fullname LIKE ? OR sp.fullname LIKE ?)";
}

$query .= " ORDER BY ss.created_at DESC";

$stmt = $conn->prepare($query);
$params = [];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard];
}

$stmt->execute($params);
$relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sponsors and spenders for dropdown
$stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'sponsor'");
$stmt->execute();
$sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT id, fullname FROM users WHERE role = 'spender'");
$stmt->execute();
$spenders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="relationship-management">
    <header class="page-header">
        <div class="header-left">
            <span class="badge-accent">Relationship Management</span>
            <h1>Sponsor-Spender Links</h1>
            <p>Manage and oversee all sponsor-spender relationships in the system.</p>
        </div>
        <div class="header-right">
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>
    </header>

    <!-- Add Relationship Form -->
    <div class="glass-card form-container">
        <div class="form-header">
            <h3>Create New Relationship</h3>
            <p>Link a sponsor with a spender to establish allowance management.</p>
        </div>
        
        <form method="post" class="relationship-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="sponsor_id">Sponsor</label>
                    <select name="sponsor_id" id="sponsor_id" required>
                        <option value="">Select Sponsor</option>
                        <?php foreach($sponsors as $sponsor): ?>
                        <option value="<?php echo $sponsor['id']; ?>"><?php echo htmlspecialchars($sponsor['fullname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="spender_id">Spender</label>
                    <select name="spender_id" id="spender_id" required>
                        <option value="">Select Spender</option>
                        <?php foreach($spenders as $spender): ?>
                        <option value="<?php echo $spender['id']; ?>"><?php echo htmlspecialchars($spender['fullname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_relationship" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Relationship
                </button>
            </div>
        </form>
    </div>

    <!-- Relationships Table -->
    <div class="glass-card table-container">
        <div class="table-header">
            <h3>Active Relationships</h3>
            <span class="record-count"><?php echo count($relationships); ?> relationships</span>
        </div>
        
        <div class="table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Sponsor</th>
                        <th>Spender</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($relationships as $rel): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar sponsor-avatar"><?php echo strtoupper(substr($rel['sponsor_name'], 0, 1)); ?></div>
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($rel['sponsor_name']); ?></span>
                                    <span class="user-role">Sponsor</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar spender-avatar"><?php echo strtoupper(substr($rel['spender_name'], 0, 1)); ?></div>
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($rel['spender_name']); ?></span>
                                    <span class="user-role">Spender</span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($rel['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this relationship?')">
                                    <input type="hidden" name="id" value="<?php echo $rel['id']; ?>">
                                    <button type="submit" name="delete_relationship" class="btn-danger">
                                        <i class="fa-solid fa-unlink"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(empty($relationships)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-user-friends"></i>
            <h4>No relationships found</h4>
            <p>Create sponsor-spender relationships to enable allowance management.</p>
        </div>
        <?php endif; ?>
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
}

[data-theme="dark"] {
    --bg-main: #0f111a;
    --card-bg: #191c24;
    --text-dark: #f8fafc;
    --text-muted: #94a3b8;
    --border: #2a2e39;
}

.relationship-management { 
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

/* Form Container */
.glass-card { 
    background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 32px;
}

.form-container { padding: 0; }
.form-header { 
    padding: 24px; border-bottom: 1px solid var(--border);
}
.form-header h3 { font-size: 17px; margin: 0 0 4px 0; font-weight: 700; }
.form-header p { color: var(--text-muted); margin: 0; font-size: 14px; }

.relationship-form { padding: 24px; }
.form-row { 
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;
}
.form-group { }
.form-group label { 
    display: block; margin-bottom: 6px; font-weight: 600; 
    color: var(--text-dark); font-size: 14px;
}
.form-group select { 
    width: 100%; padding: 12px; border: 1px solid var(--border); 
    border-radius: 8px; font-size: 14px; transition: border-color 0.2s;
    background: var(--card-bg); color: var(--text-dark);
}
.form-group select:focus { 
    outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.form-actions { text-align: right; }
.btn-primary {
    background: var(--primary); color: white; border: none; 
    padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;
    transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 14px;
}
.btn-primary:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Table Container */
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

/* User Cell */
.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar { 
    width: 32px; height: 32px; border-radius: 8px;
    display: grid; place-items: center; font-weight: 700; font-size: 12px;
    border: 1px solid var(--border);
}
.sponsor-avatar { background: #ecfdf5; color: var(--success); }
.spender-avatar { background: #e0e7ff; color: var(--primary); }
.user-info { }
.user-name { display: block; font-weight: 600; color: var(--text-dark); }
.user-role { 
    display: block; font-size: 12px; color: var(--text-muted); 
    text-transform: uppercase; letter-spacing: 0.05em; font-weight: 500;
}

/* Action Buttons */
.action-buttons { display: flex; gap: 8px; }
.btn-danger {
    background: var(--danger); color: white; border: none; 
    padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
}
.btn-danger:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

/* Empty State */
.empty-state { 
    text-align: center; padding: 64px 24px; color: var(--text-muted);
}
.empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h4 { font-size: 18px; margin: 0 0 8px 0; color: var(--text-dark); }
.empty-state p { margin: 0; font-size: 14px; }

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .relationship-management { padding: 16px; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .form-row { grid-template-columns: 1fr; gap: 16px; }
    .form-actions { text-align: center; }
    .table-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .modern-table thead th, .modern-table tbody td { padding: 12px 16px; }
    .user-cell { flex-direction: column; align-items: flex-start; gap: 4px; }
    .action-buttons { flex-direction: column; }
}
</style>