<?php
include 'dbc.php';
session_start();

if (!isset($_GET['bookingId'])) {
    echo "<script>alert('Invalid access.');</script>";
    echo "<script>window.location.href='home3.php';</script>";
    exit();
}

$bookingId = $_GET['bookingId'];

// Fetch booking details
$query = "SELECT * FROM bhotal WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<script>alert('Booking not found.');</script>";
    echo "<script>window.location.href='home3.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardNumber = $_POST['card_number'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv'];

    // Fake payment logic (validate card details)
    if (!empty($cardNumber) && !empty($expiry) && !empty($cvv)) {
        // Update booking status
        $updateQuery = "UPDATE bhotal SET status = 'Confirmed' WHERE id = ?";
        $updateStmt = $con->prepare($updateQuery);
        $updateStmt->bind_param("i", $bookingId);
        if ($updateStmt->execute()) {
            echo "<script>alert('Payment successful. Booking confirmed!');</script>";
            echo "<script>window.location.href='home3.php';</script>";
        } else {
            echo "<script>alert('Payment failed. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid payment details.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Page</title>
</head>
<body>
    <h1>Payment for Booking</h1>
    <p>Hotel: <?php echo htmlspecialchars($booking['hotel']); ?></p>
    <p>Price: ₹<?php echo htmlspecialchars($booking['price']); ?></p>

    <form method="POST">
        <label for="card_number">Card Number:</label>
        <input type="text" name="card_number" id="card_number" required><br>

        <label for="expiry">Expiry Date:</label>
        <input type="text" name="expiry" id="expiry" placeholder="MM/YY" required><br>

        <label for="cvv">CVV:</label>
        <input type="text" name="cvv" id="cvv" required><br>

        <button type="submit">Pay ₹<?php echo htmlspecialchars($booking['price']); ?></button>
    </form>
</body>
</html>
