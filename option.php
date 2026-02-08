<?php
session_start();
require_once "db.php"; // PDO connection

// If not logged in, go to login
if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

// Handle role selection
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["role"])) {

  $user_id = $_SESSION["user_id"];
  $role = $_POST["role"];

  // Allow only valid roles
  $allowed_roles = ["spender", "sponsor"];
  if (!in_array($role, $allowed_roles)) {
    die("Invalid role selected.");
  }

  // Save role to database
  $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
  $stmt->execute([
    ":role" => $role,
    ":id"   => $user_id
  ]);

  // Redirect based on role
  if ($role === "spender") {
    header("Location: spender.php");
  } else {
    header("Location: sponsor.php");
  }
  exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Choose Role | Payton</title>

  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
  />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #6f47fd, #f7f7f7);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .role-container {
      width: 100%;
      max-width: 950px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      padding: 45px;
    }

    .role-header {
      text-align: center;
      margin-bottom: 35px;
    }

    .role-header h2 {
      font-size: 30px;
      font-weight: 800;
      color: #7f308f;
      margin-bottom: 10px;
    }

    .role-header p {
      font-size: 15px;
      color: #666;
      line-height: 1.5;
    }

    .role-cards {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 25px;
      margin-top: 25px;
    }

    .role-card {
      border: 2px solid #eee;
      border-radius: 18px;
      padding: 28px;
      text-align: center;
      transition: 0.3s;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    .role-card:hover {
      border-color: #9357f5;
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .role-icon {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: rgba(147, 87, 245, 0.12);
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 18px auto;
    }

    .role-icon i {
      font-size: 28px;
      color: #7f308f;
    }

    .role-card h3 {
      font-size: 22px;
      font-weight: 800;
      color: #222;
      margin-bottom: 10px;
    }

    .role-card p {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .role-btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 20px;
      background: #7f308f;
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s;
    }

    .role-btn:hover {
      background: #9357f5;
    }

    .note {
      margin-top: 28px;
      text-align: center;
      font-size: 13px;
      color: #777;
    }

    .note span {
      font-weight: 700;
      color: #7f308f;
    }

    /* RESPONSIVE */
    @media (max-width: 800px) {
      .role-container {
        padding: 35px;
      }

      .role-cards {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
 
 <div class="role-container">

    <div class="role-header">
      <h2>How will you use Payton?</h2>
      <p>
        Choose your account type to continue.  
        You can link a Sponsor account later if needed.
      </p>
    </div>

    <div class="role-cards">

      <!-- Spender -->
      <form action="option.php" method="POST" class="role-card">
        <div class="role-icon">
          <i class="fa-solid fa-wallet"></i>
        </div>

        <h3>Spender</h3>
        <p>
          Track your expenses, manage your budget, and view your spending reports.
        </p>

        <input type="hidden" name="role" value="spender">
        <button type="submit" class="role-btn">Continue as Spender</button>
      </form>

      <!-- Sponsor -->
      <form action="option.php" method="POST" class="role-card">
        <div class="role-icon">
          <i class="fa-solid fa-user-shield"></i>
        </div>

        <h3>Sponsor</h3>
        <p>
          Link to a Spender account, set budgets, and receive alerts for overspending.
        </p>

        <input type="hidden" name="role" value="sponsor">
        <button type="submit" class="role-btn">Continue as Sponsor</button>
      </form>

    </div>

    <div class="note">
      Tip: A <span>Sponsor</span> account must be linked to a <span>Spender</span> using a code.
    </div>

  </div>

</body>
</html>