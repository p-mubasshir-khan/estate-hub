<?php
require_once 'config/database.php';

try {
    // Check if we can connect to the database
    echo "Database connection successful!<br>";
    
    // Check if properties table exists and has data
    $stmt = $conn->query("SELECT COUNT(*) FROM properties");
    $count = $stmt->fetchColumn();
    echo "Number of properties in database: " . $count . "<br>";
    
    // Check property types
    $stmt = $conn->query("SELECT DISTINCT property_type FROM properties");
    $property_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Property types in database: " . implode(", ", $property_types) . "<br>";
    
    // Check listing types
    $stmt = $conn->query("SELECT DISTINCT listing_type FROM properties");
    $listing_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Listing types in database: " . implode(", ", $listing_types) . "<br>";
    
    // Check locations
    $stmt = $conn->query("SELECT DISTINCT location FROM properties");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Locations in database: " . implode(", ", $locations) . "<br>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 