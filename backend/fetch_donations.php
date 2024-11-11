<?php
include 'db_connection.php';
include 'local_development.php';

header("Content-Type: application/json");

$db = connect_db();
$fetchTimeLimit = '24 HOUR'; // Limit donations within the last 24 hours

// Fetch donations within the last 24 hours
$stmt = $db->prepare("SELECT donation_amount, action, timestamp FROM donations WHERE timestamp >= NOW() - INTERVAL $fetchTimeLimit");
$stmt->execute();
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["status" => "success", "donations" => $donations]);
