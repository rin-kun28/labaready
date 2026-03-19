<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing All Database Queries</h2>";

include 'db_connect.php';

// Test connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Database connected successfully<br><br>";

// Test all the queries that use 'name' column
$queries_to_test = [
    "Users table" => "SELECT * FROM users ORDER BY name ASC",
    "Laundry categories" => "SELECT * FROM laundry_categories ORDER BY name ASC", 
    "Supply list" => "SELECT * FROM supply_list ORDER BY name ASC",
    "Customers table" => "SELECT * FROM customers ORDER BY name ASC"
];

foreach ($queries_to_test as $description => $query) {
    echo "<h3>Testing: $description</h3>";
    echo "Query: <code>$query</code><br>";
    
    try {
        $result = $conn->query($query);
        if ($result) {
            echo "✅ SUCCESS - Rows returned: " . $result->num_rows . "<br>";
        } else {
            echo "❌ FAILED - " . $conn->error . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ EXCEPTION - " . $e->getMessage() . "<br>";
    }
    echo "<br>";
}

// Test table structures
echo "<h2>Table Structures</h2>";
$tables = ['users', 'laundry_categories', 'supply_list', 'customers'];

foreach ($tables as $table) {
    echo "<h3>$table table structure:</h3>";
    try {
        $result = $conn->query("DESCRIBE $table");
        if ($result) {
            echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
    echo "<br>";
}

$conn->close();
?>
