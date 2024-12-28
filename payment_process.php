<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    $cardNumber = $_POST['card_number'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv'];

    // Simulate payment processing
    $isPaymentSuccessful = true; // Set to `false` to simulate a failed payment

    if ($isPaymentSuccessful) {
        echo "<script>alert('Payment Successful! Your booking is confirmed.');</script>";
        echo "<script>window.location.href='home3.php';</script>";
    } else {
        echo "<script>alert('Payment Failed. Please try again.');</script>";
        echo "<script>window.location.href='payment.php?booking_id=$bookingId';</script>";
    }
    exit();
}
?>
