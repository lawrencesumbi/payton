





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payton - Dashboard</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <link rel="stylesheet" href="dashboard.css" />
</head>

<style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: Arial, sans-serif;
}

body {
  background: #f5f7fb;
  min-height: 100vh;
}

.app {
  display: flex;
  min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
  width: 260px;
  background: #ffffff;
  padding: 25px 18px;
  border-right: 1px solid #eee;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 30px;
}

.logo {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #7f308f;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.brand h2 {
  font-size: 20px;
  font-weight: 800;
  color: #222;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.menu a {
  text-decoration: none;
  padding: 12px 14px;
  border-radius: 14px;
  color: #444;
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: 0.25s;
}

.menu a:hover {
  background: #f3eaff;
  color: #7f308f;
}

.menu a.active {
  background: #7f308f;
  color: white;
}

.menu a.logout {
  margin-top: 15px;
  background: #f6f6f6;
}

/* MAIN */
.main {
  flex: 1;
  padding: 30px;
  position: relative;
}

/* TOPBAR */
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.topbar h1 {
  font-size: 26px;
  color: #222;
}

.topbar p {
  margin-top: 4px;
  color: #777;
  font-size: 14px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-btn {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  border: none;
  background: white;
  cursor: pointer;
  font-size: 15px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  transition: 0.25s;
}

.icon-btn:hover {
  transform: translateY(-2px);
}

.profile {
  display: flex;
  align-items: center;
  gap: 10px;
  background: white;
  padding: 8px 12px;
  border-radius: 18px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

.profile img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
}

.profile h4 {
  font-size: 13px;
  font-weight: 800;
  color: #222;
}

.profile span {
  font-size: 12px;
  color: #777;
}

/* CARDS */
.cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 20px;
}

.card {
  background: white;
  padding: 18px;
  border-radius: 18px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.card-top h3 {
  font-size: 14px;
  color: #555;
  font-weight: 700;
}

.card h2 {
  font-size: 22px;
  color: #222;
  margin-bottom: 8px;
}

.muted {
  font-size: 13px;
  color: #777;
}

.warn {
  color: #d9534f;
  font-weight: 700;
}

/* GRID */
.grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 18px;
}

.panel {
  background: white;
  border-radius: 18px;
  padding: 18px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.panel-head h3 {
  font-size: 16px;
  font-weight: 800;
  color: #222;
}

.panel-head select {
  border: 1px solid #ddd;
  padding: 8px 12px;
  border-radius: 12px;
  outline: none;
}

.chart-box {
  height: 220px;
  border-radius: 18px;
  background: #f6f2ff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7f308f;
  font-weight: 700;
}

/* BUDGET */
.budget-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

.budget-row:last-child {
  border-bottom: none;
}

.green {
  color: #1c9b6c;
  font-weight: 800;
}

.red {
  color: #d9534f;
  font-weight: 800;
}

/* TRANSACTIONS */
.transactions {
  grid-column: 1 / 2;
}

.trx {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}

.trx:last-child {
  border-bottom: none;
}

.trx-left {
  display: flex;
  gap: 12px;
  align-items: center;
}

.trx-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #f3eaff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7f308f;
  font-size: 16px;
}

.trx-left h4 {
  font-size: 14px;
  font-weight: 800;
  color: #222;
}

.trx-left p {
  font-size: 12px;
  color: #777;
}

.trx-amount {
  font-size: 14px;
  font-weight: 900;
}

/* GOALS */
.goals {
  grid-column: 2 / 3;
}

.goal {
  margin-bottom: 16px;
}

.goal-top {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.goal-top h4 {
  font-size: 14px;
  font-weight: 800;
  color: #222;
}

.goal-top span {
  font-size: 13px;
  color: #555;
  font-weight: 700;
}

.progress {
  width: 100%;
  height: 10px;
  background: #eee;
  border-radius: 50px;
  overflow: hidden;
}

.bar {
  height: 100%;
  background: #7f308f;
  border-radius: 50px;
}

/* BUTTONS */
.mini-btn {
  border: none;
  background: #f3eaff;
  color: #7f308f;
  padding: 8px 12px;
  border-radius: 12px;
  font-weight: 800;
  cursor: pointer;
  transition: 0.25s;
}

.mini-btn:hover {
  background: #7f308f;
  color: white;
}

.fab {
  position: fixed;
  bottom: 25px;
  right: 25px;
  width: 60px;
  height: 60px;
  border-radius: 18px;
  border: none;
  background: #1c9b6c;
  color: white;
  font-size: 20px;
  cursor: pointer;
  box-shadow: 0 12px 30px rgba(0,0,0,0.2);
  transition: 0.25s;
}

.fab:hover {
  transform: translateY(-3px);
}

/* RESPONSIVE */
@media (max-width: 1100px) {
  .cards {
    grid-template-columns: repeat(2, 1fr);
  }
  .grid {
    grid-template-columns: 1fr;
  }
  .goals, .transactions {
    grid-column: auto;
  }
}

@media (max-width: 800px) {
  .sidebar {
    display: none;
  }
  .main {
    padding: 18px;
  }
}

</style>





<body>

  <div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="brand">
        <div class="logo">
          <i class="fa-solid fa-wallet"></i>
        </div>
        <h2>Payton</h2>
      </div>

      <nav class="menu">
        <a class="active" href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="#"><i class="fa-solid fa-receipt"></i> Transactions</a>
        <a href="#"><i class="fa-solid fa-piggy-bank"></i> Budget</a>
        <a href="#"><i class="fa-solid fa-chart-line"></i> Reports</a>
        <a href="#"><i class="fa-solid fa-bullseye"></i> Goals</a>
        <a href="#"><i class="fa-solid fa-calendar-days"></i> Scheduler</a>
        <a href="#"><i class="fa-solid fa-bell"></i> Notifications</a>
        <a href="#"><i class="fa-solid fa-gear"></i> Settings</a>
        <a href="#" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">

      <!-- TOPBAR -->
      <header class="topbar">
        <div>
          <h1>Welcome back, Student!</h1>
          <p>Manage your spending and stay within your budget.</p>
        </div>

        <div class="topbar-right">
          <button class="icon-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
          <button class="icon-btn"><i class="fa-solid fa-bell"></i></button>

          <div class="profile">
            <img src="https://i.pravatar.cc/40?img=3" alt="profile" />
            <div>
              <h4>Payton User</h4>
              <span>student@email.com</span>
            </div>
          </div>
        </div>
      </header>

      <!-- SUMMARY CARDS -->
      <section class="cards">
        <div class="card">
          <div class="card-top">
            <h3>Total Balance</h3>
            <i class="fa-solid fa-arrow-trend-up"></i>
          </div>
          <h2>₱ 2,450.00</h2>
          <p class="muted">+12% vs last month</p>
        </div>

        <div class="card">
          <div class="card-top">
            <h3>Income</h3>
            <i class="fa-solid fa-coins"></i>
          </div>
          <h2>₱ 1,500.00</h2>
          <p class="muted">Allowance received</p>
        </div>

        <div class="card">
          <div class="card-top">
            <h3>Expense</h3>
            <i class="fa-solid fa-cart-shopping"></i>
          </div>
          <h2>₱ 980.00</h2>
          <p class="muted">This month</p>
        </div>

        <div class="card">
          <div class="card-top">
            <h3>Budget Remaining</h3>
            <i class="fa-solid fa-circle-exclamation"></i>
          </div>
          <h2>₱ 520.00</h2>
          <p class="muted warn">⚠ Overspending risk</p>
        </div>
      </section>

      <!-- GRID CONTENT -->
      <section class="grid">

        <!-- CHART PLACEHOLDER -->
        <div class="panel chart">
          <div class="panel-head">
            <h3>Money Flow</h3>
            <select>
              <option>This month</option>
              <option>This year</option>
            </select>
          </div>

          <div class="chart-box">
            <p>(Chart placeholder — you can use Chart.js here)</p>
          </div>
        </div>

        <!-- BUDGET PANEL -->
        <div class="panel budget">
          <div class="panel-head">
            <h3>Budget</h3>
            <button class="mini-btn"><i class="fa-solid fa-plus"></i></button>
          </div>

          <div class="budget-row">
            <span>Food & Snacks</span>
            <span class="green">₱ 300 left</span>
          </div>

          <div class="budget-row">
            <span>Transportation</span>
            <span class="green">₱ 150 left</span>
          </div>

          <div class="budget-row">
            <span>School Supplies</span>
            <span class="red">₱ -50 over</span>
          </div>

          <div class="budget-row">
            <span>Load / Internet</span>
            <span class="green">₱ 120 left</span>
          </div>
        </div>

        <!-- RECENT TRANSACTIONS -->
        <div class="panel transactions">
          <div class="panel-head">
            <h3>Recent Transactions</h3>
            <button class="mini-btn">See all</button>
          </div>

          <div class="trx">
            <div class="trx-left">
              <div class="trx-icon"><i class="fa-solid fa-burger"></i></div>
              <div>
                <h4>McDonalds</h4>
                <p>Food</p>
              </div>
            </div>
            <span class="trx-amount red">- ₱120</span>
          </div>

          <div class="trx">
            <div class="trx-left">
              <div class="trx-icon"><i class="fa-solid fa-bus"></i></div>
              <div>
                <h4>Jeep Fare</h4>
                <p>Transportation</p>
              </div>
            </div>
            <span class="trx-amount red">- ₱20</span>
          </div>

          <div class="trx">
            <div class="trx-left">
              <div class="trx-icon"><i class="fa-solid fa-wifi"></i></div>
              <div>
                <h4>Mobile Load</h4>
                <p>Internet</p>
              </div>
            </div>
            <span class="trx-amount red">- ₱50</span>
          </div>
        </div>

        <!-- GOALS PANEL -->
        <div class="panel goals">
          <div class="panel-head">
            <h3>Saving Goals</h3>
            <button class="mini-btn"><i class="fa-solid fa-plus"></i></button>
          </div>

          <div class="goal">
            <div class="goal-top">
              <h4>New Shoes</h4>
              <span>₱ 800 / ₱ 1500</span>
            </div>
            <div class="progress">
              <div class="bar" style="width: 53%;"></div>
            </div>
          </div>

          <div class="goal">
            <div class="goal-top">
              <h4>School Bag</h4>
              <span>₱ 300 / ₱ 900</span>
            </div>
            <div class="progress">
              <div class="bar" style="width: 33%;"></div>
            </div>
          </div>
        </div>

      </section>

      <!-- FLOATING ADD BUTTON -->
      <button class="fab">
        <i class="fa-solid fa-plus"></i>
      </button>

    </main>

  </div>

</body>
</html>
