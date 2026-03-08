<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please login first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_reference = trim($_POST['booking_reference'] ?? '');
    $new_date = trim($_POST['new_date'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($booking_reference) || empty($new_date)) {
        die("Invalid input data.");
    }

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ? AND user_id = ?");
    $stmt->execute([$booking_reference, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        die("Booking not found or you don't have permission to modify it.");
    }

    if ($booking['status'] !== 'ACTIVE' && $booking['status'] !== 'RESCHEDULED') {
        die("Only ACTIVE or already RESCHEDULED bookings can be rescheduled.");
    }

    $updateStmt = $pdo->prepare("UPDATE bookings SET departure_date = ?, status = 'RESCHEDULED' WHERE booking_reference = ? AND user_id = ?");
    $result = $updateStmt->execute([$new_date, $booking_reference, $user_id]);

    if ($result) {
        header("Location: confirmation.php?id=" . urlencode($booking_reference) . "&msg=rescheduled");
        exit;
    } else {
        die("Failed to reschedule booking.");
    }
}
?>
