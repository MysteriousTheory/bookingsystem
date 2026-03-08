<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FlightBookingSystem</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 900px;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .action-btns form {
            display: inline;
        }
        .action-btns .btn {
            padding: 6px 12px;
            font-size: 14px;
            display: inline-block;
            width: auto;
            margin: 2px;
        }
        .welcome-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            th { display: none; }
            td { position: relative; padding-left: 50%; text-align: right; }
            td:before { content: attr(data-label); position: absolute; left: 15px; width: 45%; padding-right: 10px; text-align: left; font-weight: bold; }
        }
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <div class="welcome-hero">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <a href="logout.php" class="btn btn-secondary" style="width: auto;">Logout</a>
        </div>
        
        <p style="text-align: left;">Manage all your upcoming flights.</p>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
            <p style="color: #dc3545; font-weight: bold;">Booking successfully cancelled.</p>
        <?php endif; ?>

        <div style="margin-bottom: 20px;">
            <a href="book.php" class="btn" style="display: inline-block; width: auto;">+ Add New Booking</a>
        </div>

        <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Date</th>
                        <th>Pax</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td data-label="Booking ID"><strong><?= htmlspecialchars($b['booking_reference']) ?></strong></td>
                            <td data-label="Origin"><?= htmlspecialchars($b['origin']) ?></td>
                            <td data-label="Destination"><?= htmlspecialchars($b['destination']) ?></td>
                            <td data-label="Date"><?= htmlspecialchars($b['departure_date']) ?></td>
                            <td data-label="Pax"><?= htmlspecialchars($b['passengers']) ?></td>
                            <td data-label="Status"><span class="status-<?= strtolower($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                            <td data-label="Actions" class="action-btns">
                                <?php if ($b['status'] !== 'CANCELLED'): ?>
                                    <a href="reschedule.php?id=<?= urlencode($b['booking_reference']) ?>" class="btn btn-secondary">Reschedule</a>
                                    <form action="cancel_booking.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="booking_reference" value="<?= htmlspecialchars($b['booking_reference']) ?>">
                                        <input type="hidden" name="redirect_dashboard" value="1">
                                        <button type="submit" class="btn btn-danger">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                <p>You have no flight bookings yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
