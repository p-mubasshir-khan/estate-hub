<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

// Fetch all amenities
$stmt = $conn->query("SELECT * FROM amenities");
$amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Validate required fields
        $required_fields = ['title', 'description', 'price', 'location', 'property_type', 'listing_type'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        // Handle main image upload first
        if (!isset($_FILES["main_image"]) || $_FILES["main_image"]["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a main image");
        }

        $main_image = $_FILES["main_image"];
        $property_dir = "../assets/images/properties/temp_" . time();
        if (!file_exists($property_dir)) {
            mkdir($property_dir, 0777, true);
        }

        $main_image_name = "main_" . time() . "_" . basename($main_image["name"]);
        $main_image_path = $property_dir . "/" . $main_image_name;

        if (!move_uploaded_file($main_image["tmp_name"], $main_image_path)) {
            throw new Exception("Failed to upload main image");
        }

        // Insert property with all fields including image_url
        $stmt = $conn->prepare("INSERT INTO properties (title, description, price, location, bedrooms, bathrooms, 
                              square_feet, property_type, listing_type, featured, car_parking, total_floors, floor_number, image_url) 
                              VALUES (:title, :description, :price, :location, :bedrooms, :bathrooms, 
                              :square_feet, :property_type, :listing_type, :featured, :car_parking, :total_floors, :floor_number, :image_url)");
        
        $result = $stmt->execute([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'location' => $_POST['location'],
            'bedrooms' => $_POST['bedrooms'] ?: null,
            'bathrooms' => $_POST['bathrooms'] ?: null,
            'square_feet' => $_POST['square_feet'] ?: null,
            'property_type' => $_POST['property_type'],
            'listing_type' => $_POST['listing_type'],
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'car_parking' => isset($_POST['car_parking']) ? 1 : 0,
            'total_floors' => $_POST['total_floors'] ?: null,
            'floor_number' => $_POST['floor_number'] ?: null,
            'image_url' => "assets/images/properties/property_placeholder.jpg"
        ]);

        if (!$result) {
            throw new Exception("Failed to insert property");
        }

        $property_id = $conn->lastInsertId();

        // Create final property-specific directory
        $final_property_dir = "../assets/images/properties/property_" . $property_id;
        if (!file_exists($final_property_dir)) {
            mkdir($final_property_dir, 0777, true);
        }

        // Move main image to final location
        $final_main_image_path = $final_property_dir . "/" . $main_image_name;
        $final_main_image_url = "assets/images/properties/property_" . $property_id . "/" . $main_image_name;
        
        rename($main_image_path, $final_main_image_path);
        rmdir($property_dir); // Remove temporary directory

        // Update property with final image URL
        $stmt = $conn->prepare("UPDATE properties SET image_url = ? WHERE id = ?");
        $stmt->execute([$final_main_image_url, $property_id]);

        // Handle additional images
        if (!empty($_FILES['additional_images']['name'][0])) {
            $stmt = $conn->prepare("INSERT INTO property_images (property_id, image_url) VALUES (?, ?)");
            
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $image_name = "additional_" . time() . "_" . $key . "_" . basename($_FILES['additional_images']['name'][$key]);
                    $image_path = $final_property_dir . "/" . $image_name;
                    $image_url = "assets/images/properties/property_" . $property_id . "/" . $image_name;

                    if (move_uploaded_file($tmp_name, $image_path)) {
                        $stmt->execute([$property_id, $image_url]);
                    }
                }
            }
        }

        // Insert selected amenities
        if (!empty($_POST['amenities'])) {
            $stmt = $conn->prepare("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
            foreach ($_POST['amenities'] as $amenity_id) {
                $stmt->execute([$property_id, $amenity_id]);
            }
        }

        $conn->commit();
        $success_message = "Property added successfully!";

    } catch (Exception $e) {
        $conn->rollBack();
        $error_message = "Error adding property: " . $e->getMessage();
        
        // Clean up directories if there was an error
        if (isset($property_dir) && file_exists($property_dir)) {
            array_map('unlink', glob("$property_dir/*.*"));
            rmdir($property_dir);
        }
        if (isset($final_property_dir) && file_exists($final_property_dir)) {
            array_map('unlink', glob("$final_property_dir/*.*"));
            rmdir($final_property_dir);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Estate Hub Admin</title>
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
        .image-preview {
            max-width: 200px;
            margin: 10px 0;
        }
        #imagePreviewContainer img {
            max-width: 200px;
            margin: 10px;
        }
        .form-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .form-check-label i {
            font-size: 1.1em;
            color: #0d6efd;
        }
        .form-check {
            padding: 10px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .form-check:hover {
            background-color: #f8f9fa;
        }
        .form-check-input:checked + .form-check-label i {
            color: #0b5ed7;
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
                    <h2>Add New Property</h2>
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
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <select class="form-select" name="location" required>
                                        <option value="">Select Location</option>
                                        <option value="Guntur">Guntur</option>
                                        <option value="Vijayawada">Vijayawada</option>
                                        <option value="Mangalagiri">Mangalagiri</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (₹)</label>
                                    <input type="number" class="form-control" name="price" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Property Type</label>
                                    <select class="form-select" name="property_type" required>
                                        <option value="">Select Type</option>
                                        <option value="house">House</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="villa">Villa</option>
                                        <option value="plot">Plot</option>
                                        <option value="commercial">Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Listing Type</label>
                                    <select class="form-select" name="listing_type" required>
                                        <option value="">Select Listing Type</option>
                                        <option value="buy">For Sale</option>
                                        <option value="rent">For Rent</option>
                                        <option value="commercial">Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bedrooms</label>
                                    <input type="number" class="form-control" name="bedrooms" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Bathrooms</label>
                                    <input type="number" class="form-control" name="bathrooms" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Square Feet</label>
                                    <input type="number" class="form-control" name="square_feet" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label d-block">Car Parking</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="car_parking" name="car_parking">
                                        <label class="form-check-label" for="car_parking">Available</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label d-block">Featured Property</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                        <label class="form-check-label" for="featured">
                                            <i class="fas fa-star text-warning me-1"></i> Mark as Featured
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Total Floors</label>
                                    <input type="number" class="form-control" name="total_floors" min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Floor Number</label>
                                    <input type="number" class="form-control" name="floor_number" min="0">
                                    <small class="text-muted">0 for ground floor</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Amenities</label>
                                    <div class="row g-3">
                                        <?php
                                        // Fetch amenities from database
                                        $stmt = $conn->query("SELECT * FROM amenities ORDER BY name");
                                        $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($amenities as $amenity): ?>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="amenities[]" 
                                                           value="<?php echo $amenity['id']; ?>" 
                                                           id="amenity_<?php echo $amenity['id']; ?>">
                                                    <label class="form-check-label" for="amenity_<?php echo $amenity['id']; ?>">
                                                        <i class="fas fa-<?php echo htmlspecialchars($amenity['icon']); ?>"></i>
                                                        <?php echo htmlspecialchars($amenity['name']); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="4" required></textarea>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Main Property Image</label>
                                    <input type="file" class="form-control" name="main_image" accept="image/*" required onchange="previewMainImage(this)">
                                    <div id="mainImagePreview"></div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Additional Images (Multiple)</label>
                                    <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple onchange="previewAdditionalImages(this)">
                                    <div id="additionalImagesPreview" class="d-flex flex-wrap"></div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Property
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewMainImage(input) {
            const preview = document.getElementById('mainImagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('image-preview');
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewAdditionalImages(input) {
            const preview = document.getElementById('additionalImagesPreview');
            preview.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('image-preview');
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</body>
</html> 