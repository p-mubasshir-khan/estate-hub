<?php
$servername = getenv("DB_HOST");
$username   = getenv("DB_USER");
$password   = getenv("DB_PASS");
$dbname     = getenv("DB_NAME");
$port       = (int)(getenv("DB_PORT") ?: 3306);

try {
    $dsn = "mysql:host={$servername};port={$port};dbname={$dbname};charset=utf8mb4;unix_socket=";

    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
