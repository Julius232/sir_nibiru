<?php
// router.php

session_start();

// Configuration: Define allowed routes and security settings
$routes = [
    '/get_nonce' => '/backend/get_nonce.php',
    '/verify_user' => '/backend/verify_user.php',
    '/fetch_config' => '/backend/fetch_config.php',
    '/fetch_donations' => '/backend/fetch_donations.php',
    '/get_best_friend' => '/backend/get_best_friend.php',
    '/get_highscore' => '/backend/get_highscore.php',
    '/get_last_sitters' => '/backend/get_last_sitters.php',
    '/get_total_burned' => '/backend/get_total_burned.php',
    '/submit_burn' => '/backend/submit_burn.php',
    '/update_username' => '/backend/update_username.php',
    '/disconnect_user' => '/backend/disconnect_user.php'
];

// Helper function for JSON responses
function json_response($status, $message, $data = []) {
    header('Content-Type: application/json'); // Set JSON content type
    echo json_encode(array_merge(["status" => $status, "message" => $message], $data));
    exit;
}

// Step 2: Validate and Sanitize Input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Step 3: Authenticate Request (Example with Token Validation)
function authenticate() {
    $auth_token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($auth_token !== 'YourSecretToken') {  // Replace with your actual token logic
        json_response("error", "Unauthorized");
    }
}

// Step 4: Rate Limiting (Example: Limit to 60 requests per minute per user)
function rate_limit($identifier) {
    $limit = 60;
    $time_window = 60; // in seconds
    $current_time = time();

    if (!isset($_SESSION['rate_limit'][$identifier])) {
        $_SESSION['rate_limit'][$identifier] = [];
    }

    $_SESSION['rate_limit'][$identifier] = array_filter($_SESSION['rate_limit'][$identifier], function ($timestamp) use ($current_time, $time_window) {
        return ($timestamp > $current_time - $time_window);
    });

    if (count($_SESSION['rate_limit'][$identifier]) >= $limit) {
        json_response("error", "Rate limit exceeded. Try again later.");
    }

    $_SESSION['rate_limit'][$identifier][] = $current_time;
}

// Step 5: Route the Request
$path = str_replace('/sir_nibiru', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Validate route
if (!isset($routes[$path])) {
    http_response_code(404);
    json_response("error", "Endpoint not found");
}

// Sanitize input and perform authentication
foreach ($_POST as $key => $value) {
    $_POST[$key] = sanitize_input($value);
}
foreach ($_GET as $key => $value) {
    $_GET[$key] = sanitize_input($value);
}

// Optional: Authenticate request (uncomment to use)
// authenticate();

// Apply rate limiting based on user's IP or unique identifier (e.g., user ID)
$client_identifier = $_SERVER['REMOTE_ADDR']; // Or user ID if available
rate_limit($client_identifier);

$file = __DIR__ . $routes[$path];  // Ensure the full path to the file
if (file_exists($file)) {
    include $file;
} else {
    json_response("error", "File not found for route");
}
