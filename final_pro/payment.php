<?php
include 'dbc.php';
session_start();

if (!isset($_GET['booking_id'])) {
    echo "<script>alert('Invalid access.');</script>";
    echo "<script>window.location.href='hotel_booking.php';</script>";
    exit();
}

$bookingId = $_GET['booking_id'];

// Fetch booking details
$query = "SELECT * FROM bhotal WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<script>alert('Booking not found.');</script>";
    echo "<script>window.location.href='hotel_booking.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate payment processing
    $cardNumber = $_POST['card_number'];
    $expiryDate = $_POST['expiry'];
    $cvv = $_POST['cvv'];

    // Update booking status to 'Confirmed'
    $updateQuery = "UPDATE bhotal SET status = 'Confirmed' WHERE id = ?";
    $updateStmt = $con->prepare($updateQuery);
    $updateStmt->bind_param("i", $bookingId);

    if ($updateStmt->execute()) {
        echo "<script>alert('Payment successful! Booking confirmed.');</script>";
        echo "<script>window.location.href='home3.php';</script>";
        exit();
    } else {
        echo "<script>alert('Payment failed. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
</head>
<body>
    <h1>Payment Page</h1>
    <p>Booking Details:</p>
    <ul>
        <li>Hotel: <?= htmlspecialchars($booking['hotel']); ?></li>
        <li>Room Type: <?= htmlspecialchars($booking['roomtype']); ?></li>
        <li>Price: $<?= number_format($booking['price'], 2); ?></li>
    </ul>

    <form method="POST">
        <label for="card_number">Card Number:</label>
        <input type="text" name="card_number" id="card_number" required><br>

        <label for="expiry">Expiry Date:</label>
        <input type="text" name="expiry" id="expiry" placeholder="MM/YY" required><br>

        <label for="cvv">CVV:</label>
        <input type="text" name="cvv" id="cvv" required><br>

        <button type="submit">Pay Now</button>
    </form>
</body>
</html>
