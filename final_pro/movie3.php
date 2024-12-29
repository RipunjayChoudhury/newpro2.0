<?php
// Start session
session_start();
include 'dbc.php'; // Include your database connection file

if (!isset($_SESSION['name'])) {
    echo "<script>alert('Please log in to book a movie.');</script>";
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Get user details from the users table
$userEmail = $_SESSION['name'];
$userQuery = $con->prepare("SELECT * FROM users WHERE email = ?");
$userQuery->bind_param("s", $userEmail);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();
if (!$userData) {
    echo "<script>alert('User not found. Please log in again.');</script>";
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Initialize variables
$locations = [];
$halls = [];
$movies = [];
$selected_location = '';
$selected_hall = '';
$selected_movie = '';


// Fetch locations
$locations_result = $con->query("SELECT * FROM mlocation");
while ($row = $locations_result->fetch_assoc()) {
    $locations[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['location_id'])) {
        $selected_location = $_POST['location_id'];
        // Fetch halls for the selected location
        $halls_result = $con->query("SELECT * FROM movie_hall WHERE location_id = $selected_location");
        while ($row = $halls_result->fetch_assoc()) {
            $halls[] = $row;
        }
    }

    if (isset($_POST['hall_id'])) {
        $selected_hall = $_POST['hall_id'];
        // Fetch movies for the selected hall
        $movies_result = $con->query("SELECT * FROM movie WHERE hall_id = $selected_hall");
        while ($row = $movies_result->fetch_assoc()) {
            $movies[] = $row;
        }
    }

    if (isset($_POST['movie_id']) && isset($_POST['seats_booked'])) {
        $selected_movie = $_POST['movie_id'];
        $seats_booked = $_POST['seats_booked'];
        $price = $_POST['price'];
        $booking_date = date('Y-m-d'); // Get the current date

        // Fetch location name and hall name
        $location_name = '';
        $hall_name = '';
        $movie_name = ''; // To store the fetched movie name

        $location_query = $con->prepare("SELECT location FROM mlocation WHERE id = ?");
        $location_query->bind_param("i", $selected_location);
        $location_query->execute();
        $location_result = $location_query->get_result();
        if ($location_row = $location_result->fetch_assoc()) {
            $location_name = $location_row['location'];
        }

        $hall_query = $con->prepare("SELECT hall_name FROM movie_hall WHERE id = ?");
        $hall_query->bind_param("i", $selected_hall);
        $hall_query->execute();
        $hall_result = $hall_query->get_result();
        if ($hall_row = $hall_result->fetch_assoc()) {
            $hall_name = $hall_row['hall_name'];
        }

        // Fetch movie name
        $movie_query = $con->prepare("SELECT name FROM movie WHERE id = ?");
        $movie_query->bind_param("i", $selected_movie);
        $movie_query->execute();
        $movie_result = $movie_query->get_result();
        if ($movie_row = $movie_result->fetch_assoc()) {
            $movie_name = $movie_row['name'];
        }

        // Store booking details in the database
        $stmt = $con->prepare("INSERT INTO mbookings (user_email, hall_name, location_name, mname, seat_no, price, status, b_date) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
        $stmt->bind_param("sssssis", $userData['email'], $hall_name, $location_name, $movie_name, $seats_booked, $price, $booking_date);
        $stmt->execute();

        // Get the last inserted booking ID
        $booking_id = $stmt->insert_id;

        // Redirect to payment page with the booking details
        $_SESSION['booking_id'] = $booking_id;
        $_SESSION['price'] = $price;
        header("Location: moviepayment.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Booking System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        select, input, button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            max-width: 300px;
        }
        .form-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($userData['fname']); ?></h1>
    <h2>Movie Booking System</h2>

    <!-- Location Selection -->
    <form method="POST">
        <div class="form-section">
            <label for="location">Select Location:</label>
            <select name="location_id" id="location" onchange="this.form.submit()">
                <option value="">--Select Location--</option>
                <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['id'] ?>" <?= $selected_location == $location['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($location['location']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <!-- Hall Selection -->
    <?php if (!empty($halls)): ?>
        <form method="POST">
            <div class="form-section">
                <label for="hall">Select Hall:</label>
                <input type="hidden" name="location_id" value="<?= $selected_location ?>">
                <select name="hall_id" id="hall" onchange="this.form.submit()">
                    <option value="">--Select Hall--</option>
                    <?php foreach ($halls as $hall): ?>
                        <option value="<?= $hall['id'] ?>" <?= $selected_hall == $hall['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($hall['hall_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

    <?php endif; ?>

    <!-- Movie Selection -->
    <?php if (!empty($movies)): ?>
        <form method="POST">
            <div class="form-section">
                <label for="movie">Select Movie:</label>
                <input type="hidden" name="location_id" value="<?= $selected_location ?>">
                <input type="hidden" name="hall_id" value="<?= $selected_hall ?>">
                <select name="movie_id" id="movie">
                    <option value="">--Select Movie--</option>
                    <?php foreach ($movies as $movie): ?>
                        <option value="<?= $movie['id'] ?>"><?= htmlspecialchars($movie['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <label for="seats_booked">Seat to Book:</label>
            <input type="number" name="seats_booked" min="1" id="seats_booked" required>
            <label for="price">Price:</label>
            <input type="number" name="price" id= "price">
            <button type="submit">Book Now</button>
        </form>

    <?php endif; ?>

    <script>
        const seatsBookedInput = document.getElementById('seats_booked');
        const totalPriceInput = document.getElementById('price');

        seatsBookedInput.addEventListener('input', () => {
            const seatsBooked = parseInt(seatsBookedInput.value);
            let totalPrice = 0;

            if (seatsBooked <= 20) {
                totalPrice =  100;
            } else if (seatsBooked > 20 && seatsBooked <= 40) {
                totalPrice = 200;
            } else {
                // Handle invalid seat count (optional)
                totalPrice = 0; 
            }

            totalPriceInput.value = totalPrice;
        });
    </script>

</body>
</html>
