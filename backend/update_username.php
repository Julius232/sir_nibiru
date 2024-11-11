<?php
include 'db_connection.php';
include 'local_development.php';

header("Content-Type: application/json");

$db = connect_db();
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;
$new_username = $data['new_username'] ?? null;
$provided_nonce = $data['nonce'] ?? null;

function validate_nonce($wallet_address, $provided_nonce) {
    $db = connect_db();

    // Retrieve the stored nonce for the wallet address
    $stmt = $db->prepare("SELECT nonce FROM nonces WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    $stored_nonce = $stmt->fetchColumn();

    // Verify the nonce
    if (!$stored_nonce || $stored_nonce !== $provided_nonce) {
        return ["status" => "error", "message" => "Invalid nonce"];
    }

    // Generate a new nonce to replace the used one
    $new_nonce = bin2hex(random_bytes(32));

    // Update the nonce in the database
    $updateStmt = $db->prepare("UPDATE nonces SET nonce = ? WHERE wallet_address = ?");
    $updateStmt->execute([$new_nonce, $wallet_address]);

    return ["status" => "success", "new_nonce" => $new_nonce];
}


// Check required fields
if (!$wallet_address || !$new_username || !$provided_nonce) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

// Validate username length
if (strlen($new_username) > 12) {
    echo json_encode(["status" => "error", "message" => "Username cannot exceed 12 characters."]);
    exit;
}

// Validate nonce
$nonce_result = validate_nonce($wallet_address, $provided_nonce);
if ($nonce_result['status'] !== "success") {
    echo json_encode($nonce_result); // Output error message if nonce is invalid
    exit;
}

// Check for username existence
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$stmt->execute([$new_username]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(["status" => "error", "message" => "Username already taken."]);
    exit;
}

// Update the username
$updateStmt = $db->prepare("UPDATE users SET username = ? WHERE wallet_address = ?");
$updateStmt->execute([$new_username, $wallet_address]);

echo json_encode([
    "status" => "success",
    "message" => "Username updated successfully",
    "new_nonce" => $nonce_result['new_nonce']
]);