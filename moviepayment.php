<?php
session_start();
include 'dbc.php';

if (!isset($_SESSION['booking_id'])) {
    echo "<script>alert('No booking found. Please try again.');</script>";
    echo "<script>window.location.href='movie3.php';</script>";
    exit();
}

$booking_id = $_SESSION['booking_id'];

// Fetch booking details
$bookingQuery = $con->prepare("SELECT * FROM mbookings WHERE id = ?");
$bookingQuery->bind_param("i", $booking_id);
$bookingQuery->execute();
$bookingResult = $bookingQuery->get_result();
$bookingData = $bookingResult->fetch_assoc();

if (!$bookingData) {
    echo "<script>alert('Invalid booking ID.');</script>";
    echo "<script>window.location.href='movie3.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate payment processing
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];

    // Update booking status to 'Confirmed'
    $updateBooking = $con->prepare("UPDATE mbookings SET status = ? WHERE id = ?");
    $status = "Confirmed";
    $updateBooking->bind_param("si", $status, $booking_id);

    if ($updateBooking->execute()) {
        echo "<script>alert('Payment successful! Booking confirmed.');</script>";
        echo "<script>window.location.href='home3.php';</script>";
        exit();
    } else {
        echo "<script>alert('Payment failed. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fake Payment</title>
</head>
<body>
    <h1>Payment Page</h1>
    <p>Booking Details:</p>
    <ul>
        <li>Movie: <?= htmlspecialchars($bookingData['id']); // Replace with movie name ?></li>
        <li>Total Price: $<?= number_format($bookingData['price'], 2); ?></li>
    </ul>

    <form method="POST">
        <label for="card_number">Card Number:</label>
        <input type="text" name="card_number" id="card_number" required>
        <br>
        <label for="expiry">Expiry Date:</label>
        <input type="text" name="expiry" id="expiry" placeholder="MM/YY" required><br>

        <br>
        <label for="cvv">CVV:</label>
        <input type="text" name="cvv" id="cvv" required>
        <br>
        <button type="submit">Pay Now</button>
    </form>
</body>
</html>
