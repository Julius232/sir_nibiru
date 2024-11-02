<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();
define('API_URL', 'https://tame-few-season.solana-mainnet.quiknode.pro/f6d67e803c7016d76b4d81614f5c3b48531639d7');
define('SPL_TOKEN_PROGRAM_ID', 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA');

// Get JSON input and decode it
$data = json_decode(file_get_contents("php://input"), true);
$wallet_address = $data['wallet_address'] ?? null;
$signature = $data['signature'] ?? null;
$action = $data['action'] ?? null;

// Check if required fields are present
if (!$wallet_address || !$signature || !$action) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields.",
        "details" => [
            "wallet_address" => $wallet_address ?? "missing",
            "signature" => $signature ?? "missing",
            "action" => $action ?? "missing"
        ]
    ]);
    exit;
}

// Check if the signature has already been processed
$stmt = $db->prepare("SELECT COUNT(*) FROM donations WHERE signature = ?");
$stmt->execute([$signature]);
$existingCount = $stmt->fetchColumn();

if ($existingCount > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "This signature has been used already.",
        "signature" => $signature
    ]);
    exit;
}

// Fetch and validate transaction details with API
function fetch_transaction_details($signature) {
    $body = json_encode([
        'jsonrpc' => '2.0',
        'method' => 'getTransaction',
        'params' => [$signature, ["encoding" => "jsonParsed"]],
        'id' => 1
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo json_encode(["status" => "error", "message" => "Failed to connect to API", "details" => curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);
    $decoded_response = json_decode($response, true);
    if (!isset($decoded_response['result'])) {
        echo json_encode(["status" => "error", "message" => "Invalid response from API", "response" => $decoded_response]);
        exit;
    }

    return $decoded_response['result'];
}

function fetch_transaction_details_with_retry($signature, $maxRetries = 5, $initialRetryInterval = 5) {
    $attempt = 0;
    $retryInterval = $initialRetryInterval;

    while ($attempt < $maxRetries) {
        $tx_details = fetch_transaction_details($signature);

        if ($tx_details !== null && isset($tx_details['transaction'])) {
            return $tx_details;
        }

        // Log the retry attempt details
        error_log("Transaction not found, retrying in {$retryInterval} seconds (Attempt ".($attempt + 1)." of {$maxRetries})");
        sleep($retryInterval); // Wait before retrying

        // Exponential backoff
        $retryInterval *= 2;
        $attempt++;
    }

    // Final error if transaction not found after all retries
    echo json_encode([
        "status" => "error",
        "message" => "Transaction not found after multiple retries",
        "signature" => $signature
    ]);
    exit;
}



// Fetch transaction details
$tx_details = fetch_transaction_details_with_retry($signature);
if (empty($tx_details) || !isset($tx_details['transaction'])) {
    echo json_encode(["status" => "error", "message" => "Transaction not found or invalid signature", "tx_details" => $tx_details]);
    exit;
}

// Initialize variables
$burned_amount = 0;
$token_mint_address = 'DXBYAw9aQheMdujaLZYnVSpKSK4n8jMS7HfLbiv5RWnS';
$burn_instruction_found = false;

// Get the accountKeys array for index references
$accountKeys = $tx_details['transaction']['message']['accountKeys'] ?? [];

// Iterate over the transaction instructions
$instructions = $tx_details['transaction']['message']['instructions'] ?? [];
foreach ($instructions as $instruction) {
    // Get the programId directly
    $programId = $instruction['programId'] ?? null;

    if ($programId === SPL_TOKEN_PROGRAM_ID) {
        // Check if 'parsed' instruction is available
        if (isset($instruction['parsed'])) {
            $parsedInstruction = $instruction['parsed'];
            // Check if the type is 'burnChecked' (case-sensitive comparison)
            if ($parsedInstruction['type'] === 'burnChecked') {
                $burn_instruction_found = true;

                // Extract the info
                $info = $parsedInstruction['info'];
                // Extract tokenAmount details
                $tokenAmount = $info['tokenAmount'];
                $amount = $tokenAmount['amount']; // This is a string
                $decimals = $tokenAmount['decimals'];

                // Convert amount to human-readable format
                $burned_amount = $amount / pow(10, $decimals);

                // Verify mint address and owner
                $mint_account = $info['mint'];
                $owner_account = $info['authority'];

                // Verify mint address
                if ($mint_account !== $token_mint_address) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Mint address does not match",
                        "expected_mint_address" => $token_mint_address,
                        "found_mint_address" => $mint_account
                    ]);
                    exit;
                }

                // Verify owner address
                if ($owner_account !== $wallet_address) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Owner address does not match",
                        "expected_owner_address" => $wallet_address,
                        "found_owner_address" => $owner_account
                    ]);
                    exit;
                }

                // At this point, we've successfully parsed the burn instruction
                break;
            }
        } else {
            // Handle the case where 'parsed' is not available
            echo json_encode([
                "status" => "error",
                "message" => "Parsed instruction data not available"
            ]);
            exit;
        }

    }
}

if (!$burn_instruction_found) {
    echo json_encode([
        "status" => "error",
        "message" => "No BurnChecked instruction found in the transaction"
    ]);
    exit;
}

// Retrieve user_id based on wallet_address
$stmt = $db->prepare("SELECT user_id FROM users WHERE wallet_address = ?");
$stmt->execute([$wallet_address]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "User not found for this wallet address", "wallet_address" => $wallet_address]);
    exit;
}

$user_id = $user['user_id'];

// Store the action and transaction details
try {
    $stmt = $db->prepare("INSERT INTO donations (user_id, signature, donation_amount, action) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $signature, $burned_amount, $action]);

    echo json_encode([
        "status" => "success",
        "message" => "Action recorded successfully!",
        "action" => $action,
        "burned_amount" => $burned_amount
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error", "details" => $e->getMessage()]);
}
