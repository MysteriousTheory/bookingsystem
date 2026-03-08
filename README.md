# FlightBookingSystem

A simple, responsive, and robust airplane ticket booking web application built with a modern UI design and a secure PHP/MySQL backend.

## Features

- **User Authentication**: Secure registration, login, and logout functionality using PHP Sessions and `password_hash()`.
- **Flight Booking**: Users can easily book new flights by providing passenger details, origin, destination, and departure date.
- **User Dashboard**: A personalized dashboard where authenticated users can view, manage, and track the status of all their booked flights.
- **Flight Management**: Users can directly reschedule or cancel their active flights.
- **Security**: The system enforces authorization checks to ensure users can only view and modify their own bookings.
- **Modern UI**: A premium, clean, and responsive design utilizing CSS glassmorphism, smooth animations, and the Inter font family.

## Tech Stack

- **Frontend**: HTML5, CSS3 (Custom responsive styling, no external CSS frameworks)
- **Backend**: Plain PHP (No frameworks used, procedural/vanilla approach)
- **Database**: MySQL (Accessed securely via PHP Data Objects - PDO)
- **Server Environment**: Designed for local development using XAMPP (Apache + MySQL), but can be deployed to any standard LAMP/WAMP stack.

## Prerequisites

To run this application locally, you will need:
- A web server stack like **XAMPP**, **WAMP**, or **MAMP** containing:
  - PHP 7.4 or higher
  - MySQL 5.7+ or MariaDB

## Setup Instructions

### 1. Download and Extract
Clone or download the project files into your local local web server directory. 
If you are using XAMPP on Windows, place the project folder (`bksystem`) inside `C:\xampp\htdocs\`.
The path should look like: `C:\xampp\htdocs\bksystem`.

### 2. Start the Servers
Open your XAMPP Control Panel and start both the **Apache** and **MySQL** services.

### 3. Database Initialization
This application includes an automated setup script to instantly establish the correct database schema.
1. Open your web browser.
2. Navigate to: `http://localhost/bksystem/setup_db.php`
3. The page will display confirmation messages indicating that the `ticket_system` database and its tables (`users` and `bookings`) were successfully created.

*(Optional: Manual Database Setup)*
If you prefer to configure the database manually:
- Open phpMyAdmin (`http://localhost/phpmyadmin`).
- Import the provided `schema.sql` file located in the root directory.

### 4. Database Configuration (Optional)
By default, the application is configured to connect to a default local MySQL server (username: `root`, no password).
If your local server has a different configuration, open `db.php` and update the connection credentials:
```php
$host = '127.0.0.1';
$db   = 'ticket_system';
$user = 'root'; // Update if you have a specific user
$pass = '';     // Update if you have a MySQL password
```

### 5. Access the Application
You are all set! Open your browser and navigate to the application's home page:
`http://localhost/bksystem/index.php`

Create a new account, log in, and start exploring the FlightBookingSystem!

## Database Schema Details

The application uses two primary tables within the `ticket_system` database:

### Table: `users`
Stores registered user credentials.
- `id`: INT (Primary Key, Auto Increment)
- `username`: VARCHAR(100) (Unique)
- `password`: VARCHAR(255) (Hashed)
- `created_at`: TIMESTAMP

### Table: `bookings`
Stores all flight reservations and links them to the respective user.
- `id`: INT (Primary Key, Auto Increment)
- `booking_reference`: VARCHAR(20) (Unique ID, e.g., BK...)
- `user_id`: INT (Foreign Key referencing `users.id` with CASCADE deletion)
- `passenger_name`: VARCHAR(100)
- `origin`: VARCHAR(100)
- `destination`: VARCHAR(100)
- `departure_date`: DATE
- `passengers`: INT
- `status`: ENUM ('ACTIVE', 'RESCHEDULED', 'CANCELLED')
- `created_at`: TIMESTAMP
