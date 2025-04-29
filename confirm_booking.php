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
    // Validate required fields
    if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
        die("Error: Start date and end date are required.");
    }

    $car_id = intval($_POST['car_id']);
    $user_id = $_SESSION['user_id'];
    $booking_date = date('Y-m-d');
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = 'Pending';

    // Fetch daily rate of the vehicle
$rate_query = "SELECT daily_rate FROM vehicles WHERE id = ?";
$stmt = $conn->prepare($rate_query);
$stmt->bind_param('i', $car_id);
$stmt->execute();
$rate_result = $stmt->get_result();
if ($rate_result->num_rows > 0) {
    $rate_row = $rate_result->fetch_assoc();
    $daily_rate = floatval($rate_row['daily_rate']);
} else {
    die("Error: Vehicle not found.");
}
// Calculate number of days (inclusive of start & end)
$start = new DateTime($start_date);
$end = new DateTime($end_date);
$interval = $start->diff($end)->days + 1;

$total_cost = $daily_rate * $interval;



    // First, check if the vehicle is available for the selected dates
    $availability_query = "SELECT id FROM bookings 
                          WHERE vehicle_id = ? 
                          AND ((start_date <= ? AND end_date >= ?) 
                          OR (start_date <= ? AND end_date >= ?))";
    $stmt = $conn->prepare($availability_query);
    $stmt->bind_param('issss', $car_id, $end_date, $start_date, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='error-message'>";
        echo "<h2>Booking Conflict</h2>";
        echo "<p>This vehicle is already booked for the selected dates. Please choose different dates.</p>";
        echo "<a href='book_car.php?id=$car_id' class='btn'>Go Back</a>";
        echo "</div>";
        include 'php/footer.php';
        exit();
    }

    // Insert booking details into the database
    $query = "INSERT INTO bookings (vehicle_id, user_id, booking_date, start_date, end_date, status, total_cost) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('iissssd', $car_id, $user_id, $booking_date, $start_date, $end_date, $status, $total_cost);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;
        ?>
        <div class="confirmation">
            <h2>Booking Confirmed!</h2>
            <!-- <p>Your booking has been successfully placed for the vehicle ID: <?php echo $car_id; ?>.</p> -->
            <!-- <p>Booking ID: <?php echo $booking_id; ?></p> -->
            <p>Booking Dates: <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?></p>
            <p>Status: <?php echo $status; ?></p>
            <p>Total Cost: $<?php echo number_format($total_cost, 2); ?></p>
            <p>We will notify you once your booking is confirmed.</p>
            <a href="php/booking_history.php" class="btn">Go to Booking History</a>
        </div>

        <?php
    } else {
        echo "<div class='error-message'>";
        echo "<h2>Error Processing Booking</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
        echo "<a href='book_car.php?id=$car_id' class='btn'>Go Back</a>";
        echo "</div>";
    }

    $stmt->close();
} else {
    echo "<div class='error-message'>";
    echo "<h2>Invalid Request</h2>";
    echo "<p>Please go back and try again.</p>";
    echo "<a href='index.php' class='btn'>Return Home</a>";
    echo "</div>";
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
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;
    margin-top: 20px;
}

.confirmation .btn:hover {
    background-color: #0056b3;
}

.error-message {
    text-align: center;
    margin: 50px auto;
    padding: 30px;
    background-color: #f9f9f9;
    border-radius: 8px;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    color: #dc3545;
}

.error-message h2 {
    font-size: 24px;
    margin-bottom: 20px;
}

.error-message p {
    font-size: 16px;
    margin: 10px 0;
}
</style>