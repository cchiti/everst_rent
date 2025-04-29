<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'No booking ID provided']));
}

$bookingId = (int)$_GET['id'];

// Get booking details with customer and vehicle info
$stmt = $conn->prepare("
    SELECT b.*, 
           u.first_name, u.last_name, u.email, u.phone,
           v.make, v.model, v.license_plate
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['error' => 'Booking not found']));
}

$booking = $result->fetch_assoc();

// Add formatted customer and vehicle info
$booking['customer_name'] = $booking['first_name'] . ' ' . $booking['last_name'];
$booking['vehicle_info'] = $booking['make'] . ' ' . $booking['model'] . ' (' . $booking['license_plate'] . ')';

header('Content-Type: application/json');
echo json_encode($booking);
?>