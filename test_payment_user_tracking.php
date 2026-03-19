<?php
session_start();
include 'db_connect.php';

echo "<h2>💳 Test Payment User Tracking</h2>";

// Check current session
echo "<h3>1. Current Session Status</h3>";
if (isset($_SESSION['login_id'])) {
    $user_info = $conn->query("SELECT * FROM users WHERE id = " . $_SESSION['login_id']);
    if ($user_info->num_rows > 0) {
        $user = $user_info->fetch_assoc();
        echo "✅ <strong>Logged in as:</strong> " . htmlspecialchars($user['name']) . " (ID: " . $user['id'] . ")<br>";
        echo "✅ <strong>Username:</strong> " . htmlspecialchars($user['username']) . "<br>";
        echo "✅ <strong>User Type:</strong> " . ($user['type'] == 1 ? 'Admin' : 'Staff') . "<br>";
    }
} else {
    echo "❌ <strong>No user logged in.</strong> Setting default user ID to 1 (Admin)<br>";
    $_SESSION['login_id'] = 1; // Set for testing
    $_SESSION['login_name'] = 'Admin';
}

echo "<h3>2. Current Payments with User Tracking</h3>";
$payments_with_users = $conn->query("
    SELECT 
        p.id,
        p.laundry_id,
        p.user_id,
        p.amount_paid,
        p.payment_method,
        p.payment_date,
        u.name as user_name,
        u.username,
        ll.customer_name
    FROM payments p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN laundry_list ll ON p.laundry_id = ll.id
    ORDER BY p.payment_date DESC
    LIMIT 10
");

if ($payments_with_users->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Payment ID</th>
            <th>Laundry ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>User ID</th>
            <th>User Name</th>
            <th>Date</th>
            <th>Status</th>
          </tr>";
    
    while ($row = $payments_with_users->fetch_assoc()) {
        $status = $row['user_id'] ? "✅ Tracked" : "❌ No User";
        $status_color = $row['user_id'] ? "green" : "red";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['laundry_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name'] ?: 'N/A') . "</td>";
        echo "<td>₱" . number_format($row['amount_paid'], 2) . "</td>";
        echo "<td>" . $row['payment_method'] . "</td>";
        echo "<td>" . ($row['user_id'] ?: 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['user_name'] ?: 'N/A') . "</td>";
        echo "<td>" . $row['payment_date'] . "</td>";
        echo "<td style='color: $status_color;'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No payments found.</p>";
}

echo "<h3>3. Test New Payment Creation</h3>";

// Simulate creating a new payment
echo "<h4>Simulating new laundry with payment...</h4>";

try {
    $conn->begin_transaction();
    
    // Create test customer
    $test_customer_name = "Payment Test Customer " . date('H:i:s');
    $test_customer_number = "09" . rand(100000000, 999999999);
    
    $insert_customer = $conn->query("INSERT INTO customers (customer_name, customer_number) VALUES ('$test_customer_name', '$test_customer_number')");
    if (!$insert_customer) {
        throw new Exception("Customer creation failed: " . $conn->error);
    }
    $customer_id = $conn->insert_id;
    echo "✅ Test customer created (ID: $customer_id)<br>";
    
    // Create test laundry
    $total_amount = 100.00;
    $amount_tendered = 100.00;
    $amount_change = 0.00;
    
    $laundry_sql = "INSERT INTO laundry_list (
        customer_id, customer_name, customer_number, 
        remarks, total_amount, pay_status, 
        amount_tendered, amount_change, status
    ) VALUES (
        $customer_id, '$test_customer_name', '$test_customer_number',
        'Test payment tracking', $total_amount, 1,
        $amount_tendered, $amount_change, 0
    )";
    
    $laundry_result = $conn->query($laundry_sql);
    if (!$laundry_result) {
        throw new Exception("Laundry creation failed: " . $conn->error);
    }
    $laundry_id = $conn->insert_id;
    echo "✅ Test laundry created (ID: $laundry_id)<br>";
    
    // Create payment record with user tracking (using the new logic)
    $user_id = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : 1;
    $payment_method = 'Cash';
    
    $payment_sql = "INSERT INTO payments (laundry_id, user_id, amount_paid, payment_method) 
                   VALUES ($laundry_id, $user_id, $amount_tendered, '$payment_method')";
    
    $payment_result = $conn->query($payment_sql);
    if (!$payment_result) {
        throw new Exception("Payment creation failed: " . $conn->error);
    }
    $payment_id = $conn->insert_id;
    echo "✅ Payment record created (ID: $payment_id) with user_id: $user_id<br>";
    
    $conn->commit();
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "🎉 <strong>SUCCESS!</strong> New payment created with proper user tracking!<br>";
    echo "Payment ID: $payment_id<br>";
    echo "Laundry ID: $laundry_id<br>";
    echo "User ID: $user_id<br>";
    echo "Amount: ₱" . number_format($amount_tendered, 2) . "<br>";
    echo "</div>";
    
    // Verify the payment was created correctly
    $verify_payment = $conn->query("
        SELECT p.*, u.name as user_name 
        FROM payments p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.id = $payment_id
    ");
    
    if ($verify_payment->num_rows > 0) {
        $payment_data = $verify_payment->fetch_assoc();
        echo "<h4>✅ Payment Verification:</h4>";
        echo "<ul>";
        echo "<li><strong>Payment ID:</strong> " . $payment_data['id'] . "</li>";
        echo "<li><strong>Laundry ID:</strong> " . $payment_data['laundry_id'] . "</li>";
        echo "<li><strong>User ID:</strong> " . $payment_data['user_id'] . "</li>";
        echo "<li><strong>User Name:</strong> " . htmlspecialchars($payment_data['user_name']) . "</li>";
        echo "<li><strong>Amount:</strong> ₱" . number_format($payment_data['amount_paid'], 2) . "</li>";
        echo "<li><strong>Method:</strong> " . $payment_data['payment_method'] . "</li>";
        echo "<li><strong>Date:</strong> " . $payment_data['payment_date'] . "</li>";
        echo "</ul>";
    }
    
} catch(Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h3>4. Payment-User Relationship Summary</h3>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>";

$payment_stats = $conn->query("
    SELECT 
        COUNT(*) as total_payments,
        COUNT(user_id) as payments_with_user,
        COUNT(*) - COUNT(user_id) as payments_without_user
    FROM payments
");

if ($payment_stats->num_rows > 0) {
    $stats = $payment_stats->fetch_assoc();
    echo "<h4>Payment Statistics:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Payments:</strong> " . $stats['total_payments'] . "</li>";
    echo "<li><strong>With User Tracking:</strong> " . $stats['payments_with_user'] . " ✅</li>";
    echo "<li><strong>Without User Tracking:</strong> " . $stats['payments_without_user'] . " " . ($stats['payments_without_user'] > 0 ? "❌" : "✅") . "</li>";
    echo "</ul>";
    
    if ($stats['payments_without_user'] == 0) {
        echo "<p style='color: green;'><strong>🎉 All payments now have proper user tracking!</strong></p>";
    }
}

echo "</div>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f0f0f0; }
</style>
