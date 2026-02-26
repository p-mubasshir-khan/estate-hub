<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all amenities
$stmt = $conn->query("SELECT * FROM amenities ORDER BY name");
$amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $location = $_POST['location'] ?? '';
    $property_type = $_POST['property_type'] ?? '';
    $listing_type = $_POST['listing_type'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? '';
    $bathrooms = $_POST['bathrooms'] ?? '';
    $square_feet = $_POST['square_feet'] ?? '';
    $car_parking = isset($_POST['car_parking']) ? 1 : 0;
    $selected_amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "assets/images/properties/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Create a unique directory for this property's images
        $property_dir = uniqid('property_');
        $full_property_dir = $target_dir . $property_dir;
        mkdir($full_property_dir, 0777, true);

        // Handle main image
        $main_file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $main_file_name = 'main.' . $main_file_extension;
        $main_target_file = $full_property_dir . '/' . $main_file_name;
        $main_image_url = $target_dir . $property_dir . '/' . $main_file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $main_target_file)) {
            $image_url = $main_image_url;
        }
    }

    if (empty($title) || empty($description) || empty($price) || empty($location) || empty($image_url)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $conn->beginTransaction();

            // Insert property
            $stmt = $conn->prepare("INSERT INTO properties (title, description, price, location, property_type, listing_type, bedrooms, bathrooms, square_feet, car_parking, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $price, $location, $property_type, $listing_type, $bedrooms, $bathrooms, $square_feet, $car_parking, $image_url]);
            
            $property_id = $conn->lastInsertId();

            // Handle additional images
            if (!empty($_FILES['additional_images']['name'][0])) {
                $stmt = $conn->prepare("INSERT INTO property_images (property_id, image_url) VALUES (?, ?)");
                
                foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_extension = strtolower(pathinfo($_FILES['additional_images']['name'][$key], PATHINFO_EXTENSION));
                        $file_name = 'additional_' . ($key + 1) . '.' . $file_extension;
                        $target_file = $full_property_dir . '/' . $file_name;
                        $image_url = $target_dir . $property_dir . '/' . $file_name;

                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $stmt->execute([$property_id, $image_url]);
                        }
                    }
                }
            }

            // Insert property amenities
            if (!empty($selected_amenities)) {
                $stmt = $conn->prepare("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
                foreach ($selected_amenities as $amenity_id) {
                    $stmt->execute([$property_id, $amenity_id]);
                }
            }

            $conn->commit();
            header('Location: properties.php');
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = 'Error adding property: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Estate Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: 38px;
        }
        .amenity-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Add New Property</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location" required>
                        <option value="">Select Location</option>
                        <option value="Guntur">Guntur</option>
                        <option value="Vijayawada">Vijayawada</option>
                        <option value="Mangalagiri">Mangalagiri</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="property_type" class="form-label">Property Type</label>
                    <select class="form-select" id="property_type" name="property_type" required>
                        <option value="">Select Type</option>
                        <option value="house">House</option>
                        <option value="apartment">Apartment</option>
                        <option value="villa">Villa</option>
                        <option value="plot">Plot</option>
                        <option value="commercial">Commercial</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="listing_type" class="form-label">Listing Type</label>
                    <select class="form-select" id="listing_type" name="listing_type" required>
                        <option value="">Select Type</option>
                        <option value="buy">For Sale</option>
                        <option value="rent">For Rent</option>
                        <option value="commercial">Commercial</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="square_feet" class="form-label">Square Feet</label>
                    <input type="number" class="form-control" id="square_feet" name="square_feet" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="bedrooms" class="form-label">Bedrooms</label>
                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="bathrooms" class="form-label">Bathrooms</label>
                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label d-block">Car Parking</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="car_parking" name="car_parking">
                        <label class="form-check-label" for="car_parking">Available</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="amenities" class="form-label">Amenities</label>
                <select class="form-select" id="amenities" name="amenities[]" multiple>
                    <?php foreach ($amenities as $amenity): ?>
                        <option value="<?php echo $amenity['id']; ?>" data-icon="<?php echo $amenity['icon']; ?>">
                            <?php echo htmlspecialchars($amenity['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Property Images</label>
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Main Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Additional Images (Optional)</label>
                        <input type="file" class="form-control" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                        <small class="text-muted">You can select multiple images at once</small>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Add Property</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#amenities').select2({
                placeholder: 'Select amenities',
                templateResult: formatAmenity,
                templateSelection: formatAmenity,
                escapeMarkup: function(m) { return m; }
            });
        });

        function formatAmenity(amenity) {
            if (!amenity.id) return amenity.text;
            var icon = $(amenity.element).data('icon');
            return '<span><i class="fas fa-' + icon + ' amenity-icon"></i> ' + amenity.text + '</span>';
        }
    </script>
</body>
</html> 