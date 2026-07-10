<?php
/**
 * TechHub — Database Configuration
 * File: config/db.php
 *
 * Update the constants below to match your hosting environment.
 * For local development use XAMPP/WAMP (host: localhost).
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'techhub_db');
define('DB_USER',    'techhub_user');   // change to your MySQL username
define('DB_PASS',    'StrongPass123!'); // change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
