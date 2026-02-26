<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

if(isset($_GET['id'])) {
    $property_id = $_GET['id'];
    
    try {
        $conn->beginTransaction();

        // Get property image URLs before deletion
        $stmt = $conn->prepare("SELECT image_url FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get additional images
        $stmt = $conn->prepare("SELECT image_url FROM property_images WHERE property_id = ?");
        $stmt->execute([$property_id]);
        $additional_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Delete property amenities
        $stmt = $conn->prepare("DELETE FROM property_amenities WHERE property_id = ?");
        $stmt->execute([$property_id]);

        // Delete property images
        $stmt = $conn->prepare("DELETE FROM property_images WHERE property_id = ?");
        $stmt->execute([$property_id]);

        // Delete the property
        $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);

        // Delete physical files
        if($property) {
            $main_image_path = "../" . $property['image_url'];
            if(file_exists($main_image_path)) {
                unlink($main_image_path);
            }

            // Delete property directory
            $property_dir = dirname($main_image_path);
            if(is_dir($property_dir)) {
                array_map('unlink', glob("$property_dir/*.*"));
                rmdir($property_dir);
            }
        }

        // Delete additional images
        foreach($additional_images as $image_url) {
            $image_path = "../" . $image_url;
            if(file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = "Property deleted successfully!";
    } catch(Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Error deleting property: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid property ID";
}

header('Location: properties.php');
exit();
?> 