<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

// Get property ID from URL
$property_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        $update_fields = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'location' => $_POST['location'],
            'bedrooms' => $_POST['bedrooms'],
            'bathrooms' => $_POST['bathrooms'],
            'square_feet' => $_POST['square_feet'],
            'property_type' => $_POST['property_type'],
            'listing_type' => $_POST['listing_type'],
            'car_parking' => isset($_POST['car_parking']) ? 1 : 0,
            'total_floors' => $_POST['total_floors'],
            'floor_number' => $_POST['floor_number']
        ];

        // Handle main image upload if new image is provided
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../assets/images/properties/";
            $image_file = $_FILES["image"]["name"];
            $image_path = $target_dir . basename($image_file);
            $image_url = "assets/images/properties/" . basename($image_file);
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
                $update_fields['image_url'] = $image_url;
            }
        }

        // Build update query
        $sql_parts = [];
        $params = [];
        foreach($update_fields as $key => $value) {
            $sql_parts[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':id'] = $property_id;

        $sql = "UPDATE properties SET " . implode(', ', $sql_parts) . " WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);

        // Handle additional images upload
        if (!empty($_FILES['additional_images']['name'][0])) {
            $target_dir = "../assets/images/properties/" . $property_id . "/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['additional_images']['name'][$key];
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_file_name = uniqid() . '.' . $file_extension;
                    $target_path = $target_dir . $new_file_name;
                    $image_url = "assets/images/properties/" . $property_id . "/" . $new_file_name;

                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $stmt = $conn->prepare("INSERT INTO property_images (property_id, image_url) VALUES (?, ?)");
                        $stmt->execute([$property_id, $image_url]);
                    }
                }
            }
        }

        // Handle image deletion
        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $image_id) {
                // Get image path before deletion
                $stmt = $conn->prepare("SELECT image_url FROM property_images WHERE id = ? AND property_id = ?");
                $stmt->execute([$image_id, $property_id]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($image) {
                    // Delete file from server
                    $file_path = "../" . $image['image_url'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }

                    // Delete from database
                    $stmt = $conn->prepare("DELETE FROM property_images WHERE id = ? AND property_id = ?");
                    $stmt->execute([$image_id, $property_id]);
                }
            }
        }

        $conn->commit();
        $success_message = "Property updated successfully!";
    } catch(PDOException $e) {
        $conn->rollBack();
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Fetch property data
try {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        header('Location: properties.php');
        exit();
    }

    // Fetch additional images
    $stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $additional_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching property: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Estate Hub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar a.active {
            background-color: #0d6efd;
        }
        .current-image {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .additional-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 5px;
            border-radius: 5px;
        }
        .image-container {
            position: relative;
            display: inline-block;
        }
        .delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            cursor: pointer;
        }
        .delete-image:hover {
            background: rgba(255, 0, 0, 0.9);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 px-0 sidebar">
                <div class="p-3 text-white">
                    <h5>Estate Hub Admin</h5>
                </div>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="properties.php" class="active">
                    <i class="fas fa-home me-2"></i> Properties
                </a>
                <a href="users.php">
                    <i class="fas fa-users me-2"></i> Users
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit Property</h2>
                    <a href="properties.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Properties
                    </a>
                </div>

                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <select class="form-select" name="location" required>
                                        <option value="">Select Location</option>
                                        <option value="Guntur" <?php echo $property['location'] == 'Guntur' ? 'selected' : ''; ?>>Guntur</option>
                                        <option value="Vijayawada" <?php echo $property['location'] == 'Vijayawada' ? 'selected' : ''; ?>>Vijayawada</option>
                                        <option value="Mangalagiri" <?php echo $property['location'] == 'Mangalagiri' ? 'selected' : ''; ?>>Mangalagiri</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (₹)</label>
                                    <input type="number" class="form-control" name="price" value="<?php echo $property['price']; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Property Type</label>
                                    <select class="form-select" name="property_type" required>
                                        <option value="">Select Type</option>
                                        <option value="house" <?php echo $property['property_type'] == 'house' ? 'selected' : ''; ?>>House</option>
                                        <option value="apartment" <?php echo $property['property_type'] == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                        <option value="villa" <?php echo $property['property_type'] == 'villa' ? 'selected' : ''; ?>>Villa</option>
                                        <option value="plot" <?php echo $property['property_type'] == 'plot' ? 'selected' : ''; ?>>Plot</option>
                                        <option value="commercial" <?php echo $property['property_type'] == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Listing Type</label>
                                    <select class="form-select" name="listing_type" required>
                                        <option value="">Select Listing Type</option>
                                        <option value="buy" <?php echo $property['listing_type'] == 'buy' ? 'selected' : ''; ?>>For Sale</option>
                                        <option value="rent" <?php echo $property['listing_type'] == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                                        <option value="commercial" <?php echo $property['listing_type'] == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bedrooms</label>
                                    <input type="number" class="form-control" name="bedrooms" value="<?php echo $property['bedrooms']; ?>" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bathrooms</label>
                                    <input type="number" class="form-control" name="bathrooms" value="<?php echo $property['bathrooms']; ?>" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Square Feet</label>
                                    <input type="number" class="form-control" name="square_feet" value="<?php echo $property['square_feet']; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Total Floors</label>
                                    <input type="number" class="form-control" name="total_floors" value="<?php echo $property['total_floors']; ?>" min="1" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Floor Number</label>
                                    <input type="number" class="form-control" name="floor_number" value="<?php echo $property['floor_number']; ?>" min="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" name="car_parking" id="car_parking" <?php echo $property['car_parking'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="car_parking">Car Parking Available</label>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Main Image</label>
                                    <?php if($property['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($property['image_url']); ?>" class="current-image d-block mb-2">
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Additional Images</label>
                                    <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple>
                                    <small class="text-muted">You can select multiple images at once</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Current Additional Images</label>
                                    <div class="d-flex flex-wrap">
                                        <?php foreach($additional_images as $image): ?>
                                            <div class="image-container">
                                                <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" class="additional-image">
                                                <button type="button" class="delete-image" onclick="deleteImage(<?php echo $image['id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>" style="display: none;" id="delete_<?php echo $image['id']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Update Property</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteImage(imageId) {
            if (confirm('Are you sure you want to delete this image?')) {
                document.getElementById('delete_' + imageId).checked = true;
            }
        }
    </script>
</body>
</html> 