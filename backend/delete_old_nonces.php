<?php
include 'db_connection.php';

$db = connect_db();

// Calculate the datetime 7 days ago
$seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

// Prepare and execute the SQL statement
$stmt = $db->prepare("DELETE FROM nonces WHERE created_at < ?");
$stmt->execute([$seven_days_ago]);

echo "Old nonces deleted successfully.";
?>
