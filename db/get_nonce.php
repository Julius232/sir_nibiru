<?php
include 'db_connection.php';
include 'local_development.php';

header("Content-Type: application/json");

$db = connect_db();

// Generate a nonce and save it for the session
function generate_nonce($db, $wallet_address) {
    $nonce = bin2hex(random_bytes(16));  // Generate a 32-character unique nonce
    $stmt = $db->prepare("INSERT INTO nonces (wallet_address, nonce, created_at) VALUES (?, ?, NOW())
                          ON DUPLICATE KEY UPDATE nonce = ?, created_at = NOW()");
    $stmt->execute([$wallet_address, $nonce, $nonce]);
    return $nonce;
}

// Main logic
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;

if (!$wallet_address) {
    echo json_encode(["error" => "Wallet address is required"]);
    exit;
}

$nonce = generate_nonce($db, $wallet_address);

echo json_encode(["nonce" => $nonce]);
