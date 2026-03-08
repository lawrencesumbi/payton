<?php
// Note: Ensure session_start() and db connection are handled at the top level of your Payton app
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

if(!isset($_SESSION['user_id'])){
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];
$message = "";
$status = ""; // Added to track success/error for styling

if(isset($_POST['join_group'])){
    $code = strtoupper(trim($_POST['group_code'])); // Normalized code entry

    $stmt = $pdo->prepare("SELECT * FROM groups WHERE group_code = ?");
    $stmt->execute([$code]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if($group){
        $group_id = $group['id']; // Adjusted to match 'id' typically used in your DB

        /* check if already joined */
        $check = $pdo->prepare("SELECT * FROM group_member WHERE group_id = ? AND user_id = ?");
        $check->execute([$group_id, $user_id]);

        if($check->rowCount() > 0){
            $message = "You are already a member of this group.";
            $status = "error";
        }else{
            $stmt = $pdo->prepare("INSERT INTO group_member (group_id, user_id) VALUES (?, ?)");
            $stmt->execute([$group_id, $user_id]);

            $message = "Successfully joined: <b>".htmlspecialchars($group['group_name'])."</b>";
            $status = "success";
        }
    }else{
        $message = "Invalid Group Code. Please check and try again.";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payton | Join Group</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        :root {
            --primary-purple: #7c3aed;
            --hover-purple: #6d28d9;
            --bg-light: #f5f3ff;
            --text-dark: #1f2937;
            --border-color: #e5e7eb;
        }

        .join-card {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(124, 58, 237, 0.1);
            text-align: center;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        h2 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 10px;
        }

        p {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 8px;
            margin-left: 4px;
        }

        .input-field {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
            text-transform: uppercase; /* Matching typical group code style */
            letter-spacing: 1px;
        }

        .input-field:focus {
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .btn-join {
            width: 100%;
            background: var(--primary-purple);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-join:hover {
            background: var(--hover-purple);
        }

        .message-box {
            margin-top: 25px;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .message-box.success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .message-box.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: var(--primary-purple);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="join-card">
    <img src="img/logo.jpg" alt="Payton Logo" class="brand-logo"> <h2>Join a Group</h2>
    <p>Ask your sponsor for the group code, then enter it below to get started.</p>

    <form method="POST">
        <div class="input-group">
            <label for="group_code">Group Code</label>
            <input type="text" 
                   id="group_code" 
                   name="group_code" 
                   class="input-field" 
                   placeholder="e.g. ABC123" 
                   maxlength="6" 
                   required 
                   autofocus>
        </div>

        <button type="submit" name="join_group" class="btn-join">
            <i class="fa-solid fa-right-to-bracket"></i> Join Group
        </button>
    </form>

    <?php if($message): ?>
        <div class="message-box <?php echo $status; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

</body>
</html>