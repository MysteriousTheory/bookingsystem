<?php
// Include database connection
include 'db_connection.php';

// Fetch all bookings from the database
$query = "SELECT * FROM bookings";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>User</th><th>Booking Details</th><th>Status</th><th>Actions</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["user"] . "</td><td>" . $row["details"] . "</td><td>" . $row["status"] . "</td>";
        echo "<td>\n                <a href='edit_booking.php?id=" . $row["id"] . "'>Edit</a> | \n                <a href='delete_booking.php?id=" . $row["id"] . "'>Delete</a> | \n                <a href='update_status.php?id=" . $row["id"] . "'>Update Status</a>\n              </td></tr>";
    }
    echo "</table>";
} else {
    echo "No bookings available.";
}

// Close connection
$conn->close();
?>