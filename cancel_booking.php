<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please login first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_reference = trim($_POST['booking_reference'] ?? '');
    $user_id = $_SESSION['user_id'];
    $redirect_dashboard = $_POST['redirect_dashboard'] ?? null;

    if (empty($booking_reference)) {
        die("Invalid input data.");
    }

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ? AND user_id = ?");
    $stmt->execute([$booking_reference, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        die("Booking not found or you don't have permission to modify it.");
    }

    if ($booking['status'] === 'CANCELLED') {
        die("Booking is already cancelled.");
    }

    $updateStmt = $pdo->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE booking_reference = ? AND user_id = ?");
    $result = $updateStmt->execute([$booking_reference, $user_id]);

    if ($result) {
        if ($redirect_dashboard) {
            header("Location: dashboard.php?msg=cancelled");
        } else {
            header("Location: confirmation.php?id=" . urlencode($booking_reference) . "&msg=cancelled");
        }
        exit;
    } else {
        die("Failed to cancel booking.");
    }
}
?>
