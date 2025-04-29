<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['status'];
    $cost = floatval($_POST['cost']);

    // Update maintenance request
    $sql = "UPDATE maintenance_requests SET status = ?, cost = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $status, $cost, $request_id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        http_response_code(500);
        echo 'Failed to update request.';
    }

    $stmt->close();
}
?>
