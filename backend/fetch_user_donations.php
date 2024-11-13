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

// Prepare the query to get the last 10 transactions for the verified user
try {
    $user_id = $user['user_id'];
    
    $stmt = $db->prepare("
        (
            SELECT
                signature,  
                state, 
                action,
                'Not Processed' AS donation_amount, 
                timestamp
            FROM failed_transactions
            WHERE user_id = :user_id
        )
        UNION ALL
        (
            SELECT 
                signature, 
                'success' AS state,
                action,
                donation_amount,             
                timestamp
            FROM donations
            WHERE user_id = :user_id
        )
        ORDER BY timestamp DESC
        LIMIT 10
    ");
    
    // Bind the user_id parameter
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the results as JSON
    echo json_encode(["status" => "success", "transactions" => $transactions]);

} catch (Exception $e) {
    // Handle any errors that occur during the query execution
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

?>
