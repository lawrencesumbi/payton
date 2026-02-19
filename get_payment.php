<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM scheduled_payments WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($payment);
