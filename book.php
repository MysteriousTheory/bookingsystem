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
    <title>Book a Flight - FlightBookingSystem</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Book Your Flight</h1>
        <p>Enter your details below to reserve your seat.</p>
        
        <form action="book_flight.php" method="POST">
            <div class="form-group">
                <label for="passenger_name">Passenger Name</label>
                <input type="text" id="passenger_name" name="passenger_name" required placeholder="John Doe">
            </div>
            
            <div class="form-group">
                <label for="origin">Origin (Departure City)</label>
                <input type="text" id="origin" name="origin" required placeholder="New York">
            </div>
            
            <div class="form-group">
                <label for="destination">Destination</label>
                <input type="text" id="destination" name="destination" required placeholder="Japan">
            </div>
            
            <div class="form-group">
                <label for="departure_date">Departure Date</label>
                <input type="date" id="departure_date" name="departure_date" required>
            </div>
            
            <div class="form-group">
                <label for="passengers">Number of Passengers</label>
                <input type="number" id="passengers" name="passengers" min="1" max="10" value="1" required>
            </div>
            
            <button type="submit" class="btn">Confirm Booking</button>
            <div style="text-align: center;">
                <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
