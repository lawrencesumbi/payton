<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['user_id'])){
    die("Unauthorized access.");
}

$sponsor_id = $_SESSION['user_id'];

// --- HANDLE SENDING AN INVITATION ---
if(isset($_POST['send_invite'])){
    $student_email = trim($_POST['student_email']);

    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ? AND role = 'spender'");
    $stmt->execute([$student_email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$student){
        $_SESSION['error_msg'] = "No spender with that email exists.";
    } else {
        // Check if already invited (pending)
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND type='invite' AND message LIKE ? AND status = 'unread'");
        $stmt->execute([$student['id'], "%{$sponsor_id}%"]);
        
        if($stmt->rowCount() > 0){
            $_SESSION['error_msg'] = "An invitation is already pending for {$student['fullname']}.";
        } else {
            $stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
            $stmt->execute([$sponsor_id]);
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);

            $invite_message = "You have been invited by {$parent['fullname']}. Click accept to join.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, status, parent_id) VALUES (?, 'invite', ?, 'unread', ?)");
            $stmt->execute([$student['id'], $invite_message, $sponsor_id]);
            
            $_SESSION['success_msg'] = "Invitation sent to {$student['fullname']}!";
        }
    }
    header("Location: sponsor.php?page=manage_members");
    exit();
}

// --- HANDLE UNLINKING A MEMBER ---
if(isset($_POST['delete_member'])){
    $delete_id = $_POST['spender_id'];
    $stmt = $conn->prepare("DELETE FROM sponsor_spender WHERE sponsor_id = ? AND spender_id = ?");
    
    if($stmt->execute([$sponsor_id, $delete_id])){
        $_SESSION['success_msg'] = "Member removed successfully.";
    } else {
        $_SESSION['error_msg'] = "Failed to remove member.";
    }
    header("Location: sponsor.php?page=manage_members");
    exit();
}

// --- HANDLE ACCEPTING AN INVITATION ---
if (isset($_POST['accept_invite'])) {
    $notif_id = $_POST['notif_id'];
    $spender_id = $_SESSION['user_id'];

    // 1. Get the parent_id (sponsor) from the notification
    $stmt = $conn->prepare("SELECT parent_id FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $spender_id]);
    $notif = $stmt->fetch();

    if ($notif && $notif['parent_id']) {
        $sponsor_id = $notif['parent_id'];

        // 2. Check if already linked to avoid duplicates
        $check = $conn->prepare("SELECT * FROM sponsor_spender WHERE sponsor_id = ? AND spender_id = ?");
        $check->execute([$sponsor_id, $spender_id]);

        if ($check->rowCount() == 0) {
            // 3. Insert into the link table
            $ins = $conn->prepare("INSERT INTO sponsor_spender (sponsor_id, spender_id) VALUES (?, ?)");
            $ins->execute([$sponsor_id, $spender_id]);
            
            // 4. Mark notification as read
            $upd = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
            $upd->execute([$notif_id]);

            $_SESSION['success_msg'] = "You have successfully joined the group!";
        } else {
            $_SESSION['error_msg'] = "You are already a member of this group.";
        }
    }
    header("Location: spender.php?page=notifications");
    exit();
}

// --- HANDLE DECLINING ---
if (isset($_POST['decline_invite'])) {
    $notif_id = $_POST['notif_id'];
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
    
    $_SESSION['success_msg'] = "Invitation declined.";
    header("Location: spender.php?page=notifications");
    exit();
}