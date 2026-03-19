<?php
/**
 * Database Connection Configuration
 * Compatible with latest XAMPP (PHP 8.x, MySQL 8.x)
 */

// Enable error reporting for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'laundry_db');

try {
    // Create connection with proper charset
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Set charset to utf8mb4 for full Unicode support (including emojis)
    $conn->set_charset('utf8mb4');
    
    // Set timezone (adjust as needed)
    $conn->query("SET time_zone = '+08:00'");
    
} catch (mysqli_sql_exception $e) {
    // Log error securely (don't expose in production)
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}
?>
