<?php

include 'db_connection.php';
include 'local_development.php';

header("Content-Type: application/json");

// Connect to the database
$db = connect_db();

// Retrieve the stored nonce for a specific wallet
function get_nonce($db, $wallet_address) {
    $stmt = $db->prepare("SELECT nonce FROM nonces WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    return $stmt->fetchColumn();
}

// Main logic
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;
$nonce = $data['nonce'] ?? null; // The nonce sent by the user
$username = $data['username'] ?? null;

if (!$wallet_address || !$nonce || !$username) {
    echo json_encode(["error" => "Wallet address, nonce, and username are required"]);
    exit;
}

// Retrieve the nonce from the database for the provided wallet address
$stored_nonce = get_nonce($db, $wallet_address);
if (!$stored_nonce) {
    echo json_encode(["error" => "Nonce not found or expired"]);
    exit;
}

// Verify that the provided nonce matches the stored nonce
if ($nonce !== $stored_nonce) {
    echo json_encode(["error" => "Nonce verification failed"]);
    exit;
}

// Check if the username matches the record for this wallet address
$stmt = $db->prepare("SELECT user_id FROM users WHERE wallet_address = ? AND username = ?");
$stmt->execute([$wallet_address, $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["error" => "Username or wallet address is incorrect"]);
    exit;
}

// Fetch the sum of donations for the verified user
try {
    $user_id = $user['user_id'];
    
    // Query to calculate the sum of donation amounts for the user
    $stmt = $db->prepare("SELECT COALESCE(SUM(donation_amount), 0) AS total_donations FROM donations WHERE user_id = :user_id");
    
    // Bind the user_id parameter
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the sum result
    $total_donations = $stmt->fetchColumn();

    // Output the result as JSON
    echo json_encode(["status" => "success", "total_donations" => $total_donations]);

} catch (Exception $e) {
    // Handle any errors that occur during the query execution
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

?>
