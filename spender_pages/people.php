<?php
require_once "db.php";

// Standard session check
if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$message = "";

/* =========================================
   ADD NEW PERSON
   ========================================= */
if(isset($_POST['add_person'])){
    $name = trim($_POST['person_name']);

    if(!empty($name)){
        $stmt = $conn->prepare("INSERT INTO people (user_id, name) VALUES (?, ?)");
        if($stmt->execute([$user_id, $name])){
            $message = "Person added successfully.";
        } else {
            $message = "Error adding person.";
        }
    } else {
        $message = "Please enter a name.";
    }
}

/* =========================================
   DELETE PERSON
   ========================================= */
if(isset($_POST['delete_person'])){
    $person_id = $_POST['person_id'];

    // Ensure we only delete a person belonging to the logged-in user
    $stmt = $conn->prepare("DELETE FROM people WHERE id = ? AND user_id = ?");
    $stmt->execute([$person_id, $user_id]);

    $message = "Person removed.";
}

/* =========================================
   FETCH PEOPLE LIST
   ========================================= */
$stmt = $conn->prepare("SELECT * FROM people WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage People</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body{background:#f9fafb; margin:0; }
        .container{ width: 100%; margin:10px auto; padding:20px; }
        .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .btn{ padding:10px 18px; border:none; border-radius:8px; cursor:pointer; font-weight:500; transition: 0.2s; }
        .btn-primary{ background:#6f42c1; color:white; }
        .btn-danger{ background:#fef2f2; color:#dc2626; border: 1px solid #fee2e2; }
        .btn-danger:hover{ background:#fee2e2; }
        .card{ background:white; border-radius:12px; border:1px solid #e5e7eb; overflow: hidden; }
        table{ width:100%; border-collapse:collapse; }
        th{ padding:12px; text-align:left; background:#fafafa; font-size:12px; color:#6b7280; text-transform: uppercase; }
        td{ padding:14px; border-top:1px solid #eee; }
        .alert{ padding:12px; background:#ecfdf5; color:#065f46; border-radius:8px; margin-bottom:20px; border: 1px solid #d1fae5; }
        .modal {
    display: none;
    position: fixed;
    /* Ensure it starts at the very top left of the browser */
    top: 0;
    left: 0;
    /* Force it to be the full width/height of the window */
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    /* Bring it to the very front of everything else */
    z-index: 9999; 
    justify-content: center;
    align-items: center;
}
        .modal-card{ background:white; padding:25px; border-radius:12px; width:320px; }
        .input-box{ width:100%; padding:12px; margin:15px 0; border:1px solid #ddd; border-radius:8px; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Manage People</h2>
        <button class="btn btn-primary" onclick="openModal()">+ Add Person</button>
    </div>

    <?php if(!empty($message)): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date Added</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($people)): ?>
                    <?php foreach($people as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td style="color:#6b7280; font-size: 0.9em;">
                            <?php echo date("M d, Y", strtotime($p['created_at'])); ?>
                        </td>
                        <td style="text-align:right">
                            <form method="POST" onsubmit="return confirm('Remove this person?');">
                                <input type="hidden" name="person_id" value="<?php echo $p['id']; ?>">
                                <button name="delete_person" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding:40px; color:gray">
                            No people found. Click "+ Add Person" to start.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal" class="modal">
    <div class="modal-card">
        <h3 style="margin-top:0">Add New Person</h3>
        <form method="POST">
            <input type="text" name="person_name" placeholder="Full Name" class="input-box" required autofocus>
            <div style="display:flex; gap:10px;">
                <button name="add_person" class="btn btn-primary" style="flex:2">Save Person</button>
                <button type="button" onclick="closeModal()" class="btn" style="flex:1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(){ document.getElementById("modal").style.display="flex"; }
    function closeModal(){ document.getElementById("modal").style.display="none"; }
    // Close modal if clicking outside the card
    window.onclick = function(event) {
        if (event.target == document.getElementById("modal")) closeModal();
    }
</script>

</body>
</html>