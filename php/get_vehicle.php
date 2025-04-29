<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$vehicleId = $_GET['id'];  // Get vehicle ID from the query string

// Fetch vehicle details
$query = "SELECT * FROM vehicles WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $vehicleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $vehicle = $result->fetch_assoc();

    // Fetch thumbnail image
    $query = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? AND is_primary = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $vehicleId);
    $stmt->execute();
    $thumbnailResult = $stmt->get_result();
    $vehicle['thumbnail'] = $thumbnailResult->num_rows > 0 ? $thumbnailResult->fetch_assoc()['image_path'] : null;

    // Fetch additional images
    $query = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ? AND is_primary = FALSE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $vehicleId);
    $stmt->execute();
    $additionalImagesResult = $stmt->get_result();
    $additionalImages = [];
    while ($row = $additionalImagesResult->fetch_assoc()) {
        $additionalImages[] = $row['image_path'];
    }
    $vehicle['additional_images'] = $additionalImages;

    // Fetch vehicle features
    $query = "SELECT feature FROM vehicle_features WHERE vehicle_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $vehicleId);  // Correct variable name here
    $stmt->execute();
    $featuresResult = $stmt->get_result();  // Rename the result variable for clarity
    $features = [];
    while ($row = $featuresResult->fetch_assoc()) {
        $features[] = $row['feature'];
    }

    // Add features to the vehicle data
    $vehicle['features'] = $features;

    // Return vehicle data as JSON
    header('Content-Type: application/json');
    echo json_encode($vehicle);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Vehicle not found']);
}

$stmt->close();
$conn->close();
?>
