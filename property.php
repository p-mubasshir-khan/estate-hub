<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: properties.php');
    exit();
}

$property_id = $_GET['id'];

// Get property details
$stmt = $conn->prepare("SELECT p.*, GROUP_CONCAT(a.name) as amenity_names, GROUP_CONCAT(a.icon) as amenity_icons 
                       FROM properties p 
                       LEFT JOIN property_amenities pa ON p.id = pa.property_id 
                       LEFT JOIN amenities a ON pa.amenity_id = a.id 
                       WHERE p.id = ?
                       GROUP BY p.id");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: properties.php');
    exit();
}

// Process amenities
$amenity_names = $property['amenity_names'] ? explode(',', $property['amenity_names']) : [];
$amenity_icons = $property['amenity_icons'] ? explode(',', $property['amenity_icons']) : [];
$amenities = array_map(function($name, $icon) {
    return ['name' => $name, 'icon' => $icon];
}, $amenity_names, $amenity_icons);

// Get property images
$images = [];
try {
    $stmt = $conn->prepare("SELECT image_url FROM property_images WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $additional_images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Always include the main property image
    $images[] = $property['image_url'];
    
    // Add additional images if they exist
    if (!empty($additional_images)) {
        $images = array_merge($images, $additional_images);
    }
} catch (PDOException $e) {
    // If there's an error (like table doesn't exist), just use the main image
    $images = [$property['image_url']];
}

// Format price based on listing type
if ($property['listing_type'] == 'rent') {
    $price = "₹" . number_format($property['price']) . "/month";
} else {
    $price = "₹" . number_format($property['price']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - Estate Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .carousel-item img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 8px;
    }

    /* Carousel container relative positioning */
    #propertyCarousel {
        position: relative;
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 50px;
        height: 50px;
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        opacity: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }

    .carousel-control-prev {
        left: 30px;
    }

    .carousel-control-next {
        right: 30px;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 24px;
        height: 24px;
        background-size: 100%;
        filter: brightness(0);
        opacity: 0.8;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        background-color: #fff;
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .carousel-control-prev:hover .carousel-control-prev-icon,
    .carousel-control-next:hover .carousel-control-next-icon {
        opacity: 1;
    }

    .carousel-indicators {
        bottom: 20px;
        margin-bottom: 0;
    }

    .carousel-indicators button {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin: 0 5px;
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.2);
    }

    .carousel-indicators button.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .property-details {
        border: 1px solid #e9ecef;
    }

    .property-details i {
        color: #0d6efd;
        width: 25px;
    }

    .property-details p {
        margin-bottom: 10px;
    }

    .property-price {
        font-size: 1.75rem;
        font-weight: 600;
        color: #0d6efd;
    }

    .property-location {
        color: #6c757d;
        font-size: 1.1rem;
    }

    .property-description {
        line-height: 1.6;
        color: #4a5568;
    }

    .amenities .fas {
        width: 20px;
    }

    .card {
        border: 1px solid #e9ecef;
    }

    .form-control {
        padding: 0.75rem;
    }

    .btn-primary {
        padding: 0.75rem;
        font-weight: 500;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <!-- Left Column - Image Carousel and Details -->
            <div class="col-md-8">
                <!-- Image Carousel -->
                <div id="propertyCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach($images as $index => $image): ?>
                            <button type="button" 
                                    data-bs-target="#propertyCarousel" 
                                    data-bs-slide-to="<?php echo $index; ?>" 
                                    <?php echo $index === 0 ? 'class="active"' : ''; ?>
                                    aria-label="Slide <?php echo $index + 1; ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach($images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                     class="d-block w-100" 
                                     alt="Property Image <?php echo $index + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if(count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Property Details -->
                <div class="property-details bg-white p-4 rounded shadow-sm">
                    <h1 class="h2 mb-3"><?php echo htmlspecialchars($property['title']); ?></h1>
                    <p class="property-location mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    <p class="property-price mb-4"><?php echo $price; ?></p>

                    <div class="row mb-4">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bed text-primary me-2"></i>
                                <span><?php echo $property['bedrooms']; ?> Beds</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bath text-primary me-2"></i>
                                <span><?php echo $property['bathrooms']; ?> Baths</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-combined text-primary me-2"></i>
                                <span><?php echo number_format($property['square_feet']); ?> sqft</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-car text-primary me-2"></i>
                                <span><?php echo $property['car_parking'] ? 'Yes' : 'No'; ?></span>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-3">Description</h4>
                    <p class="property-description mb-4"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>

                    <?php if (!empty($amenities)): ?>
                    <h4 class="mb-3">Amenities</h4>
                    <div class="amenities mb-4">
                        <div class="row g-3">
                            <?php foreach($amenities as $amenity): ?>
                                <div class="col-6 col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-<?php echo htmlspecialchars($amenity['icon']); ?> text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($amenity['name']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Contact Form -->
            <div class="col-md-4">
                <div class="card shadow-sm position-sticky" style="top: 2rem;">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Interested in this property?</h4>
                        
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="submit_inquiry.php" method="POST">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" name="phone" placeholder="Your Phone" required>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send Inquiry</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 