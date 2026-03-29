<?php
require_once "db.php"; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$sponsor_id = $_SESSION['user_id'];

// Get search term from URL
$searchTerm = $_GET['search'] ?? '';

// Fetch linked members for display
$query = "
    SELECT u.id, u.fullname, u.email
    FROM users u
    JOIN sponsor_spender ss ON u.id = ss.spender_id
    WHERE ss.sponsor_id = ?
";

if (!empty($searchTerm)) {
    $query .= " AND (u.fullname LIKE ? OR u.email LIKE ?)";
}

$query .= " ORDER BY u.fullname";

$stmt = $conn->prepare($query);
$params = [$sponsor_id];

if (!empty($searchTerm)) {
    $searchWildcard = "%{$searchTerm}%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Members</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* [KEEPING ALL YOUR ORIGINAL CSS VARIABLES AND DARK MODE LOGIC UNTOUCHED] */
        :root {
            --primary: #6f42c1; --primary-hover: #59359a; --bg-body: #f8f9fa;
            --card-bg: #ffffff; --text-main: #111827; --text-muted: #6b7280;
            --border: #e5e7eb; --white: #ffffff; --table-hover: #fcfcfd;
            --header-bg: #fafafa; --success: #22c55e; --danger: #ef4444;
        }
        [data-theme="dark"] {
            --bg-body: #0f111a; --card-bg: #191c24; --text-main: #f8fafc;
            --text-muted: #cbd5e1; --border: #2a2e39; --white: #191c24;
            --table-hover: #1e222d; --header-bg: #242833;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg-body); color: var(--text-main); margin: 0; transition: background 0.3s ease, color 0.3s ease;}
        
        /* Scrollbar Hide */
        html, body { height: 100%; -ms-overflow-style: none; scrollbar-width: none; }
        html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; }

        .container { width: 100%; padding: 0 20px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-top: 20px; }
        
        /* TOAST STYLES */
        .toast-container { position:fixed; top:20px; right:20px; z-index:9999; }
        .custom-toast { 
            display:flex; align-items:flex-start; gap:10px; background:var(--card-bg); padding:15px; 
            border-radius:10px; margin-bottom:10px; min-width:280px; box-shadow:0 10px 25px rgba(0,0,0,0.2); 
            animation: slideIn 0.3s ease; transition: opacity 0.3s ease; border: 1px solid var(--border);
        }
        @keyframes slideIn { from{ transform: translateX(100%); opacity:0; } to{ transform: translateX(0); opacity:1; } }
        .toast-success { border-left:5px solid var(--success); }
        .toast-error { border-left:5px solid var(--danger); }
        .toast-title { font-weight:700; font-size: 14px; color: var(--text-main); }
        .toast-message { font-size:13px; color: var(--text-muted); }
        .toast-close { margin-left:auto; cursor:pointer; border:none; background:none; font-size:16px; color: var(--text-muted); }

        /* TABLE & CARD */
        .card { background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--header-bg); padding: 12px 20px; text-align: left; font-size: 0.75rem; color: var(--text-muted); border-bottom: 1px solid var(--border); text-transform: uppercase; }
        td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); }
        tr:hover { background: var(--table-hover); }

        /* BUTTONS */
        .btn { padding: 10px 18px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 8px; background: var(--card-bg); color: var(--text-main); transition: 0.2s; }
        .btn-primary { background: var(--primary); color: white; border: none; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background: #ef4444; color: white; }

        /* MODALS */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); justify-content: center; align-items: center; z-index: 1000; }
        .modal-card { background: var(--card-bg); padding: 32px; border-radius: 16px; max-width: 400px; width: 90%; border: 1px solid var(--border); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3); }
        .input-box { width: 100%; padding: 12px; margin: 16px 0; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; background: var(--bg-body); color: var(--text-main); }
    </style>
</head>
<body>

<div class="toast-container">
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="custom-toast toast-success">
            <div>
                <div class="toast-title">SUCCESS</div>
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
    <div class="header">
        <h1>Manage Members</h1>
        <button class="btn btn-primary" onclick="openInviteModal()">+ Add Member</button>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Member Details</th>
                    <th>Email Address</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($students)): ?>
                    <?php foreach($students as $s): ?>
                        <tr>
                            <td><div style="font-weight:600;"><?= htmlspecialchars($s['fullname']); ?></div></td>
                            <td><div style="color:var(--text-muted);"><?= htmlspecialchars($s['email']); ?></div></td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $s['id']; ?>, '<?= htmlspecialchars($s['fullname'], ENT_QUOTES); ?>')">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding: 40px; color: var(--text-muted);">
                            No members found. Invite someone to get started.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="inviteModal" class="modal-overlay">
    <div class="modal-card">
        <h2 style="margin:0 0 8px 0;">Invite Member</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">Send an invitation to a spender.</p>
        <form method="POST" action="members_handling.php">
            <label style="font-size: 0.8rem; font-weight: 600;">Email Address</label>
            <input type="email" name="student_email" placeholder="spender@email.com" class="input-box" required>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeModal('inviteModal')" class="btn">Cancel</button>
                <button type="submit" name="send_invite" class="btn btn-primary">Send Invite</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-card">
        <h2 style="margin:0 0 8px 0;">Remove Member?</h2>
        <p id="deleteModalText" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">
            Are you sure you want to remove this member? They will lose access to your split expenses.
        </p>
        <form method="POST" action="members_handling.php">
            <input type="hidden" name="spender_id" id="deleteSpenderId">
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeModal('deleteModal')" class="btn">Cancel</button>
                <button type="submit" name="delete_member" class="btn btn-danger">Confirm Remove</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openInviteModal() { document.getElementById('inviteModal').style.display = 'flex'; }
    
    function confirmDelete(id, name) {
        document.getElementById('deleteSpenderId').value = id;
        document.getElementById('deleteModalText').innerText = "Are you sure you want to remove " + name + " from your members list?";
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
    
    // Auto-hide toast logic
    setTimeout(() => {
        document.querySelectorAll('.custom-toast').forEach(t => {
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 300);
        });
    }, 4000);

    // Close on click outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>

</body>
</html>