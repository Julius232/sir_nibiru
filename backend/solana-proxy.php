<?php
include 'local_development.php';

// Define Tatum constants
define('TATUM_API_KEY', 't-671cf7a047a06f2a4dbacb22-190c142ec86c4751a4aa624b'); // Replace with your actual Tatum API key
define('TATUM_URL', 'https://solana-mainnet.gateway.tatum.io/');

// Get the POST data from the client
$postData = file_get_contents('php://input');

// Initialize a cURL session to Tatum's Solana endpoint
$ch = curl_init(TATUM_URL);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . TATUM_API_KEY
]);

// Execute the cURL request and get the response
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    http_response_code(curl_getinfo($ch, CURLINFO_HTTP_CODE)); // Forward the HTTP response code
    echo $response; // Output the response from Tatum API
}

// Close the cURL session
curl_close($ch);
?>
