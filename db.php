<?php
$host = "localhost";
$dbname = "payton";   // 👉 change if your DB name is different
$username = "root";  // default XAMPP username
$password = "";      // default XAMPP password

try {
  $conn = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password
  );

  // Enable PDO error reporting
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
