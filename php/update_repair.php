<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in and is a mechanic
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mechanic') {
    echo "Unauthorized access!";
    exit();
}

// Get form data (repair request id and updates)
$request_id = $_POST['request_id'];
$repair_status = $_POST['repair_status']; // 'fixed' or 'under_repair'
$cost = $_POST['cost']; // Cost of repair

// Update SQL query
$sql = "UPDATE maintenance_requests SET status = ?, cost = ?, updated_at = NOW() WHERE id = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param('sdi', $repair_status, $cost, $request_id);

// Execute the query
if ($stmt->execute()) {
    echo "Maintenance request updated successfully!";
} else {
    echo "Error updating maintenance request: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>
