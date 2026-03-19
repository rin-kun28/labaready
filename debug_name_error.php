<?php
echo "<h2>🔍 Debug 'Unknown column name' Error</h2>";

// Test database connection
include 'db_connect.php';

echo "<h3>1. Database Connection Test</h3>";
if ($conn) {
    echo "✅ Database connected successfully<br>";
    echo "Database: " . $conn->get_server_info() . "<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "<h3>2. Current Database Test</h3>";
$current_db = $conn->query("SELECT DATABASE() as current_db");
if ($current_db) {
    $db_name = $current_db->fetch_assoc()['current_db'];
    echo "Current database: <strong>$db_name</strong><br>";
} else {
    echo "❌ Could not determine current database<br>";
}

echo "<h3>3. Users Table Structure Test</h3>";
$describe = $conn->query("DESCRIBE users");
if ($describe) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $describe->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing users table: " . $conn->error . "<br>";
}

echo "<h3>4. Test Problematic Query</h3>";
echo "Testing: <code>SELECT * FROM users ORDER BY name ASC</code><br>";

$users_query = $conn->query("SELECT * FROM users ORDER BY name ASC");
if ($users_query) {
    echo "✅ Query executed successfully<br>";
    echo "Number of rows: " . $users_query->num_rows . "<br>";
    
    if ($users_query->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Type</th></tr>";
        while ($row = $users_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . ($row['type'] == 1 ? 'Admin' : 'Staff') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "❌ Query failed: <strong>" . $conn->error . "</strong><br>";
}

echo "<h3>5. Test Other Tables with 'name' Column</h3>";

// Test laundry_categories
echo "<h4>Laundry Categories:</h4>";
$cat_query = $conn->query("SELECT * FROM laundry_categories ORDER BY name ASC LIMIT 3");
if ($cat_query) {
    echo "✅ laundry_categories query successful (" . $cat_query->num_rows . " rows)<br>";
} else {
    echo "❌ laundry_categories query failed: " . $conn->error . "<br>";
}

// Test customers
echo "<h4>Customers:</h4>";
$cust_query = $conn->query("SELECT * FROM customers ORDER BY name ASC LIMIT 3");
if ($cust_query) {
    echo "✅ customers query successful (" . $cust_query->num_rows . " rows)<br>";
} else {
    echo "❌ customers query failed: " . $conn->error . "<br>";
}

echo "<h3>6. Check All Tables with 'name' Column</h3>";
$tables_with_name = $conn->query("
    SELECT TABLE_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'laundry_db' 
    AND COLUMN_NAME = 'name'
");

if ($tables_with_name) {
    echo "Tables with 'name' column:<br>";
    echo "<ul>";
    while ($row = $tables_with_name->fetch_assoc()) {
        echo "<li>" . $row['TABLE_NAME'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "❌ Error checking tables: " . $conn->error . "<br>";
}

echo "<h3>7. Recent Error Log Check</h3>";
$error_log = "c:\\xampp\\apache\\logs\\error.log";
if (file_exists($error_log)) {
    $log_content = file_get_contents($error_log);
    $recent_errors = array_slice(explode("\n", $log_content), -20);
    
    $name_errors = array_filter($recent_errors, function($line) {
        return strpos($line, 'Unknown column') !== false && strpos($line, 'name') !== false;
    });
    
    if (!empty($name_errors)) {
        echo "Recent 'Unknown column name' errors found:<br>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border: 1px solid #ddd;'>";
        foreach ($name_errors as $error) {
            echo htmlspecialchars($error) . "\n";
        }
        echo "</pre>";
    } else {
        echo "✅ No recent 'Unknown column name' errors found in log<br>";
    }
} else {
    echo "❌ Error log file not found<br>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 5px 10px; text-align: left; }
th { background: #f0f0f0; }
</style>
