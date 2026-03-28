<?php
session_start();
include 'db.php';
include 'log_helper.php';
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

    $email    = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        $_SESSION["error"] = "Email and password are required.";
        header("Location: login.php");
        exit();
    }

    /* ===== FETCH USER (Added is_verified to query) ===== */
    $stmt = $pdo->prepare("SELECT id, fullname, email, password, role, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check user & password
    if ($user && password_verify($password, $user["password"])) {

        /* ===== VERIFICATION CHECK ===== */
        if ($user["is_verified"] == 0) {
            // Store the email so the modal knows which account to verify
            $_SESSION['pending_email'] = $user['email']; 
            $_SESSION["error"] = "Please verify your email to continue.";
            
            // Redirect to register.php to trigger the verification modal
            header("Location: login.php"); 
            exit();
        }

        if (isset($_POST['remember_me'])) {
            // Save for 30 days
            setcookie("user_email", $email, time() + (30 * 24 * 60 * 60), "/");
            setcookie("user_password", $password, time() + (30 * 24 * 60 * 60), "/");
        } else {
            // Clear cookies if unchecked
            setcookie("user_email", "", time() - 3600, "/");
            setcookie("user_password", "", time() - 3600, "/");
        }

        // Store session data (Only if verified)
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["fullname"] = $user["fullname"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        // We pass $pdo (the connection) and the user details
        $logAction = $user["fullname"] . " Logged in As " . ucfirst($user["role"]);
        addLog($pdo, $user["id"], $logAction);

        // Redirect based on role
        if (!empty($user["role"])) {
            if ($user["role"] === "spender") {
                header("Location: spender.php");
            } elseif ($user["role"] === "sponsor") {
                header("Location: sponsor.php");
            } else {
                header("Location: option.php");
            }
        } else {
            header("Location: option.php");
        }
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