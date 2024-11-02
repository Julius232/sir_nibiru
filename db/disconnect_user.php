<?php
include 'local_development.php';
include 'db_connection.php';

header("Content-Type: application/json");

$db = connect_db();

// Main logic to delete nonce
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;

if (!$wallet_address) {
    echo json_encode(["error" => "Wallet address is required"]);
    exit;
}

try {
    $stmt = $db->prepare("DELETE FROM nonces WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);

    echo json_encode(["status" => "success", "message" => "Nonce deleted for wallet address"]);
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to delete nonce: " . $e->getMessage()]);
}
