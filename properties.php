<?php
session_start();
require_once 'config/database.php';

// Get search parameters
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$property_type = isset($_GET['property_type']) ? $_GET['property_type'] : '';
$price_sort = isset($_GET['price_sort']) ? $_GET['price_sort'] : '';

// Base query
$query = "SELECT * FROM properties WHERE 1=1";
$params = array();

// Add type filter
switch($type) {
    case 'buy':
        $query .= " AND listing_type = :listing_type";
        $params[':listing_type'] = 'buy';
        $title = "Properties for Sale";
        break;
    case 'rent':
        $query .= " AND listing_type = :listing_type";
        $params[':listing_type'] = 'rent';
        $title = "Properties for Rent";
        break;
    case 'commercial':
        $query .= " AND listing_type = :listing_type";
        $params[':listing_type'] = 'commercial';
        $title = "Commercial Properties";
        break;
    case 'residential':
        $query .= " AND listing_type IN ('buy', 'rent')";
        $title = "Residential Properties";
        break;
    default:
        $title = "All Properties";
}

// Add location filter if provided
if(!empty($location)) {
    $query .= " AND location LIKE :location";
    $params[':location'] = '%' . $location . '%';
}

// Add property type filter
if(!empty($property_type)) {
    $query .= " AND property_type = :property_type";
    $params[':property_type'] = $property_type;
}

// Add price sort
switch($price_sort) {
    case 'low_to_high':
        $query .= " ORDER BY price ASC";
        break;
    case 'high_to_low':
        $query .= " ORDER BY price DESC";
        break;
    default:
        $query .= " ORDER BY created_at DESC";
}

try {
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Query error: " . $e->getMessage());
    $properties = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Estate Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4"><?php echo $title; ?></h1>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form action="" method="GET" class="card p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select class="form-select" name="location">
                                <option value="">All Locations</option>
                                <option value="Guntur" <?php echo $location == 'Guntur' ? 'selected' : ''; ?>>Guntur</option>
                                <option value="Vijayawada" <?php echo $location == 'Vijayawada' ? 'selected' : ''; ?>>Vijayawada</option>
                                <option value="Mangalagiri" <?php echo $location == 'Mangalagiri' ? 'selected' : ''; ?>>Mangalagiri</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="property_type">
                                <option value="">All Property Types</option>
                                <option value="house" <?php echo $property_type == 'house' ? 'selected' : ''; ?>>House</option>
                                <option value="apartment" <?php echo $property_type == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                <option value="villa" <?php echo $property_type == 'villa' ? 'selected' : ''; ?>>Villa</option>
                                <option value="plot" <?php echo $property_type == 'plot' ? 'selected' : ''; ?>>Plot</option>
                                <option value="commercial" <?php echo $property_type == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="farmhouse" <?php echo $property_type == 'farmhouse' ? 'selected' : ''; ?>>Farmhouse</option>
                                <option value="studio" <?php echo $property_type == 'studio' ? 'selected' : ''; ?>>Studio</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="price_sort">
                                <option value="">Sort by Price</option>
                                <option value="low_to_high" <?php echo $price_sort == 'low_to_high' ? 'selected' : ''; ?>>Low to High</option>
                                <option value="high_to_low" <?php echo $price_sort == 'high_to_low' ? 'selected' : ''; ?>>High to Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Properties Grid -->
        <div class="row">
            <?php
            if (!empty($properties)) {
                foreach($properties as $property) {
                    // Format price based on listing type
                    if ($property['listing_type'] == 'rent') {
                        $price = "₹" . number_format($property['price']) . "/month";
                    } else {
                        $price = "₹" . number_format($property['price']);
                    }
            ?>
                <div class="col-md-4 mb-4">
                    <a href="property.php?id=<?php echo $property['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 property-card">
                            <img src="<?php echo htmlspecialchars($property['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title text-dark"><?php echo htmlspecialchars($property['title']); ?></h5>
                                <p class="card-text text-primary fw-bold"><?php echo $price; ?></p>
                                <p class="card-text text-dark"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                                <?php if($property['bedrooms'] > 0) { ?>
                                <p class="card-text text-dark">
                                    <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Beds
                                    <i class="fas fa-bath ms-3"></i> <?php echo $property['bathrooms']; ?> Baths
                                </p>
                                <?php } ?>
                                <p class="card-text text-dark"><i class="fas fa-ruler-combined"></i> <?php echo number_format($property['square_feet']); ?> sq.ft</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No properties found matching your criteria.</p></div>';
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    .property-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .card-img-top {
        height: 200px;
        object-fit: cover;
    }
    </style>
</body>
</html> 