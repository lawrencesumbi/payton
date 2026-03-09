<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

// Ensure parent is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['add_budget'])) {

    $sponsor_id = $_SESSION['user_id']; // Parent ID
    $spender_id = $_POST['spender_id']; // Selected student from the form
    $name       = $_POST['budget_name'];
    $amount     = $_POST['budget_amount'];
    $start      = $_POST['start_date'];
    $end        = $_POST['end_date'];
    $status     = "Active";

   $spender_id = $_POST['spender_id'] ?? null; // use null if not set
if (empty($spender_id)) {
    die("Please select a spender.");
}

    // Insert budget
    $stmt = $pdo->prepare("
        INSERT INTO budget
        (user_id, sponsor_id, budget_name, budget_amount, start_date, end_date, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([$spender_id, $sponsor_id, $name, $amount, $start, $end, $status]);

    header("Location: http://localhost/payton/spender.php?page=manage_budget");
    exit();
}
?>