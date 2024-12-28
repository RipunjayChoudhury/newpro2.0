<?php
session_start();
include 'dbc.php'; // Include your database connection file

if (!isset($_SESSION['booking_id'])) {
    echo "<script>alert('Invalid booking.');</script>";
    echo "<script>window.location.href='home3php';</script>";
    exit();
}

$booking_id = $_SESSION['booking_id'];

// Simulate a successful payment process
$stmt = $con->prepare("UPDATE mbookings SET status = 'Confirmed' WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();

// Redirect to a success page
echo "<script>alert('Payment successful! Your booking is confirmed.');</script>";
echo "<script>window.location.href='booking_success.php';</script>";
exit();
?>
