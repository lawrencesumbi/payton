<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Invite Spender</title>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
  }

  

  /* MAIN WRAPPER */
  .wizard-wrap {
    width: 100%;
    max-width: 1050px;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(10px);
    border-radius: 22px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.7);
    overflow: hidden;
  }

  /* TOP BAR */
  .wizard-topbar {
    padding: 0px 26px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.7);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 900;
    color: #5b21b6;
    font-size: 16px;
  }

  .brand .logo {
    width: 28px;
    height: 28px;
    border-radius: 10px;
    background: #ede9fe;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    color: #5b21b6;
  }

  .user-mini {
    font-size: 13px;
    color: #777;
  }

  /* MAIN BODY */
  .wizard-body {
    padding: 26px;
  }

  .wizard-header {
    text-align: center;
    margin-bottom: 18px;
  }

  .wizard-header h1 {
    font-size: 26px;
    font-weight: 900;
    color: #222;
  }

  .wizard-header p {
    font-size: 13px;
    color: #777;
    margin-top: 6px;
  }

  /* 2 COLUMN GRID */
  .wizard-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 22px;
    margin-top: 18px;
  }

  /* LEFT SIDEBAR */
  .wizard-sidebar {
    background: rgba(255, 255, 255, 0.85);
    border-radius: 18px;
    padding: 18px 14px;
    border: 1px solid rgba(0, 0, 0, 0.06);
  }

  .step {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 12px;
    border-radius: 14px;
    cursor: pointer;
    transition: 0.2s;
    margin-bottom: 8px;
  }

  .step:hover {
    background: #f6f2ff;
  }

  .step-icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 13px;
    background: #f1f5f9;
    color: #666;
  }

  .step span {
    font-size: 13px;
    font-weight: 700;
    color: #444;
  }

  /* ACTIVE STEP */
  .step.active {
    background: #f5efff;
    border: 1px solid #e8d8ff;
  }

  .step.active .step-icon {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: white;
  }

  .step.active span {
    color: #4c1d95;
  }

  /* RIGHT CONTENT CARD */
  .wizard-card {
    background: rgba(255, 255, 255, 0.88);
    border-radius: 18px;
    padding: 22px;
    border: 1px solid rgba(0, 0, 0, 0.06);
  }

  /* PROGRESS */
  .progress-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  .progress-bar {
    width: 100%;
    height: 7px;
    background: #eee;
    border-radius: 99px;
    overflow: hidden;
    margin-right: 14px;
  }

  .progress-fill {
    width: 42%;
    height: 100%;
    background: linear-gradient(90deg, #7c3aed, #a855f7);
    border-radius: 99px;
  }

  .progress-percent {
    font-size: 12px;
    font-weight: 700;
    color: #777;
    min-width: 40px;
    text-align: right;
  }

  /* TITLE */
  .wizard-card h2 {
    font-size: 18px;
    font-weight: 900;
    color: #222;
    margin-bottom: 6px;
  }

  .wizard-card p {
    font-size: 13px;
    color: #666;
    margin-bottom: 16px;
    line-height: 1.5;
  }

  /* FORM */
  .form-group {
    margin-bottom: 14px;
  }

  .form-group label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #333;
  }

  .form-group input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #ddd;
    font-size: 14px;
    outline: none;
    transition: 0.2s;
    background: #fff;
  }

  .form-group input:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
  }

  /* BUTTON ROW (like image) */
  .btn-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin-top: 18px;
  }

  .circle-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
    font-size: 18px;
    transition: 0.2s;
  }

  .circle-btn:hover {
    background: #f6f2ff;
    border-color: #c4b5fd;
  }

  .main-btn {
    min-width: 150px;
    padding: 12px 18px;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: white;
    font-weight: 800;
    font-size: 14px;
    transition: 0.2s;
    box-shadow: 0 14px 30px rgba(124, 58, 237, 0.25);
  }

  .main-btn:hover {
    transform: translateY(-2px);
  }

  /* RESPONSIVE */
  @media (max-width: 900px) {
    .wizard-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>

<body>

<div class="wizard-wrap">

  <!-- TOP BAR -->
  <div class="wizard-topbar">
    <div class="brand">
      <div class="logo">✦</div>
      Payton
    </div>
    <div class="user-mini">Hi, Sponsor 👋</div>
  </div>

  <!-- BODY -->
  <div class="wizard-body">

    <div class="wizard-header">
      <h1>Invite a Spender</h1>
      <p>Complete the steps to link and manage spender budgets</p>
    </div>

    <div class="wizard-grid">

      <!-- LEFT STEPS -->
      <div class="wizard-sidebar">
        <div class="step active">
          <div class="step-icon">1</div>
          <span>Send Invite</span>
        </div>

        <div class="step">
          <div class="step-icon">2</div>
          <span>Pending Requests</span>
        </div>

        <div class="step">
          <div class="step-icon">3</div>
          <span>Linked Spenders</span>
        </div>

        <div class="step">
          <div class="step-icon">4</div>
          <span>Assign Budgets</span>
        </div>

        <div class="step">
          <div class="step-icon">5</div>
          <span>Monitor Spending</span>
        </div>
      </div>

      <!-- RIGHT FORM -->
      <div class="wizard-card">

        <div class="progress-row">
          <div class="progress-bar">
            <div class="progress-fill"></div>
          </div>
          <div class="progress-percent">42%</div>
        </div>

        <h2>Send Spender Invitation</h2>
        <p>
          Enter the spender’s email address. They must accept your request before you can assign a monthly budget.
        </p>

        <!-- YOUR PHP FORM GOES HERE -->
        <form method="post" action="">
          <div class="form-group">
            <label for="spender_email">Spender Email</label>
            <input type="email" id="spender_email" name="spender_email"
              placeholder="spender@gmail.com" required>
          </div>

          <div class="btn-row">
            <button type="button" class="circle-btn">‹</button>

            <button type="submit" name="link_spender" class="main-btn">
              Send Invite
            </button>

            <button type="button" class="circle-btn">›</button>
          </div>
        </form>

      </div>

    </div>
  </div>
</div>

</body>
</html>
