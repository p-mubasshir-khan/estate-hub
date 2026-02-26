<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

// Get statistics
try {
    // Total properties
    $stmt = $conn->query("SELECT COUNT(*) as total FROM properties");
    $total_properties = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Properties by type
    $stmt = $conn->query("SELECT listing_type, COUNT(*) as count FROM properties GROUP BY listing_type");
    $properties_by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Properties by location
    $stmt = $conn->query("SELECT location, COUNT(*) as count FROM properties GROUP BY location");
    $properties_by_location = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching statistics: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Estate Hub</title>
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
        .stat-card {
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="properties.php">
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
                    <h2>Dashboard</h2>
                    <div>
                        Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <h3><?php echo $total_properties; ?></h3>
                            <p class="mb-0">Total Properties</p>
                        </div>
                    </div>
                    <?php foreach($properties_by_type as $type): ?>
                    <div class="col-md-3">
                        <div class="stat-card bg-success text-white">
                            <h3><?php echo $type['count']; ?></h3>
                            <p class="mb-0"><?php echo ucfirst($type['listing_type']); ?> Properties</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Properties by Location -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Properties by Location
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Location</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($properties_by_location as $location): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($location['location']); ?></td>
                                            <td><?php echo $location['count']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 