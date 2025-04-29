<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in and has the right role (staff)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    echo "Unauthorized access!";
    exit();
}

// Get form data
$vehicle_id = intval($_POST['vehicle_id']);
$description = $_POST['description'] ?? null;
$priority = $_POST['priority'] ?? 'moderate'; // Default to 'moderate'
$assigned_to = intval($_POST['assigned_to']);
$repair_status = $_POST['repair_status'] ?? 'pending'; // Default to 'pending'
$requested_by = $_SESSION['user_id']; // Staff ID from session

// Validate the repair_status value
$allowed_statuses = ['pending', 'in_progress', 'completed'];
if (!in_array($repair_status, $allowed_statuses)) {
    echo "Error: Invalid repair status.";
    exit();
}

// Validate required fields
if (empty($vehicle_id) || empty($priority) || empty($repair_status) || empty($requested_by) || empty($assigned_to)) {
    echo "Error: All required fields must be filled.";
    exit();
}

// Prepare the SQL insert query
$sql = "INSERT INTO maintenance_requests (vehicle_id, description, priority, status, requested_by, assigned_to, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

// Debugging: Log the SQL query and values
// echo "SQL Query: " . $sql . "<br>";
// echo "Values: vehicle_id=$vehicle_id, description=$description, priority=$priority, status=$repair_status, requested_by=$requested_by, assigned_to=$assigned_to<br>";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Debugging: Check if statement was prepared successfully
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Bind the parameters
$stmt->bind_param('isssii', $vehicle_id, $description, $priority, $repair_status, $requested_by, $assigned_to);

// Execute the query
if ($stmt->execute()) {
    echo "Maintenance request created successfully!";
} else {
    echo "Error creating maintenance request: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>