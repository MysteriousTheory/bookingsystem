<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlightBookingSystem - Flight Booking System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to FlightBookingSystem</h1>
        <p>Your journey begins here. Fast, easy, and reliable flight bookings at your fingertips.</p>
        
        <div class="nav-links">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
                <a href="book.php" class="btn btn-secondary">Book a Flight</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login to Book</a>
                <a href="register.php" class="btn btn-secondary">Create Account</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
