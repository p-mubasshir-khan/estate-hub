<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    echo "Database connection: SUCCESS<br>";
    
    // Check if admin_users table exists
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
    
    // Check admin user
    $stmt = $conn->query("SELECT * FROM admin_users");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<br>Admin users found: " . count($admins) . "<br>";
    foreach ($admins as $admin) {
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Password hash length: " . strlen($admin['password']) . "<br>";
    }
    
    // Test password verification
    $test_password = 'admin123';
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $hash = $stmt->fetchColumn();
    
    echo "<br>Password verification test:<br>";
    echo "Test password: $test_password<br>";
    echo "Stored hash: $hash<br>";
    echo "Verification result: " . (password_verify($test_password, $hash) ? "SUCCESS" : "FAILED") . "<br>";
    
    // Create new hash for comparison
    $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "<br>New hash generated for same password: $new_hash<br>";
    echo "New hash verification: " . (password_verify($test_password, $new_hash) ? "SUCCESS" : "FAILED") . "<br>";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
} 