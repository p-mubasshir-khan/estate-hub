<?php
require_once '../config/database.php';

try {
    // Create amenities table
    $conn->exec("CREATE TABLE IF NOT EXISTS `amenities` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `icon` varchar(50) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Create property_amenities table
    $conn->exec("CREATE TABLE IF NOT EXISTS `property_amenities` (
        `property_id` INT NOT NULL,
        `amenity_id` INT NOT NULL,
        PRIMARY KEY (`property_id`, `amenity_id`),
        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`amenity_id`) REFERENCES `amenities`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert default amenities
    $conn->exec("INSERT INTO `amenities` (`name`, `icon`) VALUES
        ('Swimming Pool', 'swimming-pool'),
        ('Fitness Center', 'dumbbell'),
        ('Parking', 'parking'),
        ('High-Speed Internet', 'wifi'),
        ('Security System', 'shield-alt'),
        ('Garden', 'leaf')");

    echo "Tables created and populated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 