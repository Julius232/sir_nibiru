<?php
include 'db_connection.php';
include 'local_development.php';
header("Content-Type: application/json");

$db = connect_db();

// Get the 'period' parameter from the query string
$period = isset($_GET['period']) ? $_GET['period'] : 'monthly';

try {
    if ($period === 'monthly') {
        // Get the first and last day of the current month
        $firstDayOfMonth = date('Y-m-01 00:00:00');
        $lastDayOfMonth = date('Y-m-t 23:59:59');

        // Query to get the top 10 donors for the current month
        $stmt = $db->prepare("
            SELECT u.username, SUM(d.donation_amount) AS total_donations
            FROM donations d
            JOIN users u ON d.user_id = u.user_id
            WHERE d.timestamp BETWEEN :first_day AND :last_day
            GROUP BY u.user_id
            ORDER BY total_donations DESC
            LIMIT 10
        ");
        $stmt->bindParam(':first_day', $firstDayOfMonth);
        $stmt->bindParam(':last_day', $lastDayOfMonth);
    } else {
        // Default to all-time top donors
        $stmt = $db->prepare("
            SELECT u.username, SUM(d.donation_amount) AS total_donations
            FROM donations d
            JOIN users u ON d.user_id = u.user_id
            GROUP BY u.user_id
            ORDER BY total_donations DESC
            LIMIT 10
        ");
    }

    $stmt->execute();
    $highscoreList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($highscoreList) {
        echo json_encode([
            "status" => "success",
            "highscore" => $highscoreList
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No donations found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
