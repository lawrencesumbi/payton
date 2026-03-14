<?php

require_once "db.php"; 

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$sponsor_id = $_SESSION['user_id'];
$message = "";

// Handle sending an invitation
if(isset($_POST['send_invite'])){
    $student_email = trim($_POST['student_email']);

    // Check if student exists
    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ? AND role = 'spender'");
    $stmt->execute([$student_email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$student){
        $message = "No spender with that email exists.";
    } else {
        // Check if already invited or linked
        $stmt = $conn->prepare("
            SELECT * 
            FROM notifications 
            WHERE user_id = ? AND type='invite' AND message LIKE ?
        ");
        $stmt->execute([$student['id'], "%{$sponsor_id}%"]);
        if($stmt->rowCount() > 0){
            $message = "An invitation has already been sent to {$student['fullname']}.";
        } else {
            $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
            $stmt->execute([$sponsor_id]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);

            $invite_message = "You have been invited by {$parent['fullname']}. Click accept to join.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, status, parent_id) VALUES (?, 'invite', ?, 'unread', ?)");
            $stmt->execute([$student['id'], $invite_message, $sponsor_id]);
            $message = "Invitation sent to {$student['fullname']}.";
        }
    }
}

// Handle deleting/unlinking a member
if(isset($_POST['delete_member'])){
    $delete_id = $_POST['spender_id'];
    $stmt = $conn->prepare("DELETE FROM sponsor_spender WHERE sponsor_id = ? AND spender_id = ?");
    if($stmt->execute([$sponsor_id, $delete_id])){
        $message = "Member removed successfully.";
    }
}

// Fetch students already linked to this parent
$stmt = $conn->prepare("
    SELECT u.id, u.fullname, u.email
    FROM users u
    JOIN sponsor_spender ss ON u.id = ss.spender_id
    WHERE ss.sponsor_id = ?
    ORDER BY u.fullname
");
$stmt->execute([$sponsor_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-body: #f9fafb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --white: #ffffff;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-body); 
            color: var(--text-main); 
            margin: 0;
            
        }

        .container { 
            max-width: 900px; 
            margin: 50px auto; 
            padding: 0 20px; 
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .header h1 { font-size: 1.5rem; font-weight: 600; margin: 0; }

        /* Table Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        
        th { 
            background: #fafafa; 
            padding: 12px 20px; 
            text-align: left; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        td { 
            padding: 16px 20px; 
            border-bottom: 1px solid var(--border); 
            font-size: 0.9rem;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover { background: #fcfcfd; }

        .member-name { font-weight: 500; color: var(--text-main); }
        .member-email { color: var(--text-muted); }

        /* Success Message */
        .alert {
            padding: 12px 16px;
            background: #ecfdf5;
            color: #065f46;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #a7f3d0;
        }

        /* Buttons */
        .btn { 
            padding: 10px 18px; 
            border: 1px solid var(--border);
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary { 
            background: var(--primary); 
            color: white; 
            border: none;
        }
        
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }

        /* Floating Button - Modernized */
        .fab { 
            position: fixed; bottom: 30px; right: 30px; 
            background: var(--primary); color: white; 
            width: 56px; height: 56px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 24px; cursor: pointer; 
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
            transition: transform 0.2s;
        }
        .fab:hover { transform: scale(1.1); }

        /* Modal Redesign */
        #modalOverlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(4px);
            justify-content: center; align-items: center; z-index: 1000;
        }

        .modal-card { 
            background: white; padding: 32px; border-radius: 16px; 
            max-width: 400px; width: 90%; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .input-box { 
            width: 100%; padding: 12px; margin: 16px 0; 
            border: 1px solid var(--border); border-radius: 8px; 
            box-sizing: border-box; font-size: 1rem;
        }
        
        .input-box:focus { outline: 2px solid var(--primary); border-color: transparent; }

        .modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }

        .empty-state { text-align: center; padding: 40px; color: var(--text-muted); }

        .btn-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
        }
        .btn-danger:hover {
            background: #fee2e2;
            color: #b91c1c;
        }
        .actions-column { text-align: right; }
    </style>
</head>
<body>

<div class="container">
        <div class="header d-flex justify-content-between align-items-center">
            <h1>Manage Members</h1>
            <button class="btn" style="background-color:#6f42c1; color:white; border-radius:8px;" onclick="openModal()">
                + Add Member
            </button>
        </div>

    <?php if(!empty($message)): ?>
        <div class="alert">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Member Details</th>
                    <th>Email Address</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($students)): ?>
                    <?php foreach($students as $s): ?>
                        <tr>
                            <td>
                                <div class="member-name"><?php echo htmlspecialchars($s['fullname'] ?? 'Pending Invite'); ?></div>
                            </td>
                            <td>
                                <div class="member-email"><?php echo htmlspecialchars($s['email']); ?></div>
                            </td>
                            <td class="actions-column">
                                <form method="POST" onsubmit="return confirmDelete();" style="display:inline;">
                                    <input type="hidden" name="spender_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" name="delete_member" class="btn btn-danger">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="empty-state">
                            No members found. Click the button to invite someone.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="modalOverlay">
    <div class="modal-card">
        <h2 style="margin:0 0 8px 0;">Invite Member</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">Enter an email address to send a workspace invitation.</p>
        
        <form method="POST">
            <label style="font-size: 0.8rem; font-weight: 600;">Email Address</label>
            <input type="email" name="student_email" placeholder="e.g. name@company.com" class="input-box" required>
            
            <div class="modal-actions">
                <button type="button" onclick="closeModal()" class="btn">Cancel</button>
                <button type="submit" name="send_invite" class="btn btn-primary">Send Invite</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('modalOverlay').style.display = 'flex'; }
    function closeModal() { document.getElementById('modalOverlay').style.display = 'none'; }
    window.onclick = function(event){
        if(event.target == document.getElementById('modalOverlay')) closeModal();
    }
</script>

</body>
</html>