<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - FlightBookingSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cancel Flight</h1>
        <p>We're sorry to see you go. Enter your booking ID to cancel.</p>
        
        <form action="cancel_booking.php" method="POST">
            <div class="form-group">
                <label for="booking_reference">Booking Reference ID</label>
                <input type="text" id="booking_reference" name="booking_reference" required placeholder="e.g. BK123456">
            </div>
            
            <button type="submit" class="btn btn-danger">Cancel Booking</button>
            <div style="text-align: center;">
                <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
