<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();

try {
    // Query to get the sum of all donations
    $stmt = $db->prepare("
        SELECT SUM(donation_amount) AS total_donations
        FROM donations
    ");

    $stmt->execute();
    $totalDonations = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($totalDonations && $totalDonations['total_donations'] !== null) {
        echo json_encode([
            "status" => "success",
            "total_donations" => $totalDonations['total_donations']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No donations found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}

