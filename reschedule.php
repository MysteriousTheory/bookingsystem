<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$booking_reference = $_GET['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Booking - FlightBookingSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Reschedule Flight</h1>
        <p>Need a change of plans? Update your flight date here.</p>
        
        <form action="reschedule_booking.php" method="POST">
            <div class="form-group">
                <label for="booking_reference">Booking Reference ID</label>
                <input type="text" id="booking_reference" name="booking_reference" required placeholder="e.g. BK123456" value="<?= htmlspecialchars($booking_reference) ?>" <?= !empty($booking_reference) ? 'readonly' : '' ?>>
            </div>
            
            <div class="form-group">
                <label for="new_date">New Departure Date</label>
                <input type="date" id="new_date" name="new_date" required>
            </div>
            
            <button type="submit" class="btn btn-secondary">Reschedule Booking</button>
            <div style="text-align: center;">
                <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
