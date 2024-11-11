<?php
include 'db_connection.php';
include 'local_development.php';

$db = connect_db();
define('API_URL', 'https://tame-few-season.solana-mainnet.quiknode.pro/f6d67e803c7016d76b4d81614f5c3b48531639d7');
define('SPL_TOKEN_PROGRAM_ID', 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA');

$log_messages = [];

// Helper function to log messages
function log_message($message) {
    global $log_messages;
    $log_messages[] = $message;
}

// Fetch entries from failed_transactions older than 1 hour and still in 'pending' state
$stmt = $db->prepare("SELECT id, signature, action FROM failed_transactions WHERE timestamp < (NOW() - INTERVAL 1 MINUTE) AND state = 'pending'");
$stmt->execute();
$failed_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

log_message("Found " . count($failed_transactions) . " pending transactions to process.");

// Process each failed transaction
foreach ($failed_transactions as $transaction) {
    $signature = $transaction['signature'];
    $id = $transaction['id'];
    $action = $transaction['action'];

    log_message("Processing Transaction ID: $id with Signature: $signature");

    // Attempt to process the transaction (fetch and validate details)
    $process_result = process_transaction($signature);

    if ($process_result['status'] === 'success') {
        // Transaction was successfully processed, try storing it in donations
        if (store_donation($process_result, $action)) {
            // Delete from failed_transactions if stored successfully
            $delete_stmt = $db->prepare("DELETE FROM failed_transactions WHERE id = ?");
            $delete_stmt->execute([$id]);
            log_message("Transaction $signature successfully reprocessed, stored in donations, and removed from failed transactions.");
        } else {
            // Failed to store in donations, mark as "failed" with reason
            update_transaction_state($id, 'failed', "Failed to store donation in database");
            log_message("Transaction $signature processed but failed to store in donations and is now marked as failed.");
        }
    } elseif ($process_result['status'] === 'pending') {
        // Check if it's the last attempt; if so, mark as failed
        if ($process_result['final_attempt']) {
            update_transaction_state($id, 'failed', "Exceeded retry attempts: " . $process_result['message']);
            log_message("Transaction $signature exceeded retries and is marked as failed: " . $process_result['message']);
        } else {
            update_transaction_state($id, 'pending', $process_result['message']);
            log_message("Transaction $signature is still pending: " . $process_result['message']);
        }
    } else {
        // Final failure, mark as "failed"
        update_transaction_state($id, 'failed', $process_result['message']);
        log_message("Transaction $signature could not be processed and is marked as failed: " . $process_result['message']);
    }
}

// Output all log messages to the browser
echo "<pre>" . implode("\n", $log_messages) . "</pre>";

function process_transaction($signature, $attempts = 6, $delay = 10) {
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

        // Check for connection errors
        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            curl_close($ch);
            return [
                "status" => "pending",
                "message" => "Failed to connect to API: $error_message",
                "final_attempt" => ($i == $attempts - 1)
            ];
        }

        curl_close($ch);
        $decoded_response = json_decode($response, true);

        // Check if the response contains a valid result
        if (isset($decoded_response['result'])) {
            $tx_details = $decoded_response['result'];
            if (is_valid_transaction($tx_details)) {
                return [
                    "status" => "success",
                    "message" => "Transaction validated and ready for storage",
                    "data" => $tx_details // Include data to store in donations table
                ];
            } else {
                return ["status" => "failed", "message" => "Transaction details invalid"];
            }
        }

        // Retry if not the last attempt
        if ($i < $attempts - 1) {
            sleep($delay);
        }
    }

    // All attempts failed, return final failure
    return [
        "status" => "failed",
        "message" => "Failed to fetch transaction details after multiple attempts",
        "final_attempt" => true
    ];
}

function is_valid_transaction($tx_details) {
    global $db;

    $instructions = $tx_details['transaction']['message']['instructions'] ?? [];
    foreach ($instructions as $instruction) {
        if (isset($instruction['parsed']['info']['authority'])) {
            $owner_account = $instruction['parsed']['info']['authority'];
            $user_id = get_user_id_from_wallet($owner_account);
            $expected_wallet_address = get_wallet_address_from_user_id($user_id);
            if ($owner_account === $expected_wallet_address) {
                return true;
            }
        }
    }
    return false;
}

function store_donation($process_result, $action) {
    global $db;
    $tx_data = $process_result['data'];

    // Debugging output for accountKeys and signatures
    log_message("Debug: Transaction data for accountKeys and signatures:");
    log_message("Account Keys: " . print_r($tx_data['transaction']['message']['accountKeys'], true));
    log_message("Signature: " . print_r($tx_data['transaction']['signatures'], true));

    // Fetch the user_id based on the wallet address in transaction data
    $wallet_address = $tx_data['transaction']['message']['accountKeys'][0]['pubkey'] ?? null;
    if (!$wallet_address) {
        log_message("Debug: Wallet address is missing in transaction data.");
        return false;
    }
    log_message("Debug: Wallet address from transaction data: $wallet_address");

    $user_id = get_user_id_from_wallet($wallet_address);
    if (!$user_id) {
        log_message("Debug: No matching user_id found for wallet address $wallet_address.");
        return false;
    }

    $donation_amount = calculate_donation_amount($tx_data);

    // Check if donation amount is correctly calculated
    if (!$donation_amount) {
        log_message("Debug: Donation amount is zero or not found.");
        return false;
    }

    // Insert into donations if user_id and donation_amount are valid
    try {
        $stmt = $db->prepare("INSERT INTO donations (user_id, signature, donation_amount, action, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $success = $stmt->execute([$user_id, $tx_data['transaction']['signatures'][0] ?? '', $donation_amount, $action]);

        if (!$success) {
            $errorInfo = $stmt->errorInfo();
            log_message("Debug: Failed to execute insert into donations - " . $errorInfo[2]);
        }

        return $success;
    } catch (PDOException $e) {
        log_message("Debug: Exception during insert into donations - " . $e->getMessage());
        return false;
    }
}

function calculate_donation_amount($tx_data) {
    $instructions = $tx_data['transaction']['message']['instructions'] ?? [];
    foreach ($instructions as $instruction) {
        if ($instruction['programId'] === SPL_TOKEN_PROGRAM_ID) {
            $parsedInstruction = $instruction['parsed'] ?? null;
            if ($parsedInstruction && $parsedInstruction['type'] === 'burnChecked') {
                $info = $parsedInstruction['info'];
                $tokenAmount = $info['tokenAmount'];
                $amount = $tokenAmount['amount'];
                $decimals = $tokenAmount['decimals'];
                return $amount / pow(10, $decimals);
            }
        }
    }
    return 0;
}

function get_user_id_from_wallet($wallet_address) {
    global $db;
    $stmt = $db->prepare("SELECT user_id FROM users WHERE wallet_address = ?");
    $stmt->execute([$wallet_address]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['user_id'] : null;
}

function get_wallet_address_from_user_id($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT wallet_address FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['wallet_address'] : null;
}

function update_transaction_state($id, $state, $reason) {
    global $db;
    $stmt = $db->prepare("UPDATE failed_transactions SET state = ?, reason = ?, timestamp = NOW() WHERE id = ?");
    $stmt->execute([$state, $reason, $id]);
}
?>
