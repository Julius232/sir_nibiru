<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();

try {
    $stmt = $db->query("SELECT config_key, config_value FROM config");
    $config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Fetch as key-value pairs

    echo json_encode(["status" => "success", "config" => $config]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to fetch config", "error" => $e->getMessage()]);
}
?>
