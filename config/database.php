<?php
/**
 * Database Connection
 * PDO connection to MySQL database for University Events Management System
 */

// Database credentials
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // Change if you have a password
$DB_NAME = 'uni_events';

try {
    // Create DSN (Data Source Name)
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    
    // Create PDO connection
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
} catch (PDOException $e) {
    // Log error to file instead of displaying to user
    error_log("Database connection failed: " . $e->getMessage(), 3, "/var/log/uni_events.log");
    
    // Show generic error message to user
    die("Database connection error. Please contact administrator.");
}
