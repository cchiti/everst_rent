<?php
session_start();  // Make sure this is at the very beginning

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page with the return_url parameter
    header("Location: php/login.php?return_url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

include 'php/header.php'; 
include 'php/db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Check if the form is submitted and car_id is present
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['car_id'])) {
    $car_id = intval($_POST['car_id']);
    $user_id = $_SESSION['user_id'];  // Assuming the user is logged in
    $booking_date = date('Y-m-d');  // Set the current date as the booking date

    // Insert booking details into the database
    $query = "INSERT INTO bookings (vehicle_id, user_id, booking_date, status) VALUES (?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iis', $car_id, $user_id, $booking_date);
    
    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;  // Get the booking ID for confirmation
        ?>
        <div class="confirmation">
            <h2>Booking Confirmed!</h2>
            <p>Your booking has been successfully placed for the vehicle ID: <?php echo $car_id; ?>.</p>
            <p>Booking ID: <?php echo $booking_id; ?></p>
            <p>Status: Pending</p>
            <p>We will notify you once your booking is confirmed.</p>
            <a href="php/booking_history.php" class="btn">Go to Booking History</a>
        </div>
        <?php
    } else {
        echo "<p>Error processing the booking. Please try again later.</p>";
    }

    $stmt->close();
} else {
    echo "<p>Invalid request. Please go back and try again.</p>";
}

include 'php/footer.php';
?>

<style>
    /* Confirmation Page */
.confirmation {
    text-align: center;
    margin: 50px auto;
    padding: 30px;
    background-color: #f9f9f9;
    border-radius: 8px;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.confirmation h2 {
    font-size: 24px;
    color: #28a745;
    margin-bottom: 20px;
}

.confirmation p {
    font-size: 16px;
    margin: 10px 0;
}

.confirmation .btn {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;
}

.confirmation .btn:hover {
    background-color: #0056b3;
}

</style>