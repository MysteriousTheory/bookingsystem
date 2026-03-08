<?php
// Database configuration settings

$host = 'localhost';
$username = 'your_username';
$password = 'your_password';
database = 'your_database';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
?>
