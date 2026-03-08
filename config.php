<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Add your password if necessary
$dbname = "bookingsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();
?>