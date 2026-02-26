# Real Estate Property Listings Website

A modern and responsive real estate website built with PHP, MySQL, and Bootstrap 5. This application allows users to browse property listings, view detailed property information, and contact agents about properties.

## Features

- Responsive design that works on all devices
- Property search with filters (price range, property type)
- Detailed property listings with images
- Contact form for property inquiries
- Featured properties section
- Modern and clean user interface

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- mod_rewrite enabled (for Apache)

## Installation

1. Clone this repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/real-estate.git
   ```

2. Create a MySQL database and import the database structure:
   ```bash
   mysql -u root -p < database.sql
   ```

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'real_estate');
     ```

4. Create an `assets/images/properties` directory and add property images:
   ```bash
   mkdir -p assets/images/properties
   ```

5. Set proper permissions:
   ```bash
   chmod 755 assets/images/properties
   ```

## Directory Structure

```
real-estate/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
│       └── properties/
├── config/
│   └── database.php
├── database.sql
├── index.php
├── properties.php
├── property.php
└── contact.php
```

## Usage

1. Access the website through your web browser:
   ```
   http://localhost/real-estate
   ```

2. Browse properties using the search filters
3. Click on property cards to view detailed information
4. Use the contact form to inquire about properties

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

Your Name - your.email@example.com
Project Link: https://github.com/yourusername/real-estate 