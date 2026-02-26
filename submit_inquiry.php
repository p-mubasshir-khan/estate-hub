<?php
session_start();
require_once 'config/database.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate inputs
    $errors = [];
    
    if (empty($property_id)) {
        $errors[] = "Invalid property reference.";
    }
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    // If no errors, proceed with saving the inquiry
    if (empty($errors)) {
        try {
            // First check if the property exists
            $stmt = $conn->prepare("SELECT id FROM properties WHERE id = ?");
            $stmt->execute([$property_id]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Property not found.");
            }

            // Insert the inquiry
            $stmt = $conn->prepare("INSERT INTO inquiries (property_id, name, email, phone, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$property_id, $name, $email, $phone, $message]);

            // Set success message
            $_SESSION['success_message'] = "Thank you for your inquiry! We will contact you soon.";
            
            // Redirect back to the property page
            header("Location: property.php?id=" . $property_id);
            exit();

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Sorry, there was an error submitting your inquiry. Please try again.";
            header("Location: property.php?id=" . $property_id);
            exit();
        }
    } else {
        // If there are validation errors, store them in session and redirect back
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: property.php?id=" . $property_id);
        exit();
    }
} else {
    // If someone tries to access this file directly without POST data
    header("Location: properties.php");
    exit();
}
?> 