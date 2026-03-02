<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=payton", "root", "");

if (isset($_POST['add_budget'])) {

    $user_id = $_SESSION['user_id'];
    $name = $_POST['budget_name'];
    $amount = $_POST['budget_amount'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    $status = "Active";

    $stmt = $pdo->prepare("
        INSERT INTO budget
        (user_id, budget_name, budget_amount, start_date, end_date, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([$user_id, $name, $amount, $start, $end, $status]);

    header("Location: http://localhost/payton/spender.php?page=manage_budget");
    exit();
}
?>