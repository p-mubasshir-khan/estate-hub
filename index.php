<?php
session_start();
require_once 'config/database.php';

// Fetch featured properties
try {
    // Check if properties table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'properties'");
    $tables = $stmt->fetchAll();
    
    if (!empty($tables)) {
        // Use PDO to fetch featured properties
        $stmt = $conn->query("SELECT * FROM properties WHERE featured = 1 LIMIT 6");
        $featured_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $featured_properties = [];
    }
} catch (PDOException $e) {
    $featured_properties = [];
    // Log error for admin
    error_log("Database error: " . $e->getMessage());
}

// Function to convert USD to INR (assuming 1 USD = 83 INR)
function convertToINR($usd) {
    return $usd * 83;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estate Hub - Find Your Dream Property</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background-color: rgba(255, 248, 248, 0.8);
            padding: 80px 0;
            position: relative;
            z-index: 1;
        }
        .hero-section h1, .hero-section p {
            color: #ffffff !important;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgb(0, 0, 0);
        }
        .hero-section h1 {
            font-size: 8rem;
            font-weight: bold;
            margin-bottom: 20px;
            opacity: 1 !important;
        }
        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 30px;
            opacity: 1 !important;
        }
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                <p style="font-size: 60px;"><b>Find Your Dream Property</b></p>
                    <p>Discover the perfect home from our extensive collection of properties</p>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="assets/images/index/bg1.jpg" alt="Beautiful Property" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="search-bar">
            <form action="properties.php" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="location" placeholder="Location">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">Property Type</option>
                            <option value="house">House</option>
                            <option value="apartment">Apartment</option>
                            <option value="condo">Condo</option>
                            <option value="villa">Villa</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="min_price" placeholder="Min Price">
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="max_price" placeholder="Max Price">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search Properties
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <section class="featured-properties mt-5">
            <h2 class="section-title">Featured Properties</h2>
            <div class="row">
                <?php foreach ($featured_properties as $property): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($property['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($property['description']), 0, 100) . '...'; ?></p>
                            <div class="property-features">
                                <span class="feature-item">
                                    <i class="fas fa-bed"></i> <?php echo htmlspecialchars($property['bedrooms']); ?>
                                </span>
                                <span class="feature-item">
                                    <i class="fas fa-bath"></i> <?php echo htmlspecialchars($property['bathrooms']); ?>
                                </span>
                                <span class="feature-item">
                                    <i class="fas fa-ruler-combined"></i> <?php echo htmlspecialchars($property['square_feet']); ?> sqft
                                </span>
                            </div>
                            <p class="property-price">₹<?php echo number_format($property['price']); ?></p>
                            <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Browse by Category Section -->
        <section class="category-section py-5">
            <div class="container">
                <h2 class="text-center mb-5">Browse by Category</h2>
                <div class="row justify-content-center">
                    <!-- Buy Category -->
                    <div class="col-md-3 mb-4">
                        <div class="card category-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-home text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3>Buy</h3>
                            <p class="text-muted">Find your perfect home to buy</p>
                            <a href="properties.php?type=buy" class="btn btn-outline-primary mt-3">View Listings</a>
                        </div>
                    </div>
                    
                    <!-- Rent Category -->
                    <div class="col-md-3 mb-4">
                        <div class="card category-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-key text-success" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3>Rent</h3>
                            <p class="text-muted">Browse rental properties</p>
                            <a href="properties.php?type=rent" class="btn btn-outline-success mt-3">View Listings</a>
                        </div>
                    </div>
                    
                    <!-- Commercial Category -->
                    <div class="col-md-3 mb-4">
                        <div class="card category-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-building text-info" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3>Commercial</h3>
                            <p class="text-muted">Commercial real estate</p>
                            <a href="properties.php?type=commercial" class="btn btn-outline-info mt-3">View Listings</a>
                        </div>
                    </div>
                    
                    <!-- Residential Category -->
                    <div class="col-md-3 mb-4">
                        <div class="card category-card text-center p-4 h-100">
                            <div class="icon-wrapper mb-3">
                                <i class="fas fa-house-user text-warning" style="font-size: 2.5rem;"></i>
                            </div>
                            <h3>Residential</h3>
                            <p class="text-muted">Residential properties</p>
                            <a href="properties.php?type=residential" class="btn btn-outline-warning mt-3">View Listings</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Estate Hub</h5>
                    <p>Find your perfect property with us.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="properties.php">Properties</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p>Email: info@estatehub.com<br>
                    Phone: +91 9989406361</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Force text visibility and black color
        function forceTextVisibility() {
            const heroSection = document.querySelector('.hero-section');
            const heroTitle = heroSection.querySelector('h1');
            const heroText = heroSection.querySelector('.lead');
            
            const blackTextStyles = `
                opacity: 1 !important;
                visibility: visible !important;
                animation: none !important;
                -webkit-animation: none !important;
                transform: none !important;
                transition: none !important;
                display: block !important;
                color: #000000 !important;
                text-shadow: none !important;
            `;
            
            if (heroTitle) {
                heroTitle.style.cssText = blackTextStyles;
                // Force color on any nested elements
                heroTitle.querySelectorAll('*').forEach(el => {
                    el.style.cssText = blackTextStyles;
                });
            }
            
            if (heroText) {
                heroText.style.cssText = blackTextStyles;
                // Force color on any nested elements
                heroText.querySelectorAll('*').forEach(el => {
                    el.style.cssText = blackTextStyles;
                });
            }
        }

        // Run immediately
        forceTextVisibility();

        // Run on DOM content loaded
        document.addEventListener('DOMContentLoaded', () => {
            forceTextVisibility();
            // Also run multiple times after load to ensure it sticks
            for(let i = 0; i < 5; i++) {
                setTimeout(forceTextVisibility, i * 100);
            }
        });

        // Run on load
        window.addEventListener('load', () => {
            forceTextVisibility();
            // Also run multiple times after load to ensure it sticks
            for(let i = 0; i < 5; i++) {
                setTimeout(forceTextVisibility, i * 100);
            }
        });
    </script>
</body>
</html> 