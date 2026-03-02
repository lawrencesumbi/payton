<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=payton", "root", "");

if (isset($_POST['update_budget'])) {

    $budget_id    = $_POST['budget_id'];
    $budget_name  = $_POST['budget_name'];
    $budget_amount = $_POST['budget_amount'];
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];
    $user_id      = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        UPDATE budget
        SET budget_name = ?,
            budget_amount = ?,
            start_date = ?,
            end_date = ?
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->execute([
        $budget_name,
        $budget_amount,
        $start_date,
        $end_date,
        $budget_id,
        $user_id
    ]);

    header("Location: http://localhost/payton/spender.php?page=manage_budget");
    exit;
}
?>