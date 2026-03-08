<?php
// Ensure db.php or your connection logic is included if this is a standalone include
// if (!isset($pdo)) { $pdo = new PDO("mysql:host=localhost;dbname=payton", "root", ""); }

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$sponsor_id = $_SESSION['user_id'];
$message = "";
$display_group = null;

// 1. HANDLE NEW GROUP CREATION
if(isset($_POST['create_group'])){
    $new_group_name = $_POST['group_name'];
    
    // Simple 6-character code generator
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $new_group_code = "";
    for($i = 0; $i < 6; $i++){
        $new_group_code .= $chars[rand(0, strlen($chars) - 1)];
    }

    $stmt = $pdo->prepare("INSERT INTO groups (group_name, group_code, sponsor_id) VALUES (?, ?, ?)");
    $stmt->execute([$new_group_name, $new_group_code, $sponsor_id]);
    
    // Set display data to the new group
    $display_group = [
        'group_name' => $new_group_name,
        'group_code' => $new_group_code
    ];
    $message = "success";
}

// 2. HANDLE SIDEBAR SELECTION
// If a group_id is passed in the URL, fetch its details
if(isset($_GET['group_id']) && $message != "success"){
    $stmt = $conn->prepare("SELECT group_name, group_code FROM groups WHERE id = ? AND sponsor_id = ?");
    $stmt->execute([$_GET['group_id'], $sponsor_id]);
    $display_group = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payton | Manage Groups</title>
    <style>
        :root {
            --primary-purple: #7c3aed; /* Violet 600 */
            --hover-purple: #6d28d9;
            --bg-light: #f5f3ff;
            --text-dark: #1f2937;
            --border-color: #e5e7eb;
        }

        /* DASHBOARD LAYOUT */
        .dashboard-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .header-banner {
            background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
            height: 200px;
            border-radius: 12px;
            display: flex;
            align-items: flex-end;
            padding: 30px;
            color: white;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-banner h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .main-content {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 25px;
            margin-top: 25px;
        }

        .info-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            height: fit-content;
        }

        .code-display {
            font-size: 1.5rem;
            color: var(--primary-purple);
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        /* MODAL STYLES */
        #modalOverlay {
            display: <?php echo ($message == "success") ? "none" : "none"; ?>; /* Controlled by JS */
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-card {
            background: white;
            width: 100%;
            max-width: 500px;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-card h2 { margin-top: 0; font-weight: 500; }

        .input-box {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-bottom: 2px solid #ddd;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
            margin: 20px 0;
        }

        .input-box:focus {
            border-color: var(--primary-purple);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        .btn-text { background: none; color: #666; }
        .btn-text:hover { background: #f3f4f6; }

        .btn-primary { background: var(--primary-purple); color: white; }
        .btn-primary:hover { background: var(--hover-purple); }
        
        /* Floating Action Button for the initial state */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-purple);
            color: white;
            width: 60px; height: 60px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(124, 58, 237, 0.3);
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php if($display_group): ?>
        <div class="header-banner">
            <h1><?php echo htmlspecialchars($display_group['group_name']); ?></h1>
        </div>

        <div class="main-content">
            <aside>
                <div class="info-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: #666; font-weight: 600;">Group code</span>
                        <i class="fa-solid fa-ellipsis-vertical" style="color: #666; font-size: 0.8rem;"></i>
                    </div>
                    <div class="code-display"><?php echo htmlspecialchars($display_group['group_code']); ?></div>
                </div>

            </aside>

            <main>
                <div class="info-card" style="display:flex; align-items:center; color: #666; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <div style="width:40px; height:40px; background:#f3f4f6; border-radius:50%; margin-right:15px; border: 1px solid #e5e7eb;"></div>
                    <span style="font-size: 0.9rem;">Announce something to your group...</span>
                </div>
            </main>
        </div>
    <?php else: ?>
        <div style="text-align:center; margin-top:100px; color:#666;">
            <i class="fa-solid fa-users-viewfinder" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 20px;"></i>
            <h2>No group selected</h2>
            <p>Select a group from the sidebar or click the + button to create a new one.</p>
        </div>
    <?php endif; ?>
</div>

<div class="fab" onclick="openModal()">+</div>

<div id="modalOverlay">
    <div class="modal-card">
        <h2>Create group</h2>
        <form method="POST">
            <input type="text" name="group_name" class="input-box" placeholder="Group name (required)" required autofocus>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-text" onclick="closeModal()">Cancel</button>
                <button type="submit" name="create_group" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("modalOverlay");

    function openModal() {
        modal.style.display = "flex";
    }

    function closeModal() {
        modal.style.display = "none";
    }

    // Close modal if user clicks outside of the card
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

</body>
</html>