<?php
include 'db_connect.php';

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']); // Sanitize the input

    // Prepare the DELETE query
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'User deleted successfully';
    } else {
        $response['message'] = 'Error deleting user: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?>