<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create DB if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ticket_system");
    echo "Database established.\n";
    
    // Use DB
    $pdo->exec("USE ticket_system");
    
    // Drop existing tables to recreate them with relations
    $pdo->exec("DROP TABLE IF EXISTS bookings");
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "Old tables dropped.\n";

    // Create Users Schema
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_users);
    echo "Users table created.\n";

    // Create Bookings Schema
    $sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_reference VARCHAR(20) NOT NULL UNIQUE,
        user_id INT NOT NULL,
        passenger_name VARCHAR(100) NOT NULL,
        origin VARCHAR(100) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        departure_date DATE NOT NULL,
        passengers INT NOT NULL,
        status ENUM('ACTIVE', 'RESCHEDULED', 'CANCELLED') DEFAULT 'ACTIVE',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_bookings);
    echo "Bookings table created.\n";
    
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
