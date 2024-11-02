<?php

include 'db_connection.php';
include 'local_development.php';

header("Content-Type: application/json");


// Tatum API configuration
define('TATUM_API_KEY', 't-671cf7a047a06f2a4dbacb22-190c142ec86c4751a4aa624b');
define('TATUM_URL', 'https://solana-mainnet.gateway.tatum.io/');

$db = connect_db();

// Fetch user_id if the user-wallet combination exists
function get_user_id($db, $username, $wallet_address) {
    $stmt = $db->prepare("SELECT user_id, username, wallet_address FROM users WHERE username = ? OR wallet_address = ?");
    $stmt->execute([$username, $wallet_address]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if a donation with the same signature exists for any user
function signature_exists_for_different_user($db, $signature, $user_id) {
    $stmt = $db->prepare("SELECT user_id FROM donations WHERE signature = ? LIMIT 1;");
    $stmt->execute([$signature]);
    $existing_user_id = $stmt->fetchColumn();
    return $existing_user_id && $existing_user_id !== $user_id;
}

// Check if a donation with the same signature exists for the same user
function signature_exists_for_same_user($db, $signature, $user_id) {
    $stmt = $db->prepare("SELECT 1 FROM donations WHERE signature = ? AND user_id = ? LIMIT 1;");
    $stmt->execute([$signature, $user_id]);
    return $stmt->fetch() !== false;
}

// Save a new donation record
function save_donation($db, $signature, $user_id, $balance_change) {
    $stmt = $db->prepare("INSERT INTO donations (signature, user_id, donation_amount) VALUES (?, ?, ?)");
    return $stmt->execute([$signature, $user_id, $balance_change]);
}

// Fetch transaction details from Tatum API
function fetch_transaction_details($signature) {
    $body = json_encode([
        'jsonrpc' => '2.0',
        'method' => 'getTransaction',
        'params' => [$signature, ["encoding" => "jsonParsed"]],
        'id' => 1
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, TATUM_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . TATUM_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true)['result'] ?? [];
}

// Main logic
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? null;
$wallet_address = $data['wallet_address'] ?? null;
$signature = $data['signature'] ?? null;

// Validate input for username and wallet address
if (!$username || !$wallet_address) {
    echo json_encode(["error" => "Username and wallet address are required"]);
    exit;
}

// Only proceed to Tatum API validation and user data storage if signature is provided
if ($signature) {
    // Fetch user data based on username and wallet address
    $user = get_user_id($db, $username, $wallet_address);

    // Check if user already exists and if username/wallet matches
    if ($user) {
        if ($user['username'] !== $username || $user['wallet_address'] !== $wallet_address) {
            echo json_encode(["error" => "Username or wallet address does not match existing records."]);
            exit;
        }
        $user_id = $user['user_id'];
    } else {
        $user_id = null; // User does not exist; will be added after validation
    }

    // Check if the signature already exists for a different user
    if (signature_exists_for_different_user($db, $signature, $user_id)) {
        echo json_encode(["error" => "This signature is already associated with a different user."]);
        exit;
    }

    // Check if the signature already exists for the same user
    if ($user_id && signature_exists_for_same_user($db, $signature, $user_id)) {
        echo json_encode(["error" => "This transaction has already been processed for this user.", "code" => "TRANSACTION_ALREADY_PROCESSED"]);
        exit;
    }

    // Validate the signature with Tatum API
    $tx_details = fetch_transaction_details($signature);
    if (empty($tx_details) || !isset($tx_details['transaction'])) {
        echo json_encode(["error" => "Transaction not found or invalid signature"]);
        exit;
    }

    // Extract the sender address from the Tatum response
    $sender_address = null;
    foreach ($tx_details['transaction']['message']['accountKeys'] as $account) {
        if ($account['signer'] ?? false) {
            $sender_address = $account['pubkey'];
            break;
        }
    }

    // Ensure the sender address matches the provided wallet address
    if ($sender_address !== $wallet_address) {
        echo json_encode(["error" => "The wallet address does not match the sender address associated with this signature"]);
        exit;
    }

    // Process token balances
    $mint_address = 'DXBYAw9aQheMdujaLZYnVSpKSK4n8jMS7HfLbiv5RWnS';
    $burn_address = '1nc1nerator11111111111111111111111111111111';
    $new_balance = $previous_balance = 0.0;

    foreach ($tx_details['meta']['preTokenBalances'] ?? [] as $balance) {
        if ($balance['mint'] === $mint_address && $balance['owner'] === $burn_address) {
            $previous_balance = (float)$balance['uiTokenAmount']['uiAmount'];
        }
    }
    foreach ($tx_details['meta']['postTokenBalances'] ?? [] as $balance) {
        if ($balance['mint'] === $mint_address && $balance['owner'] === $burn_address) {
            $new_balance = (float)$balance['uiTokenAmount']['uiAmount'];
        }
    }

    $balance_change = $new_balance - $previous_balance;

    // If balance change is valid and user does not already exist, add the user and donation
    if ($balance_change != 0) {
        if (!$user_id) {
            // Add user only if they don't already exist
            $stmt = $db->prepare("INSERT INTO users (username, wallet_address) VALUES (?, ?)");
            $stmt->execute([$username, $wallet_address]);
            $user_id = $db->lastInsertId();
        }

        save_donation($db, $signature, $user_id, $balance_change);
        echo json_encode([
            "status" => "success",
            "message" => "Transaction processed and user data saved",
            "username" => $username,
            "wallet_address" => $wallet_address,
            "donation_amount" => $balance_change,
            "fetched_at" => (new DateTime())->format(DateTime::ISO8601)
        ]);
    } else {
        echo json_encode(["error" => "No balance change found for the specified token"]);
    }
} else {
    // If no signature is provided, just validate username and wallet without storing data
    echo json_encode(["status" => "success", "message" => "User and wallet verified"]);
}
