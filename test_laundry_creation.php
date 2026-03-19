<?php
include 'db_connect.php';

echo "<h2>🧺 Test Laundry Creation Fix</h2>";

echo "<h3>1. Test Customer Table Insert</h3>";

// Test the fixed customer insert query
$test_customer_name = "Test Customer " . date('H:i:s');
$test_customer_number = "09" . rand(100000000, 999999999);

echo "Testing customer insert with:<br>";
echo "Name: $test_customer_name<br>";
echo "Number: $test_customer_number<br><br>";

try {
    $insert_customer = $conn->query("INSERT INTO customers (customer_name, customer_number) VALUES ('$test_customer_name', '$test_customer_number')");
    
    if($insert_customer){
        $customer_id = $conn->insert_id;
        echo "✅ Customer insert successful! Customer ID: $customer_id<br>";
        
        // Verify the insert
        $verify = $conn->query("SELECT * FROM customers WHERE id = $customer_id");
        if($verify && $verify->num_rows > 0) {
            $customer_data = $verify->fetch_assoc();
            echo "✅ Customer data verified:<br>";
            echo "&nbsp;&nbsp;ID: " . $customer_data['id'] . "<br>";
            echo "&nbsp;&nbsp;Name: " . $customer_data['customer_name'] . "<br>";
            echo "&nbsp;&nbsp;Number: " . $customer_data['customer_number'] . "<br>";
            echo "&nbsp;&nbsp;Date: " . $customer_data['date_created'] . "<br>";
        }
    } else {
        echo "❌ Customer insert failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception during customer insert: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Test Complete Laundry Creation Process</h3>";

// Simulate the laundry creation process
$_POST = [
    'customer_name' => $test_customer_name,
    'customer_number' => $test_customer_number,
    'remarks' => 'Test laundry entry',
    'tamount' => '150.00',
    'pay' => '1',
    'tendered' => '150.00',
    'payment_method' => 'Cash',
    'laundry_category_id' => [1],
    'weight' => [2.5],
    'unit_price' => [60],
    'amount' => [150]
];

echo "Simulating laundry creation with payment...<br>";

try {
    $conn->begin_transaction();
    
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $customer_number = $conn->real_escape_string($_POST['customer_number']);
    $remarks = $conn->real_escape_string($_POST['remarks']);
    $total_amount = floatval($_POST['tamount']);
    $pay_status = isset($_POST['pay']) ? 1 : 0;
    $amount_tendered = $pay_status ? floatval($_POST['tendered']) : 0;
    $amount_change = $pay_status ? ($amount_tendered - $total_amount) : 0;
    $status = 0;
    
    // Payment method details
    $payment_method = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : 'Cash';
    $payment_ref = isset($_POST['payment_ref']) ? $conn->real_escape_string($_POST['payment_ref']) : null;
    
    // Insert customer (using the fixed query)
    $customer_id = null;
    if(!empty($customer_name) && !empty($customer_number)){
        $insert_customer = $conn->query("INSERT INTO customers (customer_name, customer_number) VALUES ('$customer_name', '$customer_number')");
        if($insert_customer){
            $customer_id = $conn->insert_id;
            echo "✅ Customer created with ID: $customer_id<br>";
        } else {
            throw new Exception("Customer insert failed: " . $conn->error);
        }
    }
    
    // Insert laundry
    $sql = "INSERT INTO laundry_list (
        customer_id,
        customer_name, 
        customer_number, 
        remarks, 
        total_amount, 
        pay_status, 
        amount_tendered, 
        amount_change, 
        status
    ) VALUES (
        '$customer_id',
        '$customer_name', 
        '$customer_number', 
        '$remarks', 
        '$total_amount', 
        '$pay_status', 
        '$amount_tendered', 
        '$amount_change', 
        '$status'
    )";
    
    $save = $conn->query($sql);
    if(!$save){
        throw new Exception("Laundry insert failed: " . $conn->error);
    }
    $laundry_id = $conn->insert_id;
    echo "✅ Laundry entry created with ID: $laundry_id<br>";
    
    // Insert laundry items
    if(isset($_POST['laundry_category_id'])){
        for($i = 0; $i < count($_POST['laundry_category_id']); $i++){
            $laundry_category_id = intval($_POST['laundry_category_id'][$i]);
            $weight = floatval($_POST['weight'][$i]);
            $unit_price = floatval($_POST['unit_price'][$i]);
            $amount = floatval($_POST['amount'][$i]);

            $stmt = $conn->prepare("INSERT INTO laundry_items (
                laundry_id, 
                laundry_category_id, 
                weight, 
                unit_price, 
                amount
            ) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiddd", 
                $laundry_id,
                $laundry_category_id,
                $weight,
                $unit_price,
                $amount
            );
            if(!$stmt->execute()){
                throw new Exception("Laundry item insert failed: " . $stmt->error);
            }
            $stmt->close();
        }
        echo "✅ Laundry items added<br>";
    }
    
    // Create payment record
    if ($pay_status == 1 && $amount_tendered > 0) {
        $payment_sql = "INSERT INTO payments (laundry_id, amount_paid, payment_method) VALUES ($laundry_id, $amount_tendered, '$payment_method')";
        $payment_result = $conn->query($payment_sql);
        if (!$payment_result) {
            throw new Exception("Payment record failed: " . $conn->error);
        }
        echo "✅ Payment record created<br>";
    }
    
    $conn->commit();
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "🎉 <strong>SUCCESS!</strong> Complete laundry creation process completed successfully!<br>";
    echo "Laundry ID: $laundry_id<br>";
    echo "Customer ID: $customer_id<br>";
    echo "Total Amount: ₱" . number_format($total_amount, 2) . "<br>";
    echo "Payment Method: $payment_method<br>";
    echo "</div>";
    
} catch(Exception $e) {
    $conn->rollback();
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h3>3. Verify Database State</h3>";

// Show recent entries
$recent_customers = $conn->query("SELECT * FROM customers ORDER BY date_created DESC LIMIT 3");
echo "<h4>Recent Customers:</h4>";
if ($recent_customers->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Number</th><th>Date</th></tr>";
    while ($row = $recent_customers->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_number']) . "</td>";
        echo "<td>" . $row['date_created'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$recent_laundry = $conn->query("SELECT * FROM laundry_list ORDER BY date_created DESC LIMIT 3");
echo "<h4>Recent Laundry Entries:</h4>";
if ($recent_laundry->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>";
    while ($row = $recent_laundry->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
        echo "<td>₱" . number_format($row['total_amount'], 2) . "</td>";
        echo "<td>" . ($row['pay_status'] ? 'Paid' : 'Unpaid') . "</td>";
        echo "<td>" . $row['date_created'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 5px 10px; text-align: left; }
th { background: #f0f0f0; }
</style>
