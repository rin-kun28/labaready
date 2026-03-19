<?php
include 'db_connect.php';

echo "<h2>🔧 Fix Specific ID Issues</h2>";

echo "<h3>Current Status</h3>";

// Check customers table
$customers = $conn->query("SELECT id FROM customers ORDER BY id");
$customer_ids = [];
if ($customers) {
    while ($row = $customers->fetch_assoc()) {
        $customer_ids[] = $row['id'];
    }
}

// Check payments table  
$payments = $conn->query("SELECT id FROM payments ORDER BY id");
$payment_ids = [];
if ($payments) {
    while ($row = $payments->fetch_assoc()) {
        $payment_ids[] = $row['id'];
    }
}

echo "<p><strong>Customers IDs:</strong> " . implode(', ', $customer_ids) . "</p>";
echo "<p><strong>Payments IDs:</strong> " . implode(', ', $payment_ids) . "</p>";

// Get auto-increment values
$customer_status = $conn->query("SHOW TABLE STATUS LIKE 'customers'")->fetch_assoc();
$payment_status = $conn->query("SHOW TABLE STATUS LIKE 'payments'")->fetch_assoc();

echo "<p><strong>Customers next auto-increment:</strong> " . $customer_status['Auto_increment'] . "</p>";
echo "<p><strong>Payments next auto-increment:</strong> " . $payment_status['Auto_increment'] . "</p>";

if (isset($_POST['fix_customers'])) {
    echo "<h3>🔧 Fixing Customers Table...</h3>";
    
    try {
        $conn->begin_transaction();
        
        // Option 1: Renumber existing records to be sequential
        echo "<h4>Option 1: Renumber existing records</h4>";
        
        // Get all customer records
        $customers_data = $conn->query("SELECT * FROM customers ORDER BY id");
        $customers_array = [];
        while ($row = $customers_data->fetch_assoc()) {
            $customers_array[] = $row;
        }
        
        // Temporarily disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Clear the table
        $conn->query("DELETE FROM customers");
        
        // Reset auto-increment to 1
        $conn->query("ALTER TABLE customers AUTO_INCREMENT = 1");
        
        // Re-insert records with new sequential IDs
        $new_id = 1;
        $id_mapping = [];
        
        foreach ($customers_array as $customer) {
            $old_id = $customer['id'];
            $name = $conn->real_escape_string($customer['customer_name']);
            $number = $conn->real_escape_string($customer['customer_number']);
            $date = $customer['date_created'];
            
            $insert_sql = "INSERT INTO customers (id, customer_name, customer_number, date_created) VALUES ($new_id, '$name', '$number', '$date')";
            $conn->query($insert_sql);
            
            $id_mapping[$old_id] = $new_id;
            echo "✅ Customer ID $old_id → $new_id: " . htmlspecialchars($name) . "<br>";
            $new_id++;
        }
        
        // Update laundry_list table to use new customer IDs
        foreach ($id_mapping as $old_id => $new_id) {
            $conn->query("UPDATE laundry_list SET customer_id = $new_id WHERE customer_id = $old_id");
        }
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $conn->commit();
        
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>Customers table fixed!</strong> IDs are now sequential: 1, 2, 3, 4, 5. Next ID will be 6.";
        echo "</div>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

if (isset($_POST['fix_payments'])) {
    echo "<h3>🔧 Fixing Payments Table...</h3>";
    
    try {
        $conn->begin_transaction();
        
        // Get all payment records
        $payments_data = $conn->query("SELECT * FROM payments ORDER BY id");
        $payments_array = [];
        while ($row = $payments_data->fetch_assoc()) {
            $payments_array[] = $row;
        }
        
        // Clear the table
        $conn->query("DELETE FROM payments");
        
        // Reset auto-increment to 1
        $conn->query("ALTER TABLE payments AUTO_INCREMENT = 1");
        
        // Re-insert records with new sequential IDs
        $new_id = 1;
        
        foreach ($payments_array as $payment) {
            $laundry_id = $payment['laundry_id'];
            $user_id = $payment['user_id'];
            $amount = $payment['amount_paid'];
            $method = $conn->real_escape_string($payment['payment_method']);
            $ref = $payment['payment_ref'] ? "'" . $conn->real_escape_string($payment['payment_ref']) . "'" : 'NULL';
            $date = $payment['payment_date'];
            
            $insert_sql = "INSERT INTO payments (id, laundry_id, user_id, amount_paid, payment_method, payment_ref, payment_date) 
                          VALUES ($new_id, $laundry_id, $user_id, $amount, '$method', $ref, '$date')";
            $conn->query($insert_sql);
            
            echo "✅ Payment ID " . $payment['id'] . " → $new_id: ₱" . number_format($amount, 2) . " ($method)<br>";
            $new_id++;
        }
        
        $conn->commit();
        
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>Payments table fixed!</strong> IDs are now sequential starting from 1. Next ID will be $new_id.";
        echo "</div>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

if (!isset($_POST['fix_customers']) && !isset($_POST['fix_payments'])) {
    echo "<h3>⚠️ Fix Options</h3>";
    
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🔧 Customers Table Fix</h4>";
    echo "<p><strong>Current:</strong> IDs are 1, 2, 3, 4, 10 (next would be 11)</p>";
    echo "<p><strong>After fix:</strong> IDs will be 1, 2, 3, 4, 5 (next will be 6)</p>";
    echo "<p>This will renumber the customer with ID 10 to ID 5 and update all related laundry records.</p>";
    
    echo "<form method='POST' style='display: inline;'>";
    echo "<button type='submit' name='fix_customers' style='background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "Fix Customers Table";
    echo "</button>";
    echo "</form>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>💳 Payments Table Fix</h4>";
    echo "<p><strong>Current:</strong> ID is 4 (next would be 5)</p>";
    echo "<p><strong>After fix:</strong> ID will be 1 (next will be 2)</p>";
    echo "<p>This will renumber the payment with ID 4 to ID 1.</p>";
    
    echo "<form method='POST' style='display: inline;'>";
    echo "<button type='submit' name='fix_payments' style='background: #17a2b8; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "Fix Payments Table";
    echo "</button>";
    echo "</form>";
    echo "</div>";
}

// Show updated status if fixes were applied
if (isset($_POST['fix_customers']) || isset($_POST['fix_payments'])) {
    echo "<h3>Updated Status</h3>";
    
    // Re-check customers
    $customers = $conn->query("SELECT id FROM customers ORDER BY id");
    $customer_ids = [];
    if ($customers) {
        while ($row = $customers->fetch_assoc()) {
            $customer_ids[] = $row['id'];
        }
    }
    
    // Re-check payments
    $payments = $conn->query("SELECT id FROM payments ORDER BY id");
    $payment_ids = [];
    if ($payments) {
        while ($row = $payments->fetch_assoc()) {
            $payment_ids[] = $row['id'];
        }
    }
    
    echo "<p><strong>Customers IDs:</strong> " . implode(', ', $customer_ids) . "</p>";
    echo "<p><strong>Payments IDs:</strong> " . implode(', ', $payment_ids) . "</p>";
    
    // Get updated auto-increment values
    $customer_status = $conn->query("SHOW TABLE STATUS LIKE 'customers'")->fetch_assoc();
    $payment_status = $conn->query("SHOW TABLE STATUS LIKE 'payments'")->fetch_assoc();
    
    echo "<p><strong>Customers next auto-increment:</strong> " . $customer_status['Auto_increment'] . "</p>";
    echo "<p><strong>Payments next auto-increment:</strong> " . $payment_status['Auto_increment'] . "</p>";
}

echo "<br><a href='fix_auto_increment.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Fix All Tables</a>";
echo " <a href='index.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Back to Dashboard</a>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
button:hover { opacity: 0.9; }
</style>
