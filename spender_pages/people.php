<?php
require_once "db.php";

// LOGIN CHECK
if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];

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
        body{background:#f9fafb; margin:0; font-family:'Inter', sans-serif;}

        .container{ width: 100%; padding:20px; }
        .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }

        .btn{ padding:10px 18px; border:none; border-radius:8px; cursor:pointer; font-weight:500; }
        .btn-primary{ background:#6f42c1; color:white; }
        .btn-danger{ background:#fef2f2; color:#dc2626; border: 1px solid #fee2e2; }

        .card{ background: white; border-radius:12px; border:1px solid #e5e7eb; overflow: hidden; }

        table{ width:100%; border-collapse:collapse; }
        th{ padding:12px; text-align:left; background:#8c3bf6; font-size:14px; color:white; }
        td{ padding:14px; border-top:1px solid #eeeeee; }

        /* MODAL */
        .modal{
            display:none;
            position:fixed;
            top:0; left:0;
            width:100%; height:100%;
            background:rgba(0,0,0,0.6);
            z-index:9999;
            justify-content:center;
            align-items:center;
        }

        .modal-card{
            background:white;
            padding:25px;
            border-radius:12px;
            width:320px;
        }

        .input-box{
            width:100%;
            padding:12px;
            margin:15px 0;
            border:1px solid #ddd;
            border-radius:8px;
        }

        /* TOAST */
        .toast-container{
            position:fixed;
            top:20px;
            right:20px;
            z-index:9999;
        }

        .custom-toast{
            display:flex;
            align-items:flex-start;
            gap:10px;
            background:white;
            padding:15px;
            border-radius:10px;
            margin-bottom:10px;
            min-width:250px;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn{
            from{ transform: translateX(100%); opacity:0; }
            to{ transform: translateX(0); opacity:1; }
        }

        .toast-success{ border-left:5px solid #22c55e; }
        .toast-error{ border-left:5px solid #ef4444; }

        .toast-title{ font-weight:700; }
        .toast-message{ font-size:14px; color:#555; }

        .toast-close{
            margin-left:auto;
            cursor:pointer;
            border:none;
            background:none;
            font-size:16px;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="header">
        <h2>Manage People</h2>
        <button class="btn btn-primary" onclick="openModal()">+ Add Person</button>
    </div>

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
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= date("M d, Y", strtotime($p['created_at'])) ?></td>
                        <td style="text-align:right">
                            <form method="POST" action="add_delete_people.php" onsubmit="return confirm('Remove this person?');">
                                <input type="hidden" name="person_id" value="<?= $p['id'] ?>">
                                <button name="delete_person" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding:40px; color:gray">
                            No people found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-card">
        <h3>Add New Person</h3>
        <form method="POST" action="add_delete_people.php">
            <input type="text" name="person_name" placeholder="Full Name" class="input-box" required>
            <button name="add_person" class="btn btn-primary">Save</button>
            <button type="button" onclick="closeModal()" class="btn">Cancel</button>
        </form>
    </div>
</div>

<!-- TOAST -->
<div class="toast-container">
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="custom-toast toast-success">
            <div>
                <div class="toast-title">SUCCESS!</div>
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

<script>
function openModal(){
    document.getElementById("modal").style.display = "flex";
}

function closeModal(){
    document.getElementById("modal").style.display = "none";
}

// Auto-hide toast
setTimeout(() => {
    document.querySelectorAll('.custom-toast').forEach(t => {
        t.style.opacity = '0';
        setTimeout(() => t.remove(), 300);
    });
}, 3000);
</script>

</body>
</html>