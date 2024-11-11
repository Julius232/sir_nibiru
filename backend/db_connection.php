<?php
// Define database constants (adjust these values based on your actual database configuration)
define('DB_HOST', 'mysqlsvr80.world4you.com');  // Database host
define('DB_PORT', '3306');                      // Database port
define('DB_NAME', '4158319db1');                // Database name
define('DB_USER', 'sql5999128');                // Database username
define('DB_PASSWORD', 'f+8qq@ts');              // Database password

// Database connection function
function connect_db() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Enable exception mode for errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC  // Set default fetch mode to associative array
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Display error message in JSON format for easy debugging
        echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
        exit;
    }
}
