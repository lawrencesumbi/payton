<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }

    body { background: #f5f7fb; }

    /* Navbar */
    .navbar {
      background: #fff;
      padding: 15px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .navbar h2 { color: #7f308f; }

    .logout {
      text-decoration: none;
      background: #7f308f;
      color: #fff;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      transition: 0.3s;
    }

    .logout:hover { background: #9357f5; }

    /* Main */
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .welcome {
      margin-bottom: 30px;
    }

    .welcome h1 {
      font-size: 28px;
      color: #333;
    }

    .welcome span {
      color: #7f308f;
    }

    /* Cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
    }

    .card {
      background: #fff;
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      transition: transform 0.3s;
    }

    .card:hover { transform: translateY(-6px); }

    .card h3 {
      font-size: 16px;
      color: #7f308f;
      margin-bottom: 10px;
    }

    .card p {
      font-size: 28px;
      font-weight: 800;
      color: #333;
    }

    /* Activity */
    .activity {
      margin-top: 40px;
      background: #fff;
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    .activity h3 {
      margin-bottom: 15px;
      color: #7f308f;
    }

    .activity ul {
      list-style: none;
    }

    .activity li {
      padding: 10px 0;
      border-bottom: 1px solid #eee;
      color: #555;
    }

    .activity li:last-child { border-bottom: none; }

    @media (max-width: 768px) {
      .navbar { flex-direction: column; gap: 10px; }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <h2>Dashboard</h2>
    <a href="logout.php" class="logout">Logout</a>
  </div>

  <!-- Content -->
  <div class="container">

    <div class="welcome">
      <h1>Welcome back, <span><?php echo htmlspecialchars($_SESSION["fullname"]); ?></span> 👋</h1>
    </div>

    <!-- Cards -->
    <div class="cards">
      <div class="card">
        <h3>Account Balance</h3>
        <p>₱ 25,000</p>
      </div>

      <div class="card">
        <h3>Total Transactions</h3>
        <p>120</p>
      </div>

      <div class="card">
        <h3>Monthly Savings</h3>
        <p>₱ 5,500</p>
      </div>
    </div>

    <!-- Activity -->
    <div class="activity">
      <h3>Recent Activity</h3>
      <ul>
        <li>✔ Payment received – ₱2,000</li>
        <li>✔ Bill paid – ₱1,200</li>
        <li>✔ Savings added – ₱500</li>
      </ul>
    </div>

  </div>

</body>
</html>
