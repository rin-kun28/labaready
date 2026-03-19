<?php
include 'db_connect.php';

echo "<h2>🔧 Fix Auto-Increment Values</h2>";

// List of tables to fix
$tables_to_fix = [
    'customers',
    'payments', 
    'users',
    'laundry_list',
    'laundry_categories',
    'expenditures',
    'supply_list',
    'laundry_items',
    'supplies_used',
    'inventory'
];

echo "<h3>Current Auto-Increment Status</h3>";

// Show current auto-increment values
foreach ($tables_to_fix as $table) {
    $result = $conn->query("SHOW TABLE STATUS LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $auto_increment = $row['Auto_increment'];
        
        // Get the highest current ID
        $max_id_result = $conn->query("SELECT MAX(id) as max_id FROM $table");
        $max_id = 0;
        if ($max_id_result && $max_id_result->num_rows > 0) {
            $max_row = $max_id_result->fetch_assoc();
            $max_id = $max_row['max_id'] ? $max_row['max_id'] : 0;
        }
        
        $next_should_be = $max_id + 1;
        $status = ($auto_increment == $next_should_be) ? "✅ OK" : "❌ Needs Fix";
        
        echo "<p><strong>$table:</strong> Current auto-increment: $auto_increment, Max ID: $max_id, Should be: $next_should_be $status</p>";
    }
}

if (isset($_POST['fix_auto_increment'])) {
    echo "<h3>🔧 Fixing Auto-Increment Values...</h3>";
    
    $fixed_count = 0;
    $errors = [];
    
    foreach ($tables_to_fix as $table) {
        try {
            // Get the highest current ID
            $max_id_result = $conn->query("SELECT MAX(id) as max_id FROM $table");
            $max_id = 0;
            if ($max_id_result && $max_id_result->num_rows > 0) {
                $max_row = $max_id_result->fetch_assoc();
                $max_id = $max_row['max_id'] ? $max_row['max_id'] : 0;
            }
            
            $next_id = $max_id + 1;
            
            // Reset auto-increment to the next available ID
            $alter_sql = "ALTER TABLE $table AUTO_INCREMENT = $next_id";
            $result = $conn->query($alter_sql);
            
            if ($result) {
                echo "✅ <strong>$table:</strong> Auto-increment reset to $next_id<br>";
                $fixed_count++;
            } else {
                $error_msg = "Failed to fix $table: " . $conn->error;
                $errors[] = $error_msg;
                echo "❌ <strong>$table:</strong> $error_msg<br>";
            }
            
        } catch (Exception $e) {
            $error_msg = "Exception fixing $table: " . $e->getMessage();
            $errors[] = $error_msg;
            echo "❌ <strong>$table:</strong> $error_msg<br>";
        }
    }
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>🎉 Auto-Increment Fix Complete!</h4>";
    echo "<p><strong>Tables Fixed:</strong> $fixed_count out of " . count($tables_to_fix) . "</p>";
    if (!empty($errors)) {
        echo "<p><strong>Errors:</strong> " . count($errors) . "</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // Show updated status
    echo "<h3>Updated Auto-Increment Status</h3>";
    foreach ($tables_to_fix as $table) {
        $result = $conn->query("SHOW TABLE STATUS LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $auto_increment = $row['Auto_increment'];
            
            $max_id_result = $conn->query("SELECT MAX(id) as max_id FROM $table");
            $max_id = 0;
            if ($max_id_result && $max_id_result->num_rows > 0) {
                $max_row = $max_id_result->fetch_assoc();
                $max_id = $max_row['max_id'] ? $max_row['max_id'] : 0;
            }
            
            $next_should_be = $max_id + 1;
            $status = ($auto_increment == $next_should_be) ? "✅ Fixed" : "❌ Still Issues";
            
            echo "<p><strong>$table:</strong> Auto-increment: $auto_increment, Max ID: $max_id $status</p>";
        }
    }
    
} else {
    echo "<h3>⚠️ Fix Auto-Increment Values</h3>";
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>What this will do:</strong></p>";
    echo "<ul>";
    echo "<li>Reset auto-increment counters to the next available ID</li>";
    echo "<li>Fix gaps in ID sequences caused by deleted records</li>";
    echo "<li>Ensure new records get sequential IDs</li>";
    echo "</ul>";
    echo "<p><strong>Example:</strong> If customers table has IDs 1,2,3,4,10 and you want the next to be 5, this will fix it.</p>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='fix_auto_increment' style='background: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
    echo "🔧 Fix All Auto-Increment Values";
    echo "</button>";
    echo "</form>";
    echo "</div>";
}

echo "<h3>📊 Table Information</h3>";

// Show detailed table information
foreach ($tables_to_fix as $table) {
    $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = 0;
    if ($count_result && $count_result->num_rows > 0) {
        $count = $count_result->fetch_assoc()['count'];
    }
    
    if ($count > 0) {
        $sample_result = $conn->query("SELECT id FROM $table ORDER BY id LIMIT 5");
        $ids = [];
        if ($sample_result && $sample_result->num_rows > 0) {
            while ($row = $sample_result->fetch_assoc()) {
                $ids[] = $row['id'];
            }
        }
        
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
        echo "<strong>$table:</strong> $count records, Sample IDs: " . implode(', ', $ids);
        if (count($ids) < $count) {
            echo " ... (and " . ($count - count($ids)) . " more)";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
        echo "<strong>$table:</strong> No records";
        echo "</div>";
    }
}

echo "<br><a href='index.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Back to Dashboard</a>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
button:hover { opacity: 0.9; }
</style>
