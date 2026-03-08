<?php
// Secure deletion logic in PHP
function delete_booking($user_id, $booking_id) {
    // Verify user ownership of the booking
    $booking = fetch_booking($booking_id);
    if (!$booking || $booking['user_id'] !== $user_id) {
        return 'You do not have permission to delete this booking.';
    }

    // Proceed with deletion
    $result = perform_deletion($booking_id);
    return $result ? 'Booking deleted successfully.' : 'Failed to delete booking.';
}

// Fetch booking by ID (this is a placeholder)
function fetch_booking($booking_id) {
    // Normally, this would query the database
    return ['user_id' => 1]; // Simulating booking ownership
}

// Perform the actual deletion (this is a placeholder)
function perform_deletion($booking_id) {
    // Simulating deletion process
    return true; // Simulating successful deletion
}
?>