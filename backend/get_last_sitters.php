<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();

try {
    // Query to get the usernames of the last 5 donations
    $stmt = $db->prepare("
        SELECT u.username
        FROM donations d
        JOIN users u ON d.user_id = u.user_id
        ORDER BY d.timestamp DESC
        LIMIT 5
    ");
    $stmt->execute();
    $lastSitters = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch only the username column

    if ($lastSitters) {
        echo json_encode([
            "status" => "success",
            "last_sitters" => $lastSitters
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No recent donations found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
