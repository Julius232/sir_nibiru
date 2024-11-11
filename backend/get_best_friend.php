<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();

try {
    // Query to get the user with the highest single donation
    $stmt = $db->prepare("
        SELECT u.username, d.donation_amount AS topDonation
        FROM donations d
        JOIN users u ON d.user_id = u.user_id 
        ORDER BY d.donation_amount DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $bestFriend = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($bestFriend) {
        echo json_encode([
            "status" => "success",
            "username" => $bestFriend['username'],
            "topDonation" => $bestFriend['topDonation'] // Use the single highest donation amount
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No donations found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
