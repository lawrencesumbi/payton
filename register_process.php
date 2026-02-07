<?php
session_start();

/* ===== DATABASE CONNECTION ===== */
$host = "localhost";
$dbname = "payton";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed.");
}

/* ===== CHECK FORM SUBMISSION ===== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get and sanitize inputs
    $fullname = trim($_POST["fullname"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    // Basic validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION["error"] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format.";
        header("Location: register.php");
        exit();
    }

    /* ===== CHECK IF EMAIL EXISTS ===== */
    $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->execute([$email]);

    if ($checkEmail->rowCount() > 0) {
        $_SESSION["error"] = "Email is already registered.";
        header("Location: register.php");
        exit();
    }

    /* ===== HASH PASSWORD ===== */
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    /* ===== INSERT USER ===== */
    $stmt = $pdo->prepare(
        "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)"
    );

    if ($stmt->execute([$fullname, $email, $hashedPassword])) {
        $_SESSION["success"] = "Account created successfully. Please login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION["error"] = "Something went wrong. Try again.";
        header("Location: register.php");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>
