<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['budget_id'])) {
    $budget_id = $_POST['budget_id'];

    // Delete the budget
    $stmt = $conn->prepare("DELETE FROM budget WHERE id = ?");
    $stmt->execute([$budget_id]);

    // Optionally, delete related expenses
    $stmt2 = $conn->prepare("DELETE FROM expenses WHERE budget_id = ?");
    $stmt2->execute([$budget_id]);

    header("Location: http://localhost/payton/spender.php?page=manage_budget");
    exit;
}
?>