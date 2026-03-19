<?php
include 'db_connect.php';
session_start();

echo "<h2>🔧 Fix Expenditure User Links</h2>";

if (!isset($_SESSION['login_id'])) {
    echo "<p style='color: red;'>Please login first to run this fix.</p>";
    echo "<a href='login.php'>Login Here</a>";
    exit;
}

$current_user_id = $_SESSION['login_id'];

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id = $current_user_id");
$user = $user_info->fetch_assoc();

echo "<p><strong>Current User:</strong> " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['username']) . ")</p>";

// Check expenditures with NULL user_id
$null_expenditures = $conn->query("SELECT COUNT(*) as count FROM expenditures WHERE user_id IS NULL");
$null_count = $null_expenditures->fetch_assoc()['count'];

echo "<p><strong>Expenditures with NULL user_id:</strong> $null_count</p>";

if ($null_count > 0) {
    if (isset($_POST['fix_expenditures'])) {
        // Update all NULL user_id expenditures to current user
        $update_result = $conn->query("UPDATE expenditures SET user_id = $current_user_id WHERE user_id IS NULL");
        
        if ($update_result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ Successfully updated $null_count expenditures to be linked to " . htmlspecialchars($user['name']);
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ Error updating expenditures: " . $conn->error;
            echo "</div>";
        }
    } else {
        echo "<form method='POST'>";
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>⚠️ Warning:</strong> This will assign all expenditures with NULL user_id to the current user (" . htmlspecialchars($user['name']) . ").</p>";
        echo "<p>This is a one-time fix to establish proper relationships.</p>";
        echo "<button type='submit' name='fix_expenditures' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
        echo "Fix Expenditure User Links";
        echo "</button>";
        echo "</div>";
        echo "</form>";
    }
} else {
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ All expenditures already have user links!";
    echo "</div>";
}

// Show current expenditure-user relationships
echo "<h3>Current Expenditure-User Relationships</h3>";
$expenditures = $conn->query("
    SELECT 
        e.id,
        e.details,
        e.total,
        e.date,
        e.user_id,
        u.name as user_name,
        u.username
    FROM expenditures e
    LEFT JOIN users u ON e.user_id = u.id
    ORDER BY e.date DESC
    LIMIT 10
");

if ($expenditures->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>ID</th>
            <th>Details</th>
            <th>Total</th>
            <th>Date</th>
            <th>User</th>
            <th>Status</th>
          </tr>";
    
    while ($row = $expenditures->fetch_assoc()) {
        $status = $row['user_id'] ? "✅ Linked" : "❌ No User";
        $status_color = $row['user_id'] ? "green" : "red";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['details'], 0, 30)) . "...</td>";
        echo "<td>₱" . number_format($row['total'], 2) . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . ($row['user_name'] ? htmlspecialchars($row['user_name']) . " (" . htmlspecialchars($row['username']) . ")" : 'N/A') . "</td>";
        echo "<td style='color: $status_color;'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><a href='test_relationships.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Test All Relationships</a>";
echo " <a href='index.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Back to Dashboard</a>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
</style>
