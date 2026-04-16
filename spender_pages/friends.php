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
// Get search term from URL
$searchTerm = $_GET['search'] ?? '';

// Build WHERE clause with search filter
$whereClause = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($searchTerm)) {
    $whereClause .= " AND name LIKE ?";
    $searchWildcard = "%{$searchTerm}%";
    $params[] = $searchWildcard;
}

$stmt = $conn->prepare("SELECT * FROM people $whereClause ORDER BY id DESC");
$stmt->execute($params);
$people = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Friends</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            --bg-body: #f9fafb;
            --bg-card: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --border-light: #eeeeee;
            --accent-purple: #7c3aed;
            --accent-purple-dark: #8c3bf6;
            --accent-red: #dc2626;
            --accent-red-light: #fef2f2;
            --accent-red-border: #fee2e2;
            --shadow: rgba(0,0,0,0.1);
        }

        [data-theme="dark"] {
            --bg-body: #12141a;
            --bg-card: #191c24;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #2a2e39;
            --border-light: #374151;
            --accent-purple: #a855f7;
            --accent-purple-dark: #a855f7;
            --accent-red: #ef4444;
            --accent-red-light: #451a1a;
            --accent-red-border: #7f1d1d;
            --shadow: rgba(0,0,0,0.2);
        }

        body{background: var(--bg-body); margin:0; font-family:'Inter', sans-serif; color: var(--text-main); transition: background 0.3s ease;}

        .container{ width: 100%; padding:20px; }
        .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }

        .btn{margin-top: 10px; padding:10px 18px; border:none; border-radius:8px; cursor:pointer; font-weight:500; }
        .btn-primary{ background: var(--accent-purple); color:white; }
        .btn-danger{ background: var(--accent-red-light); color: var(--accent-red); border: 1px solid var(--accent-red-border); }

        .btn-edit {
            background: var(--border-light); /* Subtle background */
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-edit:hover {
            background: var(--border-color);
            border-color: var(--text-muted);
        }

        .card {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px var(--shadow);
            display: flex;
            flex-direction: column;
            max-height: 500px; /* Adjust this height as needed */
            overflow: hidden;
        }
        .table-scroll {
            overflow-y: auto;
            flex-grow: 1;
            /* Hide scrollbar for Chrome, Safari and Opera */
            &::-webkit-scrollbar {
                display: none;
            }

            /* Hide scrollbar for IE, Edge and Firefox */
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        table{ width:100%; border-collapse:collapse;}
        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--accent-purple); /* Or your preferred header color */
            color: white;
            padding: 12px;
            text-align: left;
        }
        td{ padding:14px; border-top:1px solid var(--border-light); color: var(--text-main); }

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
            background: var(--bg-card);
            padding:25px;
            border-radius:12px;
            width:320px;
            transition: background 0.3s ease;
        }

        .input-box{
            width:100%;
            padding:12px;
            margin:7px 0;
            border:1px solid var(--border-color);
            border-radius:8px;
            background: var(--bg-card);
            color: var(--text-main);
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
            background: var(--bg-card);
            padding:15px;
            border-radius:10px;
            margin-bottom:10px;
            min-width:250px;
            box-shadow:0 5px 15px var(--shadow);
            animation: slideIn 0.3s ease;
            color: var(--text-main);
            transition: background 0.3s ease;
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
        <h2>Manage Friends</h2>
        <button class="btn btn-primary" onclick="openModal()">+ Add Friend</button>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>No.</th> <th>Name</th>
                        <th>Email</th>
                        <th>Date Added</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($people)): ?>
                        <?php 
                            $num = 1; // Initialize counter
                            foreach($people as $p): 
                        ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.9rem;"><?= $num++ ?>.</td> <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['email']) ?></td>
                            <td><?= date("M d, Y", strtotime($p['created_at'])) ?></td>
                            <td style="text-align:right; display: flex; justify-content: flex-end; gap: 10px;">
                                <button class="btn btn-edit" style="margin-top:0;" onclick='openEditModal(<?= json_encode($p) ?>)'>
                                    Edit
                                </button>

                                <form method="POST" action="add_delete_people.php" onsubmit="return confirm('Remove this person?');">
                                    <input type="hidden" name="person_id" value="<?= $p['id'] ?>">
                                    <button name="delete_person" class="btn btn-danger" style="margin-top:0;">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:gray">
                                No friends found. Click the button to add one.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-card">
        <h3 id="modal-title">Add New Friend</h3>
        <form method="POST" action="add_delete_people.php">
            <input type="hidden" name="person_id" id="edit_person_id">
            
            <input type="text" name="person_name" id="edit_person_name" placeholder="Full Name" class="input-box" required>
            <input type="text" name="person_email" id="edit_person_email" placeholder="Email" class="input-box">
            
            <button type="submit" name="save_person" id="submit-btn" class="btn btn-primary">Save</button>
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
function openModal() {
    // Reset form for "Add" mode
    document.getElementById("modal-title").innerText = "Add New Friend";
    document.getElementById("edit_person_id").value = "";
    document.getElementById("edit_person_name").value = "";
    document.getElementById("edit_person_email").value = "";
    document.getElementById("submit-btn").name = "add_person"; // Action for PHP
    document.getElementById("modal").style.display = "flex";
}

function openEditModal(person) {
    // Fill form for "Edit" mode
    document.getElementById("modal-title").innerText = "Edit Friend";
    document.getElementById("edit_person_id").value = person.id;
    document.getElementById("edit_person_name").value = person.name;
    document.getElementById("edit_person_email").value = person.email;
    document.getElementById("submit-btn").name = "edit_person"; // Action for PHP
    document.getElementById("modal").style.display = "flex";
}

function closeModal() {
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