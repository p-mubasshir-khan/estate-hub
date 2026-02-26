-- Create the database
CREATE DATABASE IF NOT EXISTS real_estate;
USE real_estate;

-- Create properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    property_type ENUM('house', 'apartment', 'villa', 'plot', 'commercial') NOT NULL,
    listing_type ENUM('buy', 'rent', 'commercial') NOT NULL DEFAULT 'buy',
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    square_feet INT NOT NULL,
    year_built INT NOT NULL,
    garage VARCHAR(50) NOT NULL,
    lot_size VARCHAR(50) NOT NULL,
    status ENUM('available', 'sold', 'pending') NOT NULL DEFAULT 'available',
    image_url VARCHAR(255) NOT NULL,
    featured BOOLEAN DEFAULT FALSE,
    car_parking BOOLEAN DEFAULT FALSE,
    total_floors INT DEFAULT 1,
    floor_number INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create property_images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Create amenities table
CREATE TABLE IF NOT EXISTS amenities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create property_amenities table
CREATE TABLE IF NOT EXISTS property_amenities (
    property_id INT,
    amenity_id INT,
    PRIMARY KEY (property_id, amenity_id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

-- Create inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    property_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

-- Insert sample properties
INSERT INTO properties (title, description, price, location, property_type, bedrooms, bathrooms, square_feet, year_built, garage, lot_size, status, image_url, featured) VALUES
('Modern Downtown Apartment', 'Beautiful modern apartment in the heart of downtown. Features high ceilings, floor-to-ceiling windows, and premium finishes.', 450000.00, 'Downtown, City', 'apartment', 2, 2, 1200, 2020, '1 car', 'N/A', 'available', 'assets/images/properties/apartment1.jpg', 1),
('Luxury Family Home', 'Spacious family home with modern amenities and beautiful landscaping. Perfect for growing families.', 750000.00, 'Suburban Area', 'house', 4, 3, 2500, 2018, '2 car', '0.5 acres', 'available', 'assets/images/properties/house1.jpg', 1),
('Beachfront Villa', 'Stunning beachfront villa with panoramic ocean views. Private pool and outdoor entertainment area.', 1200000.00, 'Beachfront', 'villa', 5, 4, 3500, 2021, '3 car', '1 acre', 'available', 'assets/images/properties/villa1.jpg', 1),
('Cozy Condo', 'Modern condo with city views and access to amenities. Perfect for young professionals.', 350000.00, 'City Center', 'condo', 1, 1, 800, 2019, '1 car', 'N/A', 'available', 'assets/images/properties/condo1.jpg', 0),
('Suburban Family Home', 'Classic family home in a quiet neighborhood. Large backyard and finished basement.', 550000.00, 'Suburban Area', 'house', 3, 2, 2000, 2015, '2 car', '0.3 acres', 'available', 'assets/images/properties/house2.jpg', 0);

-- Insert default amenities
INSERT INTO amenities (name, icon) VALUES 
('24/7 Water Supply', 'water'),
('WiFi', 'wifi'),
('Elevator', 'elevator'),
('Swimming Pool', 'swimming-pool'),
('Gym', 'dumbbell'),
('Security', 'shield-alt'),
('Power Backup', 'bolt'),
('Parking', 'parking'),
('Garden', 'tree'),
('CCTV', 'video'); 