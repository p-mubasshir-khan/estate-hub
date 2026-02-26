<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: properties.php');
    exit();
}

$property_id = $_GET['id'];

// Fetch property details
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: properties.php');
    exit();
}

// Fetch all amenities
$stmt = $conn->query("SELECT * FROM amenities ORDER BY name");
$amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch selected amenities for this property
$stmt = $conn->prepare("SELECT amenity_id FROM property_amenities WHERE property_id = ?");
$stmt->execute([$property_id]);
$selected_amenities = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $property_type = $_POST['property_type'];
    $listing_type = $_POST['listing_type'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $square_feet = $_POST['square_feet'];
    $car_parking = isset($_POST['car_parking']) ? 1 : 0;
    $new_amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    // Handle image upload if new image is provided
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "assets/images/properties/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image if exists
            if (!empty($property['image_url']) && file_exists($property['image_url'])) {
                unlink($property['image_url']);
            }
            $image_url = $target_file;
        }
    } else {
        $image_url = $property['image_url'];
    }

    try {
        $conn->beginTransaction();

        // Update property
        $stmt = $conn->prepare("UPDATE properties SET title = ?, description = ?, price = ?, location = ?, property_type = ?, listing_type = ?, bedrooms = ?, bathrooms = ?, square_feet = ?, car_parking = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$title, $description, $price, $location, $property_type, $listing_type, $bedrooms, $bathrooms, $square_feet, $car_parking, $image_url, $property_id]);

        // Update amenities
        // First, remove all existing amenities for this property
        $stmt = $conn->prepare("DELETE FROM property_amenities WHERE property_id = ?");
        $stmt->execute([$property_id]);

        // Then insert new amenities
        if (!empty($new_amenities)) {
            $stmt = $conn->prepare("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
            foreach ($new_amenities as $amenity_id) {
                $stmt->execute([$property_id, $amenity_id]);
            }
        }

        $conn->commit();
        header('Location: properties.php');
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error updating property: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Estate Hub</title>
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
        .current-image {
            max-width: 200px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">Edit Property</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control" id="price" name="price" value="<?php echo $property['price']; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="property_type" class="form-label">Property Type</label>
                    <select class="form-select" id="property_type" name="property_type" required>
                        <option value="">Select Type</option>
                        <option value="house" <?php echo $property['property_type'] === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="apartment" <?php echo $property['property_type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                        <option value="condo" <?php echo $property['property_type'] === 'condo' ? 'selected' : ''; ?>>Condo</option>
                        <option value="villa" <?php echo $property['property_type'] === 'villa' ? 'selected' : ''; ?>>Villa</option>
                        <option value="office" <?php echo $property['property_type'] === 'office' ? 'selected' : ''; ?>>Office</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="listing_type" class="form-label">Listing Type</label>
                    <select class="form-select" id="listing_type" name="listing_type" required>
                        <option value="">Select Type</option>
                        <option value="sale" <?php echo $property['listing_type'] === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo $property['listing_type'] === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="square_feet" class="form-label">Square Feet</label>
                    <input type="number" class="form-control" id="square_feet" name="square_feet" value="<?php echo $property['square_feet']; ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="bedrooms" class="form-label">Bedrooms</label>
                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="<?php echo $property['bedrooms']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="bathrooms" class="form-label">Bathrooms</label>
                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="<?php echo $property['bathrooms']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label d-block">Car Parking</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="car_parking" name="car_parking" <?php echo $property['car_parking'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="car_parking">Available</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="amenities" class="form-label">Amenities</label>
                <select class="form-select" id="amenities" name="amenities[]" multiple required>
                    <?php foreach ($amenities as $amenity): ?>
                        <option value="<?php echo $amenity['id']; ?>" <?php echo in_array($amenity['id'], $selected_amenities) ? 'selected' : ''; ?>>
                            <i class="fas fa-<?php echo $amenity['icon']; ?>"></i> 
                            <?php echo htmlspecialchars($amenity['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($property['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Main Image</label>
                <?php if (!empty($property['image_url'])): ?>
                    <div>
                        <img src="<?php echo htmlspecialchars($property['image_url']); ?>" alt="Current Property Image" class="current-image">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted">Leave empty to keep the current image</small>
            </div>

            <button type="submit" class="btn btn-primary">Update Property</button>
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
                templateSelection: formatAmenity
            });
        });

        function formatAmenity(amenity) {
            if (!amenity.id) return amenity.text;
            return $('<span><i class="fas fa-' + $(amenity.element).find('i').attr('class').split(' ')[1] + ' amenity-icon"></i>' + amenity.text + '</span>');
        }
    </script>
</body>
</html> 