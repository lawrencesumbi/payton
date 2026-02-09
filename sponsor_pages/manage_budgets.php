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
  margin: 0 0 16px 0;
}

.btn-link-spender {
  display: inline-block;
  background: #7f308f;
  color: #fff;
  padding: 12px 24px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 700;
  font-size: 14px;
  transition: 0.2s;
}

.btn-link-spender:hover {
  background: #61216c;
  transform: translateY(-2px);
}

.page-wrapper {
  padding: 30px;
}

.header-section {
  margin-bottom: 24px;
}

.link-form-container {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  margin-bottom: 24px;
  max-width: 500px;
  display: none;
  animation: slideDown 0.3s ease-out;
}

.link-form-container.show {
  display: block;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.link-form-container h3 {
  font-size: 16px;
  font-weight: 800;
  color: #222;
  margin-bottom: 8px;
}

.link-form-container p {
  font-size: 13px;
  color: #666;
  margin-bottom: 16px;
  line-height: 1.5;
}

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
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #ddd;
  font-size: 14px;
  outline: none;
  transition: 0.2s;
}

.form-group input:focus {
  border-color: #7f308f;
  box-shadow: 0 0 0 3px rgba(127, 48, 143, 0.12);
}

.btn-submit {
  width: 100%;
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

.btn-submit:hover {
  background: #61216c;
}

.tip-box {
  background: #f9f2fb;
  border: 1px solid #f1d7f6;
  padding: 12px;
  border-radius: 12px;
  font-size: 12px;
  color: #5c1c69;
  line-height: 1.5;
  margin-top: 12px;
}

.btn-toggle-form {
  display: inline-block;
  background: #7f308f;
  color: #fff;
  padding: 10px 20px;
  border-radius: 12px;
  border: none;
  text-decoration: none;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s;
}

.btn-toggle-form:hover {
  background: #61216c;
  transform: translateY(-2px);
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

<div class="page-wrapper">
  
  <!-- LINK SPENDER FORM (Always visible) -->
  <div class="link-form-container">
    <h3>Link a New Spender Account</h3>
    <p>Enter the spender's email to send them a link request. They must accept your request before you can manage their budget.</p>
    
    <form method="post" action="">
      <div class="form-group">
        <label for="spender_email">Spender Email</label>
        <input type="email" id="spender_email" name="spender_email" placeholder="spender@email.com" required>
      </div>
      
      <button type="submit" name="link_spender" class="btn-submit">Send Link Request</button>
    </form>
    
    <div class="tip-box">
      💡 Tip: Make sure the spender has already registered as a <b>Spender</b> account.
    </div>
  </div>

<div class="budget-container">

 <?php if (empty($spenders)): ?>
  <div class="empty-wrap" id="emptyState">
    <div class="empty-box">
      <div class="empty-icon">⚠️</div>
      <h3>No Linked Spender Accounts</h3>
      <p>
        You currently have no linked spender accounts. Use the form above to link a spender first
        so you can assign budgets and monitor spending activity.
      </p>
      <button class="btn-toggle-form" onclick="toggleFormContainer()">Link a Spender Account</button>
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

</div>

<script>
function toggleFormContainer() {
  const formContainer = document.querySelector('.link-form-container');
  const emptyState = document.querySelector('#emptyState');
  
  formContainer.classList.toggle('show');
  
  // Toggle empty state visibility
  if (formContainer.classList.contains('show')) {
    emptyState.style.display = 'none';
    formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } else {
    emptyState.style.display = 'flex';
  }
}
</script>

</body>
</html>
