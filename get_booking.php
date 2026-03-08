<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$booking_reference = $_GET['id'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($booking_reference)) {
    echo json_encode(['error' => 'No booking reference provided']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, booking_reference, passenger_name, origin, destination, departure_date, passengers, status, created_at FROM bookings WHERE booking_reference = ? AND user_id = ?");
$stmt->execute([$booking_reference, $user_id]);
$booking = $stmt->fetch();

if ($booking) {
    echo json_encode(['success' => true, 'booking' => $booking]);
} else {
    echo json_encode(['success' => false, 'error' => 'Booking not found or access denied']);
}
?>
