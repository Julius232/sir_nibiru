<?php
// db_connection.php
include_once 'config_loader.php';


// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type header to application/json
header('Content-Type: application/json');

// Verify that loadEnv() is defined
if (!function_exists('loadEnv')) {
    echo json_encode(["error" => "Function loadEnv() is not defined after including config_loader.php"]);
    exit;
}

// Load environment variables and get variables as an array
try {
    $envVariables = loadEnv(__DIR__ . '/.env');
} catch (Exception $e) {
    echo json_encode(["error" => "Error loading environment variables: " . $e->getMessage()]);
    exit;
}

// Fetch database configuration from the returned array
$dbHost = $envVariables['DB_HOST'] ?? null;
$dbPort = $envVariables['DB_PORT'] ?? null;
$dbName = $envVariables['DB_NAME'] ?? null;
$dbUser = $envVariables['DB_USER'] ?? null;
$dbPassword = $envVariables['DB_PASSWORD'] ?? null;

// Check that all necessary variables are set and not empty
if (empty($dbHost) || empty($dbPort) || empty($dbName) || empty($dbUser)) {
    echo json_encode(["error" => "Database configuration variables are not set properly in the .env file."]);
    exit;
}


// Database connection function
function connect_db() {
    global $dbHost, $dbPort, $dbName, $dbUser, $dbPassword;

    try {
        $pdo = new PDO(
            "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        return $pdo;

    } catch (PDOException $e) {
        echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
        exit;
    }
}
