<?php
require_once 'config/database.php';

// New amenities to add
$new_amenities = [
    ['name' => 'WiFi', 'icon' => 'wifi'],
    ['name' => 'Gym', 'icon' => 'dumbbell'],
    ['name' => 'Swimming Pool', 'icon' => 'swimming-pool'],
    ['name' => '24/7 Electricity & Water', 'icon' => 'bolt']
];

try {
    // First, delete existing amenities to avoid duplicates
    $conn->exec("TRUNCATE TABLE property_amenities");
    $conn->exec("TRUNCATE TABLE amenities");
    
    // Insert new amenities
    $stmt = $conn->prepare("INSERT INTO amenities (name, icon) VALUES (?, ?)");
    foreach ($new_amenities as $amenity) {
        $stmt->execute([$amenity['name'], $amenity['icon']]);
    }
    
    echo "Amenities updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 