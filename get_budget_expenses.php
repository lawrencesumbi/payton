<?php
require 'db.php';
session_start();

if(isset($_GET['budget_id'])){

    $budget_id = $_GET['budget_id'];

    $stmt = $conn->prepare("
        SELECT description, amount, expense_date
        FROM expenses
        WHERE budget_id = ?
        ORDER BY expense_date ASC
    ");
    $stmt->execute([$budget_id]);
    $expenses = $stmt->fetchAll();

    if($expenses){
        echo "<table class='table table-bordered table-sm'>";
        echo "<thead class='table-light'>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
              </thead><tbody>";

        foreach($expenses as $exp){
            echo "<tr>";
            echo "<td>".htmlspecialchars($exp['description'])."</td>";
            echo "<td class='text-danger'>-₱".number_format($exp['amount'],2)."</td>";
            echo "<td>".date('F j, Y', strtotime($exp['expense_date']))."</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p class='text-center text-muted'>No expenses found.</p>";
    }
}
?>