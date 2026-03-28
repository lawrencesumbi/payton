<?php
require 'db.php';
include 'log_helper.php';


if(isset($_POST['update_budget'])){

    $id = $_POST['budget_id'];
    $name = $_POST['budget_name'];
    $amount = $_POST['budget_amount'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $spender = $_POST['spender_id'];

    $stmt = $conn->prepare("
        UPDATE budget 
        SET budget_name = ?, 
            budget_amount = ?, 
            start_date = ?, 
            end_date = ?, 
            user_id = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $name,
        $amount,
        $start,
        $end,
        $spender,
        $id
    ]);

    $logAction = $user["fullname"] . " Updated a Budget: $name " . ucfirst($user["role"]);
    addLog($conn, $user["id"], $logAction);

    header("Location: http://localhost/payton/sponsor.php?page=manage_allowance");
    exit;
}