<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Spender Budgets</title>

<style>
  .page-title {
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 6px;
    color: #222;
  }

  .page-subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 18px;
    max-width: 700px;
    line-height: 1.5;
  }

  .budget-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }

  .budget-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    transition: 0.2s;
    border: 1px solid #f1f1f1;
  }

  .budget-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 35px rgba(0, 0, 0, 0.08);
  }

  .spender-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
  }

  .spender-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #7f308f;
    color: #fff;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
  }

  .spender-info h3 {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    color: #222;
  }

  .spender-info span {
    font-size: 12px;
    color: #777;
  }

  .budget-meta {
    background: #f7f7fb;
    border-radius: 14px;
    padding: 12px;
    margin-bottom: 12px;
  }

  .budget-meta p {
    margin: 0;
    font-size: 13px;
    color: #444;
  }

  .budget-meta strong {
    font-size: 18px;
    display: block;
    margin-top: 5px;
    color: #111;
  }

  .budget-form label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #333;
  }

  .budget-form input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid #ddd;
    font-size: 14px;
    outline: none;
    transition: 0.2s;
  }

  .budget-form input:focus {
    border-color: #7f308f;
    box-shadow: 0 0 0 3px rgba(127, 48, 143, 0.12);
  }

  .btn-save {
    width: 100%;
    margin-top: 12px;
    padding: 10px 14px;
    background: #7f308f;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    transition: 0.2s;
  }

  .btn-save:hover {
    background: #61216c;
  }

  .empty-wrap {
  width: 100%;
  min-height: 55vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-box {
  width: 100%;
  max-width: 520px;
  background: #ffffff;
  border: 1px solid #f0d6d6;
  border-radius: 18px;
  padding: 22px 20px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  width: 52px;
  height: 52px;
  margin: 0 auto 12px;
  border-radius: 16px;
  background: #ffecec;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
}

.empty-box h3 {
  font-size: 18px;
  font-weight: 900;
  color: #222;
  margin-bottom: 6px;
}

.empty-box p {
  font-size: 14px;
  color: #666;
  line-height: 1.5;
  margin: 0;
}

  /* Responsive */
  @media (max-width: 1100px) {
    .budget-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 700px) {
    .budget-container {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body>


<div class="budget-container">

  <?php if (empty($spenders)): ?>
  <div class="empty-wrap">
    <div class="empty-box">
      <div class="empty-icon">⚠️</div>
      <h3>No Linked Spender Accounts</h3>
      <p>
        You currently have no linked spender accounts. Please link a spender first
        so you can assign budgets and monitor spending activity.
      </p>
    </div>
  </div>
<?php else: ?>


    <?php foreach ($spenders as $sp): ?>
      <?php
        // Avatar initials
        $name = $sp['fullname'] ?? 'Spender';
        $parts = explode(" ", trim($name));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
        $budget = $sp['budget'] ?? 0;
      ?>

      <div class="budget-card">

        <div class="spender-row">
          <div class="spender-avatar"><?= htmlspecialchars($initials) ?></div>

          <div class="spender-info">
            <h3><?= htmlspecialchars($sp['fullname']) ?></h3>
            <span><?= htmlspecialchars($sp['email']) ?></span>
          </div>
        </div>

        <div class="budget-meta">
          <p>Current Monthly Budget</p>
          <strong>₱<?= number_format($budget, 2) ?></strong>
        </div>

        <!-- FORM (backend will follow) -->
        <form class="budget-form" method="post" action="">
          <input type="hidden" name="spender_id" value="<?= (int)$sp['id'] ?>">

          <label>Set New Budget (₱)</label>
          <input type="number" name="new_budget" min="0" placeholder="Enter amount..." required>

          <button class="btn-save" type="submit">
            Save Budget
          </button>
        </form>

      </div>
    <?php endforeach; ?>

  <?php endif; ?>

</div>

</body>
</html>
