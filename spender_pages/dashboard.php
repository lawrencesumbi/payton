<?php
/** * DATABASE CONNECTION 
 * Ensure these match your actual config in spender.php 
 */
$host = 'localhost';
$db   = 'payton';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Replace with your session user ID
    $user_id = 1; 

    // --- DATA FETCHING ---
    
    // 1. Budget vs Actuals
    $stmt_budgets = $pdo->prepare("
        SELECT b.budget_name, b.budget_amount, COALESCE(SUM(e.amount), 0) as total_spent
        FROM budget b
        LEFT JOIN expenses e ON b.id = e.budget_id
        WHERE b.user_id = ? AND b.status = 'active'
        GROUP BY b.id LIMIT 4
    ");
    $stmt_budgets->execute([$user_id]);
    $budgets = $stmt_budgets->fetchAll();

    // 2. Category Distribution
    $stmt_cats = $pdo->prepare("
        SELECT c.category_name, SUM(e.amount) as total
        FROM expenses e
        JOIN category c ON e.category_id = c.id
        WHERE e.user_id = ?
        GROUP BY c.id
    ");
    $stmt_cats->execute([$user_id]);
    $category_spending = $stmt_cats->fetchAll();
    $grand_total = array_sum(array_column($category_spending, 'total'));

    // 3. Monthly Trends
    $stmt_trend = $pdo->prepare("
        SELECT DATE_FORMAT(expense_date, '%b') as month_label, SUM(amount) as monthly_sum
        FROM expenses
        WHERE user_id = ? AND expense_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month_label ORDER BY expense_date ASC
    ");
    $stmt_trend->execute([$user_id]);
    $trends = $stmt_trend->fetchAll();

} catch (Exception $e) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded'>Data Error: " . $e->getMessage() . "</div>";
    $budgets = []; $category_spending = []; $trends = []; $grand_total = 0;
}
?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="w-full space-y-6">
    
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <div class="md:col-span-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-700 text-lg">Spending Overview</h3>
                <div class="flex space-x-2">
                    <span class="text-[10px] bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full font-bold uppercase">Recent History</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <div class="md:col-span-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-4">Where Your Money Went</h3>
            <div class="relative flex justify-center items-center h-48">
                <canvas id="donutChart"></canvas>
                <div class="absolute text-center">
                    <p class="text-[10px] uppercase text-gray-400 font-bold">Total</p>
                    <p class="text-xl font-bold text-gray-800">$<?= number_format($grand_total, 2) ?></p>
                </div>
            </div>
            <div class="mt-6 space-y-2 max-h-32 overflow-y-auto">
                <?php foreach($category_spending as $index => $cat): ?>
                <div class="flex justify-between items-center text-xs">
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full mr-2" style="background-color: <?= ['#4F46E5', '#10B981', '#F59E0B', '#EF4444'][$index % 4] ?>"></span>
                        <span class="text-gray-500"><?= htmlspecialchars($cat['category_name']) ?></span>
                    </div>
                    <span class="font-bold text-gray-700">$<?= number_format($cat['total'], 0) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <div class="md:col-span-7 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-1">Financial Trend</h3>
            <p class="text-[11px] text-gray-400 mb-6">Visualizing your monthly cash outflow</p>
            <div class="h-56">
                <canvas id="lineChart"></canvas>
            </div>
        </div>

        <div class="md:col-span-5 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-700 mb-6 border-b pb-2">Budget vs Actuals</h3>
            <div class="space-y-5">
                <?php foreach($budgets as $b): 
                    $pct = ($b['budget_amount'] > 0) ? min(($b['total_spent'] / $b['budget_amount']) * 100, 100) : 0;
                    $isOver = $b['total_spent'] > $b['budget_amount'];
                ?>
                <div class="group">
                    <div class="flex justify-between text-xs mb-1.5 font-semibold">
                        <span class="text-gray-700"><?= htmlspecialchars($b['budget_name']) ?></span>
                        <span class="<?= $isOver ? 'text-red-500' : 'text-indigo-600' ?>">
                            <?= $isOver ? 'Over' : 'On Track' ?>
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="<?= $isOver ? 'bg-red-500' : 'bg-indigo-600' ?> h-full rounded-full transition-all duration-700" style="width: <?= $pct ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-1 text-[10px] text-gray-400">
                        <span>Spent: $<?= number_format($b['total_spent'], 2) ?></span>
                        <span>Limit: $<?= number_format($b['budget_amount'], 2) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($budgets)) echo "<p class='text-gray-400 text-xs italic'>No active budgets set.</p>"; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Logic to handle empty states for charts
const trendLabels = <?= json_encode(array_column($trends, 'month_label')) ?>;
const trendData = <?= json_encode(array_column($trends, 'monthly_sum')) ?>;
const catLabels = <?= json_encode(array_column($category_spending, 'category_name')) ?>;
const catData = <?= json_encode(array_column($category_spending, 'total')) ?>;

// Bar Chart
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: trendLabels,
        datasets: [{
            backgroundColor: '#C7D2FE',
            hoverBackgroundColor: '#4F46E5',
            data: trendData,
            borderRadius: 4
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } }
});

// Donut Chart
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{
            data: catData,
            backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444'],
            borderWidth: 0
        }]
    },
    options: { maintainAspectRatio: false, cutout: '82%', plugins: { legend: { display: false } } }
});

// Line Chart
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.05)',
            data: trendData,
            fill: true,
            tension: 0.4
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } } }
});
</script>