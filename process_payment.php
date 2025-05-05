<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: php/login.php?return_url=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

include 'php/header.php'; 
include 'php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['car_id'])) {
    $car_id = intval($_POST['car_id']);
    $user_id = $_SESSION['user_id'];
    $booking_date = date('Y-m-d');
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_amount = floatval($_POST['total_amount']);
    $status = 'Confirmed';
    $payment_method = $_POST['payment_method'];
    $card_last4 = isset($_POST['card_last4']) ? $_POST['card_last4'] : null;

    // Check availability
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

    // Insert booking with payment details
    $query = "INSERT INTO bookings (vehicle_id, user_id, booking_date, start_date, end_date, status, total_cost, payment_method, card_last4) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iissssdss', $car_id, $user_id, $booking_date, $start_date, $end_date, $status, $total_amount, $payment_method, $card_last4);

    if ($stmt->execute()) {
        $booking_id = $stmt->insert_id;
        
        // In a real application, you would process payment with a gateway here
        // For this example, we'll simulate a successful payment
        
        ?>
        <div class="confirmation">
            <h2>Payment Successful!</h2>
            <div class="payment-details">
                <p><strong>Booking ID:</strong> #<?php echo $booking_id; ?></p>
                <p><strong>Dates:</strong> <?php echo date('M j, Y', strtotime($start_date)); ?> to <?php echo date('M j, Y', strtotime($end_date)); ?></p>
                <p><strong>Total Paid:</strong> $<?php echo number_format($total_amount, 2); ?></p>
                <p><strong>Payment Method:</strong> Card ending in <?php echo htmlspecialchars($card_last4); ?></p>
            </div>
            <p>Your booking has been confirmed. A receipt has been sent to your email.</p>
            <div class="action-buttons">
                <a href="php/booking_history.php" class="btn">View Bookings</a>
                <a href="index.php" class="btn secondary">Return Home</a>
            </div>
        </div>
        <?php
    } else {
        echo "<div class='error-message'>";
        echo "<h2>Payment Failed</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
        echo "<a href='book_car.php?id=$car_id' class='btn'>Try Again</a>";
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
