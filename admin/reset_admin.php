<?php
require_once '../config/database.php';

try {
    // Create a new password hash for 'admin123'
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // First try to update existing admin user
    $stmt = $conn->prepare("UPDATE admin_users SET password = :password WHERE username = 'admin'");
    $result = $stmt->execute(['password' => $hash]);

    if ($stmt->rowCount() > 0) {
        echo "Admin password updated successfully!<br>";
    } else {
        // If no update (user doesn't exist), insert new admin user
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email) VALUES ('admin', :password, 'admin@estatehub.com')");
        $result = $stmt->execute(['password' => $hash]);
        echo "New admin user created successfully!<br>";
    }

    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<br>You can now <a href='login.php'>login here</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 