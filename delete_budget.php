<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['budget_id'])) {

        $budget_id = $_POST['budget_id'];
        $user_id = $_SESSION['user_id'];

        // Security: only allow deleting if the user is owner or sponsor
        $stmt = $conn->prepare("
            DELETE FROM budget
            WHERE id = ? 
            AND (user_id = ? OR sponsor_id = ?)
        ");

        $stmt->execute([$budget_id, $user_id, $user_id]);
    }
}

    header("Location: http://localhost/payton/sponsor.php?page=manage_allowance");
    exit;

?>