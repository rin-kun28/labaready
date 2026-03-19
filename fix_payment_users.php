<?php
include 'db_connect.php';

echo "<h2>🔧 Fix Payment User IDs</h2>";

// Check current status
$null_payments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE user_id IS NULL");
$null_count = $null_payments->fetch_assoc()['count'];

echo "<p><strong>Payments with NULL user_id:</strong> $null_count</p>";

if ($null_count > 0) {
    if (isset($_POST['fix_payments'])) {
        // Update all NULL user_id payments to Admin (user ID 1)
        $update_result = $conn->query("UPDATE payments SET user_id = 1 WHERE user_id IS NULL");
        
        if ($update_result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Successfully updated $null_count payments to be linked to Admin (User ID: 1)";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Error updating payments: " . $conn->error;
            echo "</div>";
        }
    } else {
        echo "<form method='POST'>";
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>⚠️ Warning:</strong> This will assign all payments with NULL user_id to Admin (User ID: 1).</p>";
        echo "<p>This ensures proper tracking of who processed each payment.</p>";
        echo "<button type='submit' name='fix_payments' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
        echo "Fix Payment User Links";
        echo "</button>";
        echo "</div>";
        echo "</form>";
    }
} else {
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ All payments already have user links!";
    echo "</div>";
}

// Show current payment-user relationships
echo "<h3>Current Payment-User Relationships</h3>";
$payments = $conn->query("
    SELECT 
        p.id,
        p.laundry_id,
        p.amount_paid,
        p.payment_method,
        p.payment_date,
        p.user_id,
        u.name as user_name,
        u.username,
        ll.customer_name
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN laundry_list ll ON p.laundry_id = ll.id
    ORDER BY p.payment_date DESC
    LIMIT 10
");

if ($payments->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Payment ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>User ID</th>
            <th>User Name</th>
            <th>Date</th>
            <th>Status</th>
          </tr>";
    
    while ($row = $payments->fetch_assoc()) {
        $status = $row['user_id'] ? "✅ Linked" : "❌ No User";
        $status_color = $row['user_id'] ? "green" : "red";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name'] ?: 'N/A') . "</td>";
        echo "<td>₱" . number_format($row['amount_paid'], 2) . "</td>";
        echo "<td>" . $row['payment_method'] . "</td>";
        echo "<td>" . ($row['user_id'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['user_name'] ? htmlspecialchars($row['user_name']) . " (" . htmlspecialchars($row['username']) . ")" : 'N/A') . "</td>";
        echo "<td>" . $row['payment_date'] . "</td>";
        echo "<td style='color: $status_color;'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><a href='test_payment_user_tracking.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Test Payment Tracking</a>";
echo " <a href='test_relationships.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Test All Relationships</a>";
echo " <a href='index.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Back to Dashboard</a>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
</style>
