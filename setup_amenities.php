<?php
require_once 'config/database.php';

// Default amenities with their icons
$default_amenities = [
    ['name' => '24/7 Water Supply', 'icon' => 'water'],
    ['name' => 'WiFi', 'icon' => 'wifi'],
    ['name' => 'Elevator', 'icon' => 'elevator'],
    ['name' => 'Swimming Pool', 'icon' => 'swimming-pool'],
    ['name' => 'Gym', 'icon' => 'dumbbell'],
    ['name' => 'Security', 'icon' => 'shield-alt'],
    ['name' => 'Power Backup', 'icon' => 'bolt'],
    ['name' => 'Parking', 'icon' => 'parking'],
    ['name' => 'Garden', 'icon' => 'tree'],
    ['name' => 'CCTV', 'icon' => 'video']
];

try {
    // First, check if amenities table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'amenities'");
    if ($stmt->rowCount() == 0) {
        // Create amenities table if it doesn't exist
        $conn->exec("CREATE TABLE IF NOT EXISTS amenities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "Amenities table created successfully.<br>";
    }

    // Check if amenities table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM amenities");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Insert default amenities
        $stmt = $conn->prepare("INSERT INTO amenities (name, icon) VALUES (?, ?)");
        foreach ($default_amenities as $amenity) {
            $stmt->execute([$amenity['name'], $amenity['icon']]);
        }
        echo "Default amenities inserted successfully.<br>";
    } else {
        echo "Amenities already exist in the database.<br>";
    }

    // Create property_amenities table if it doesn't exist
    $stmt = $conn->query("SHOW TABLES LIKE 'property_amenities'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("CREATE TABLE IF NOT EXISTS property_amenities (
            property_id INT,
            amenity_id INT,
            PRIMARY KEY (property_id, amenity_id),
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
        )");
        echo "Property amenities table created successfully.<br>";
    }

    echo "Setup completed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 