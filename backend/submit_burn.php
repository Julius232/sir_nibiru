<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();
define('API_URL', 'https://tame-few-season.solana-mainnet.quiknode.pro/f6d67e803c7016d76b4d81614f5c3b48531639d7');
define('SPL_TOKEN_PROGRAM_ID', 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA');

// Fetch token mint address from the config table
$token_mint_address = getConfigValue('tokenMintAddress');

// Get JSON input and decode it
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;
$signature = $data['signature'] ?? null;
$action = $data['action'] ?? null;
$nonce = $data['nonce'] ?? null;

// Fetch the user_id based on the wallet address
$user_id = get_user_id_from_wallet($wallet_address);
// Telegram Bot token
$botToken = '8019392115:AAGLca28IFuMDSrbRPF7GXm7TNxaXrdBur8';
// Chat ID to send messages to (replace with the actual chat ID or use your own user ID for testing)
$chatId = '-1002481738456';

// Check if required fields are present
if (!$wallet_address || !$signature || !$action || !$nonce) {
    record_failed_transaction($signature, "Missing required fields", $action, $user_id);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields.",
        "details" => [
            "wallet_address" => $wallet_address ?? "missing",
            "signature" => $signature ?? "missing",
            "action" => $action ?? "missing",
            "nonce" => $nonce ?? "missing"
        ]
    ]);
    exit;
}

// Validate nonce by checking if it exists in the database
$stmt = $db->prepare("SELECT nonce FROM nonces WHERE wallet_address = ? AND nonce = ?");
$stmt->execute([$wallet_address, $nonce]);
$nonce_row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nonce_row) {
    record_failed_transaction($signature, "Invalid nonce", $action, $user_id);
    echo json_encode(["status" => "error", "message" => "Invalid nonce. Please re-authenticate."]);
    exit;
}

// Check if the signature already exists in the database
$stmt = $db->prepare("SELECT donation_id FROM donations WHERE signature = ?");
$stmt->execute([$signature]);
$existing_donation = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing_donation) {
    echo json_encode([
        "status" => "error",
        "message" => "This signature has already been processed.",
        "signature" => $signature
    ]);
    exit;
}

// Function to send a message to Telegram
function sendTelegramMessage($username, $burned_amount) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    // Improved message with a link to the burn website and added emphasis
    $message = "🔥 *Token Burn Alert!* 🔥\n\n" .
               "👤 *User*: $username just contributed to our community burn!\n\n" .
               "💰 *Amount Burned*: $burned_amount tokens\n\n" .
               "💪 *WE BURN FOR OUR COMMUNITY!*\n\n" .
               "🌐 [Visit Sir Nibiru's official burn page](https://www.sir-nibiru.com) to join the action! 🎉";

    $postFields = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Proceed with processing the action

function getConfigValue($key) {
    global $db;
    $stmt = $db->prepare("SELECT config_value FROM config WHERE config_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

function record_failed_transaction($signature, $reason, $action = null, $user_id = null) {
    global $db;
    $stmt = $db->prepare("INSERT INTO failed_transactions (signature, reason, action, user_id, timestamp) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$signature, $reason, $action, $user_id, date('Y-m-d H:i:s')]);
}

function get_user_id_from_wallet($wallet_address) {
    global $db;
    $stmt = $db->prepare("SELECT user_id FROM users WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['user_id'] : null;
}

function fetch_transaction_details($signature) {
    global $action, $user_id; // Ensure action and user_id are available in case of failure
    $attempts = 6;
    $delay = 10;

    for ($i = 0; $i < $attempts; $i++) {
        $body = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'getTransaction',
            'params' => [$signature, ["encoding" => "jsonParsed"]],
            'id' => 1
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            record_failed_transaction($signature, "Failed to connect to API", $action, $user_id);
            echo json_encode(["status" => "error", "message" => "Failed to connect to API", "details" => curl_error($ch)]);
            curl_close($ch);
            exit;
        }

        curl_close($ch);
        $decoded_response = json_decode($response, true);

        if (isset($decoded_response['result'])) {
            return $decoded_response['result'];
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid response from API, retrying...", "attempt" => $i + 1]);
            if ($i < $attempts - 1) {
                sleep($delay);
            }
        }
    }

    record_failed_transaction($signature, "Failed to fetch transaction details after multiple attempts", $action, $user_id);
    echo json_encode(["status" => "error", "message" => "Failed to fetch transaction details after multiple attempts"]);
    return null;
}

function fetch_transaction_details_with_retry($signature, $maxRetries = 5, $initialRetryInterval = 5) {
    global $action, $user_id;
    $attempt = 0;
    $retryInterval = $initialRetryInterval;

    while ($attempt < $maxRetries) {
        $tx_details = fetch_transaction_details($signature);

        if ($tx_details !== null && isset($tx_details['transaction'])) {
            return $tx_details;
        }

        error_log("Transaction not found, retrying in {$retryInterval} seconds (Attempt ".($attempt + 1)." of {$maxRetries})");
        sleep($retryInterval);
        $retryInterval *= 2;
        $attempt++;
    }

    record_failed_transaction($signature, "Transaction not found after multiple retries", $action, $user_id);
    echo json_encode([
        "status" => "error",
        "message" => "Transaction not found after multiple retries",
        "signature" => $signature
    ]);
    exit;
}

$tx_details = fetch_transaction_details_with_retry($signature);
if (empty($tx_details) || !isset($tx_details['transaction'])) {
    record_failed_transaction($signature, "Transaction not found or invalid signature", $action, $user_id);
    echo json_encode(["status" => "error", "message" => "Transaction not found or invalid signature", "tx_details" => $tx_details]);
    exit;
}

$burned_amount = 0;
$burn_instruction_found = false;
$accountKeys = $tx_details['transaction']['message']['accountKeys'] ?? [];

$instructions = $tx_details['transaction']['message']['instructions'] ?? [];
foreach ($instructions as $instruction) {
    $programId = $instruction['programId'] ?? null;

    if ($programId === SPL_TOKEN_PROGRAM_ID) {
        if (isset($instruction['parsed'])) {
            $parsedInstruction = $instruction['parsed'];
            if ($parsedInstruction['type'] === 'burnChecked') {
                $burn_instruction_found = true;

                $info = $parsedInstruction['info'];
                $tokenAmount = $info['tokenAmount'];
                $amount = $tokenAmount['amount'];
                $decimals = $tokenAmount['decimals'];

                $burned_amount = $amount / pow(10, $decimals);

                $mint_account = $info['mint'];
                $owner_account = $info['authority'];

                if ($mint_account !== $token_mint_address) {
                    record_failed_transaction($signature, "Mint address does not match", $action, $user_id);
                    echo json_encode([
                        "status" => "error",
                        "message" => "Mint address does not match",
                        "expected_mint_address" => $token_mint_address,
                        "found_mint_address" => $mint_account
                    ]);
                    exit;
                }

                if ($owner_account !== $wallet_address) {
                    record_failed_transaction($signature, "Owner address does not match", $action, $user_id);
                    echo json_encode([
                        "status" => "error",
                        "message" => "Owner address does not match",
                        "expected_owner_address" => $wallet_address,
                        "found_owner_address" => $owner_account
                    ]);
                    exit;
                }
                break;
            }
        } else {
            record_failed_transaction($signature, "Parsed instruction data not available", $action, $user_id);
            echo json_encode([
                "status" => "error",
                "message" => "Parsed instruction data not available"
            ]);
            exit;
        }
    }
}

if (!$burn_instruction_found) {
    record_failed_transaction($signature, "No BurnChecked instruction found in the transaction", $action, $user_id);
    echo json_encode([
        "status" => "error",
        "message" => "No BurnChecked instruction found in the transaction"
    ]);
    exit;
}

$stmt = $db->prepare("SELECT username, user_id FROM users WHERE wallet_address = ?");
$stmt->execute([$wallet_address]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    record_failed_transaction($signature, "User not found for this wallet address", $action, $user_id);
    echo json_encode(["status" => "error", "message" => "User not found for this wallet address", "wallet_address" => $wallet_address]);
    exit;
}

$user_id = $user['user_id'];
$username = $user['username'];

try {
    $stmt = $db->prepare("INSERT INTO donations (user_id, signature, donation_amount, action) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $signature, $burned_amount, $action]);

    sendTelegramMessage($username, $burned_amount);

    echo json_encode([
        "status" => "success",
        "message" => "Action recorded successfully!",
        "action" => $action,
        "burned_amount" => $burned_amount
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            "status" => "error",
            "message" => "This signature has already been processed.",
            "signature" => $signature
        ]);
    } else {
        record_failed_transaction($signature, "Database error: " . $e->getMessage(), $action, $user_id);
        echo json_encode(["status" => "error", "message" => "Database error", "details" => $e->getMessage()]);
    }
}
?>
