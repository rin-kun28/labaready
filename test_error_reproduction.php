<?php
session_start();
include 'db_connect.php';

echo "<h2>🔍 Error Reproduction Test</h2>";

// Test different scenarios that might cause the error

echo "<h3>1. Test Direct Database Queries</h3>";

// Test 1: Users query
echo "<h4>Users Query Test:</h4>";
try {
    $users = $conn->query("SELECT * FROM users ORDER BY name ASC");
    if ($users) {
        echo "✅ Users query successful (" . $users->num_rows . " rows)<br>";
    } else {
        echo "❌ Users query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception in users query: " . $e->getMessage() . "<br>";
}

// Test 2: Categories query
echo "<h4>Categories Query Test:</h4>";
try {
    $categories = $conn->query("SELECT * FROM laundry_categories ORDER BY name ASC");
    if ($categories) {
        echo "✅ Categories query successful (" . $categories->num_rows . " rows)<br>";
    } else {
        echo "❌ Categories query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception in categories query: " . $e->getMessage() . "<br>";
}

// Test 3: Customers query
echo "<h4>Customers Query Test:</h4>";
try {
    $customers = $conn->query("SELECT * FROM customers ORDER BY name ASC");
    if ($customers) {
        echo "✅ Customers query successful (" . $customers->num_rows . " rows)<br>";
    } else {
        echo "❌ Customers query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception in customers query: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Test AJAX Actions</h3>";

// Simulate different AJAX actions that might fail
$test_actions = [
    'save_laundry',
    'save_user', 
    'save_category',
    'save_expenditure',
    'delete_user',
    'login'
];

foreach ($test_actions as $action) {
    echo "<h4>Testing action: $action</h4>";
    
    // Check if the action exists in ajax.php
    $ajax_content = file_get_contents('ajax.php');
    if (strpos($ajax_content, "action == \"$action\"") !== false || strpos($ajax_content, "action'] == \"$action\"") !== false) {
        echo "✅ Action '$action' found in ajax.php<br>";
    } else {
        echo "❌ Action '$action' NOT found in ajax.php<br>";
    }
}

echo "<h3>3. Test Session Information</h3>";
if (isset($_SESSION['login_id'])) {
    echo "✅ User logged in: ID = " . $_SESSION['login_id'] . "<br>";
    if (isset($_SESSION['login_name'])) {
        echo "✅ User name: " . $_SESSION['login_name'] . "<br>";
    }
} else {
    echo "❌ No user logged in<br>";
    echo "<a href='login.php'>Login here</a><br>";
}

echo "<h3>4. Test Database Connection Details</h3>";
echo "Host: " . DB_HOST . "<br>";
echo "User: " . DB_USER . "<br>";
echo "Database: " . DB_NAME . "<br>";
echo "Connection status: " . ($conn->ping() ? "✅ Active" : "❌ Inactive") . "<br>";

echo "<h3>5. Test Recent Error Scenarios</h3>";

// Test scenarios that might cause the error
echo "<h4>Scenario 1: Wrong table context</h4>";
try {
    // This might fail if there's a table without 'name' column
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        $columns = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'name'");
        if ($columns->num_rows == 0) {
            echo "⚠️ Table '$table' does NOT have 'name' column<br>";
        } else {
            echo "✅ Table '$table' has 'name' column<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}

echo "<h4>Scenario 2: Database switching</h4>";
$current_db = $conn->query("SELECT DATABASE() as db")->fetch_assoc()['db'];
echo "Current database: <strong>$current_db</strong><br>";

if ($current_db !== 'laundry_db') {
    echo "❌ WARNING: Not using laundry_db database!<br>";
} else {
    echo "✅ Using correct database<br>";
}

echo "<h3>6. Manual Error Trigger Test</h3>";
echo "<p>Click the buttons below to test specific scenarios:</p>";

echo "<button onclick='testUsersPage()'>Test Users Page</button> ";
echo "<button onclick='testAjaxCall()'>Test AJAX Call</button> ";
echo "<button onclick='testLoginCheck()'>Test Login Check</button><br><br>";

?>

<script src="assets/js/jquery.js"></script>
<script>
function testUsersPage() {
    window.location.href = 'index.php?page=users';
}

function testAjaxCall() {
    $.ajax({
        url: 'ajax.php?action=login',
        method: 'POST',
        data: {username: 'test', password: 'test'},
        success: function(resp) {
            alert('AJAX call successful: ' + resp);
        },
        error: function(xhr, status, error) {
            alert('AJAX call failed: ' + error + '\nResponse: ' + xhr.responseText);
        }
    });
}

function testLoginCheck() {
    $.ajax({
        url: 'users.php',
        method: 'GET',
        success: function(resp) {
            alert('Users page loaded successfully');
        },
        error: function(xhr, status, error) {
            alert('Users page failed: ' + error + '\nResponse: ' + xhr.responseText);
        }
    });
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
button { padding: 8px 15px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #0056b3; }
</style>
