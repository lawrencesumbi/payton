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
<style>
body { font-family: Arial, sans-serif; background:#f5f3ff; color:#1f2937; }
.container { max-width:800px; margin:30px auto; padding:20px; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { padding:12px 15px; border:1px solid #e5e7eb; text-align:left; }
th { background:#7c3aed; color:white; }
tr:hover { background:#f3f4f6; }
.btn { padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
.btn-primary { background:#7c3aed; color:white; }
.fab { position:fixed; bottom:30px; right:30px; background:#7c3aed; color:white; width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; cursor:pointer; }
/* Modal */
#modalOverlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; }
.modal-card { background:white; padding:30px; border-radius:16px; max-width:400px; width:100%; }
.modal-card h2 { margin-top:0; }
.input-box { width:100%; padding:10px; margin-top:15px; border:1px solid #ddd; border-radius:6px; }
.modal-actions { margin-top:20px; display:flex; justify-content:flex-end; gap:10px; }
</style>
</head>
<body>

<div class="container">
    

    <?php if($message != ""): ?>
        <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($students) > 0): ?>
                <?php foreach($students as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['fullname'] ?? 'No name'); ?></td>
                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2">No members linked yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Floating Add Button -->
<div class="fab" onclick="openModal()">+</div>

<!-- Modal -->
<div id="modalOverlay">
    <div class="modal-card">
        <h2>Add Members</h2>
        <form method="POST">
            <input type="email" name="student_email" placeholder="Spender's email" class="input-box" required>
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