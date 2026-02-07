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

    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validation
    if (empty($email) || empty($password)) {
        $_SESSION["error"] = "Email and password are required.";
        header("Location: login.php");
        exit();
    }

    /* ===== FETCH USER ===== */
    $stmt = $pdo->prepare("SELECT id, fullname, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check user & password
    if ($user && password_verify($password, $user["password"])) {

        // Store session data
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["fullname"] = $user["fullname"];
        $_SESSION["email"] = $user["email"];

        // Redirect after login
        header("Location: dashboard.php"); // change if needed
        exit();

    } else {
        $_SESSION["error"] = "Invalid email or password.";
        header("Location: login.php");
        exit();
    }

} else {
    header("Location: login.php");
    exit();
}
?>
