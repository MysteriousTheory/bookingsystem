<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please login first.");
}

$booking_reference = $_GET['id'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($booking_reference)) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>Invalid Booking Reference. <a href='dashboard.php'>Dashboard</a></div>");
}

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ? AND user_id = ?");
$stmt->execute([$booking_reference, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>Booking not found or access denied. <a href='dashboard.php'>Dashboard</a></div>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - FlightBookingSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Booking Details</h1>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'rescheduled'): ?>
            <p style="color: #28a745; font-weight: bold;">Your booking has been successfully rescheduled!</p>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
            <p style="color: #dc3545; font-weight: bold;">Your booking has been successfully cancelled.</p>
        <?php else: ?>
            <p style="color: #28a745; font-weight: bold;">Booking confirmed successfully!</p>
        <?php endif; ?>
        
        <div class="confirmation-box">
            <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking['booking_reference']) ?></p>
            <p><strong>Passenger Name:</strong> <?= htmlspecialchars($booking['passenger_name']) ?></p>
            <p><strong>Route:</strong> <?= htmlspecialchars($booking['origin']) ?> &rarr; <?= htmlspecialchars($booking['destination']) ?></p>
            <p><strong>Departure Date:</strong> <?= htmlspecialchars($booking['departure_date']) ?></p>
            <p><strong>Passengers:</strong> <?= htmlspecialchars($booking['passengers']) ?></p>
            <p><strong>Status:</strong> <span class="status-<?= strtolower($booking['status']) ?>"><?= htmlspecialchars($booking['status']) ?></span></p>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php" class="btn">Return to Dashboard</a>
        </div>
    </div>
</body>
</html>
