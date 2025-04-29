<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = intval($_POST['id']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete images associated with the vehicle
        $query = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $vehicleId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the image file
            }
        }

        // Delete images from the database
        $query = "DELETE FROM vehicle_images WHERE vehicle_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $vehicleId);
        $stmt->execute();

        // Delete the vehicle
        $query = "DELETE FROM vehicles WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $vehicleId);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting vehicle: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>