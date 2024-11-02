<?php
include 'db_connection.php';
include 'local_development.php';

header("Content-Type: application/json");

// Base58 decoding function for Solana public keys
function base58_decode($input) {
    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $base = strlen($alphabet);
    $size = strlen($input);
    $num = gmp_init(0, 10);

    for ($i = 0; $i < $size; $i++) {
        $pos = strpos($alphabet, $input[$i]);
        if ($pos === false) {
            return false; // Invalid character found
        }
        $num = gmp_add(gmp_mul($num, $base), $pos);
    }
    return gmp_export($num);
}

// Connect to the database
$db = connect_db();

// Retrieve the nonce from the database for a specific wallet
function get_nonce($db, $wallet_address) {
    $stmt = $db->prepare("SELECT nonce FROM nonces WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    return $stmt->fetchColumn();
}

// Verify the signature using Ed25519 (libsodium)
function verify_signature($publicKey, $signature, $message) {
    $decodedSignature = base64_decode($signature);  // Decode the base64-encoded signature
    $decodedPublicKey = base58_decode($publicKey);  // Decode the public key from Base58 to binary

    // Verify the signature using sodium's Ed25519 verification function
    return sodium_crypto_sign_verify_detached($decodedSignature, $message, $decodedPublicKey);
}

// Fetch or create a username for a wallet
function get_or_create_username($db, $wallet_address) {
    // Check if the wallet already has a username
    $stmt = $db->prepare("SELECT username FROM users WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        return $user['username'];
    } else {
        // Generate a new username from the first 8 characters of the wallet address
        $username = substr($wallet_address, 0, 8);

        // Store the new username in the database
        $insert_stmt = $db->prepare("INSERT INTO users (wallet_address, username) VALUES (?, ?)");
        $insert_stmt->execute([$wallet_address, $username]);

        return $username;
    }
}

// Main logic
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;
$signature = $data['signature'] ?? null;

if (!$wallet_address || !$signature) {
    echo json_encode(["error" => "Wallet address and signature are required"]);
    exit;
}

// Retrieve the nonce
$nonce = get_nonce($db, $wallet_address);
if (!$nonce) {
    echo json_encode(["error" => "Nonce not found or expired"]);
    exit;
}

// Verify the signature (nonce is used as the message)
if (!verify_signature($wallet_address, $signature, $nonce)) {
    echo json_encode(["error" => "Signature verification failed"]);
    exit;
}

// Fetch or create a username
$username = get_or_create_username($db, $wallet_address);

echo json_encode([
    "status" => "success",
    "wallet_address" => $wallet_address,
    "username" => $username
]);

