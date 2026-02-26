<?php
require_once '../config/database.php';

try {
    // Check if admin_users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'admin_users'");
    $tableExists = $stmt->fetch();
    
    echo "Admin users table exists: " . ($tableExists ? "Yes" : "No") . "<br>";
    
    if ($tableExists) {
        // Check for admin user
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "Admin user found:<br>";
            echo "Username: " . $admin['username'] . "<br>";
            echo "Email: " . $admin['email'] . "<br>";
            
            // Test password verification
            $test_password = 'admin123';
            $stored_hash = $admin['password'];
            $verification = password_verify($test_password, $stored_hash);
            
            echo "<br>Password verification test:<br>";
            echo "Test password: " . $test_password . "<br>";
            echo "Stored hash: " . $stored_hash . "<br>";
            echo "Password verification result: " . ($verification ? "Success" : "Failed") . "<br>";
        } else {
            echo "Admin user not found<br>";
        }
        
        // Show all admin users
        echo "<br>All admin users:<br>";
        $stmt = $conn->query("SELECT id, username, email FROM admin_users");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: " . $row['id'] . ", Username: " . $row['username'] . ", Email: " . $row['email'] . "<br>";
        }
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?> 