<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please login first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = trim($_POST['passenger_name'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $departure_date = trim($_POST['departure_date'] ?? '');
    $passengers = (int)($_POST['passengers'] ?? 1);
    $user_id = $_SESSION['user_id'];

    if (empty($passenger_name) || empty($origin) || empty($destination) || empty($departure_date) || $passengers < 1) {
        die("Invalid input data.");
    }

    $booking_reference = 'BK' . strtoupper(substr(uniqid(), -6));
    
    $stmt = $pdo->prepare("INSERT INTO bookings (booking_reference, user_id, passenger_name, origin, destination, departure_date, passengers, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE')");
    $result = $stmt->execute([$booking_reference, $user_id, $passenger_name, $origin, $destination, $departure_date, $passengers]);

    if ($result) {
        header("Location: confirmation.php?id=" . urlencode($booking_reference));
        exit;
    } else {
        die("Failed to create booking.");
    }
}
?>
