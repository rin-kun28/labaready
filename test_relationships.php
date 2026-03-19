<?php
include 'db_connect.php';
session_start();

echo "<h2>🔗 Database Relationships Test</h2>";

// Test 1: Laundry List to Payments Connection
echo "<h3>1. Laundry List ↔ Payments Relationship</h3>";
$laundry_payments = $conn->query("
    SELECT 
        ll.id as laundry_id,
        ll.customer_name,
        ll.total_amount,
        ll.pay_status,
        p.id as payment_id,
        p.amount_paid,
        p.payment_method,
        p.payment_ref,
        p.payment_date
    FROM laundry_list ll
    LEFT JOIN payments p ON ll.id = p.laundry_id
    WHERE ll.pay_status = 1
    ORDER BY ll.date_created DESC
    LIMIT 5
");

if ($laundry_payments->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Laundry ID</th>
            <th>Customer</th>
            <th>Total Amount</th>
            <th>Payment ID</th>
            <th>Amount Paid</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Date</th>
          </tr>";
    
    while ($row = $laundry_payments->fetch_assoc()) {
        $status = $row['payment_id'] ? "✅ Connected" : "❌ Missing Payment";
        echo "<tr>";
        echo "<td>" . $row['laundry_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
        echo "<td>₱" . number_format($row['total_amount'], 2) . "</td>";
        echo "<td>" . ($row['payment_id'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['amount_paid'] ? '₱' . number_format($row['amount_paid'], 2) : 'N/A') . "</td>";
        echo "<td>" . ($row['payment_method'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['payment_ref'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['payment_date'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No paid laundry entries found.</p>";
}

// Test 2: Laundry List to Customers Connection
echo "<h3>2. Laundry List ↔ Customers Relationship</h3>";
$laundry_customers = $conn->query("
    SELECT 
        ll.id as laundry_id,
        ll.customer_name,
        ll.customer_number,
        ll.customer_id,
        c.id as customer_table_id,
        c.name as customer_table_name,
        c.phone as customer_table_phone
    FROM laundry_list ll
    LEFT JOIN customers c ON ll.customer_id = c.id
    ORDER BY ll.date_created DESC
    LIMIT 5
");

if ($laundry_customers->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Laundry ID</th>
            <th>Customer Name (Laundry)</th>
            <th>Customer Number (Laundry)</th>
            <th>Customer ID Link</th>
            <th>Customer Name (Table)</th>
            <th>Customer Phone (Table)</th>
            <th>Status</th>
          </tr>";
    
    while ($row = $laundry_customers->fetch_assoc()) {
        $status = $row['customer_table_id'] ? "✅ Connected" : "❌ Not Linked";
        echo "<tr>";
        echo "<td>" . $row['laundry_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_number']) . "</td>";
        echo "<td>" . ($row['customer_id'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['customer_table_name'] ? htmlspecialchars($row['customer_table_name']) : 'N/A') . "</td>";
        echo "<td>" . ($row['customer_table_phone'] ? htmlspecialchars($row['customer_table_phone']) : 'N/A') . "</td>";
        echo "<td style='color: " . ($row['customer_table_id'] ? 'green' : 'red') . ";'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No laundry entries found.</p>";
}

// Test 3: Users to Expenditures Connection
echo "<h3>3. Users ↔ Expenditures Relationship</h3>";
$user_expenditures = $conn->query("
    SELECT 
        e.id as expenditure_id,
        e.details,
        e.total,
        e.date,
        e.user_id,
        u.id as user_table_id,
        u.name as user_name,
        u.username
    FROM expenditures e
    LEFT JOIN users u ON e.user_id = u.id
    ORDER BY e.date DESC
    LIMIT 5
");

if ($user_expenditures->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Expenditure ID</th>
            <th>Details</th>
            <th>Total</th>
            <th>Date</th>
            <th>User ID Link</th>
            <th>User Name</th>
            <th>Username</th>
            <th>Status</th>
          </tr>";
    
    while ($row = $user_expenditures->fetch_assoc()) {
        $status = $row['user_table_id'] ? "✅ Connected" : "❌ No User Link";
        echo "<tr>";
        echo "<td>" . $row['expenditure_id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['details'], 0, 50)) . "...</td>";
        echo "<td>₱" . number_format($row['total'], 2) . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . ($row['user_id'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['user_name'] ? htmlspecialchars($row['user_name']) : 'N/A') . "</td>";
        echo "<td>" . ($row['username'] ? htmlspecialchars($row['username']) : 'N/A') . "</td>";
        echo "<td style='color: " . ($row['user_table_id'] ? 'green' : 'red') . ";'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No expenditures found.</p>";
}

// Test 4: Database Structure Summary
echo "<h3>4. Database Structure Summary</h3>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>";

// Check foreign key constraints
$constraints = $conn->query("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE REFERENCED_TABLE_SCHEMA = 'laundry_db' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

echo "<h4>Foreign Key Relationships:</h4>";
if ($constraints->num_rows > 0) {
    echo "<ul>";
    while ($row = $constraints->fetch_assoc()) {
        echo "<li><strong>" . $row['TABLE_NAME'] . "." . $row['COLUMN_NAME'] . "</strong> → " . 
             $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No foreign key constraints found.</p>";
}

echo "</div>";

// Test 5: Current Session Info
echo "<h3>5. Current Session Info</h3>";
echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
if (isset($_SESSION['login_id'])) {
    $user_info = $conn->query("SELECT * FROM users WHERE id = " . $_SESSION['login_id']);
    if ($user_info->num_rows > 0) {
        $user = $user_info->fetch_assoc();
        echo "<p><strong>Logged in as:</strong> " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['username']) . ")</p>";
        echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
        echo "<p><strong>User Type:</strong> " . ($user['type'] == 1 ? 'Admin' : 'Staff') . "</p>";
    }
} else {
    echo "<p style='color: red;'>No user logged in. Please login to test expenditure user tracking.</p>";
}
echo "</div>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
h2 { color: #333; }
h3 { color: #666; margin-top: 30px; }
</style>
