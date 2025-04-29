<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// Handle cancellation
if (isset($_POST['cancel_booking_id'])) {
    $booking_id = intval($_POST['cancel_booking_id']);
    
    // Start transaction for atomic operation
    $conn->begin_transaction();
    
    try {
        // Lock the booking row for update
        $check_sql = "SELECT status FROM bookings WHERE id = ? AND user_id = ? FOR UPDATE";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('ii', $booking_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            throw new Exception("Booking not found or doesn't belong to you.");
        }
        
        $booking = $check_result->fetch_assoc();
        $current_status = strtolower(trim($booking['status']));
        
        if ($current_status !== 'pending') {
            throw new Exception("Only pending bookings can be cancelled. Current status: " . $booking['status']);
        }
        
        // Update booking status to 'cancelled'
        $update_sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('ii', $booking_id, $user_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to cancel booking. Database error.");
        }
        
        $conn->commit();
        $success_message = "Booking cancelled successfully.";
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
    
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
}

// Fetch user bookings with proper status validation
$bookings = [];
$sql = "SELECT bookings.id,
               CONCAT(vehicles.make, ' ', vehicles.model) AS vehicle_name, 
               bookings.start_date, 
               bookings.end_date, 
               bookings.total_cost, 
               TRIM(LOWER(bookings.status)) AS status
        FROM bookings
        INNER JOIN vehicles ON bookings.vehicle_id = vehicles.id
        WHERE bookings.user_id = ?
        ORDER BY bookings.booking_date DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
} else {
    $error_message = "Failed to fetch bookings.";
}
?>

<style>
.booking-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
}
.booking-container h2 {
    margin-bottom: 20px;
    color: #333;
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #007BFF;
    color: white;
}
tr:hover {
    background-color: #f1f1f1;
}
.no-bookings {
    text-align: center;
    margin-top: 30px;
    font-size: 18px;
    color: #666;
}
.success-message, .error-message {
    text-align: center;
    margin-bottom: 20px;
    font-weight: bold;
}
.success-message {
    color: green;
}
.error-message {
    color: red;
}
.cancel-btn {
    background-color: red;
    color: white;
    padding: 5px 10px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}
.cancel-btn:hover {
    background-color: darkred;
}
.status-pending {
    color: #FFA500;
    font-weight: bold;
}
.status-confirmed {
    color: #28a745;
    font-weight: bold;
}
.status-cancelled {
    color: #dc3545;
    font-weight: bold;
}
</style>

<main>

<div class="booking-container">
    <h2>My Booking History</h2>

    <?php if ($success_message): ?>
        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (count($bookings) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Vehicle Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
          
          
            <tbody>
    <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
            <td><?php echo htmlspecialchars(date('d M Y', strtotime($booking['start_date']))); ?></td>
            <td><?php echo htmlspecialchars(date('d M Y', strtotime($booking['end_date']))); ?></td>
            <td><?php echo htmlspecialchars($booking['total_cost']); ?></td>
            <td class="status-<?php echo htmlspecialchars($booking['status']); ?>">
                <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
            </td>
            <td>
                <?php 
                // Debug output
                error_log("Rendering booking ID {$booking['id']} with status: {$booking['status']}");
                
                if (strtolower($booking['status']) === 'pending'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="cancel_booking_id" value="<?php echo $booking['id']; ?>">
                        <button type="submit" class="cancel-btn" 
                            onclick="return confirm('Are you sure you want to cancel this booking?');">
                            Cancel
                        </button>
                    </form>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

        </table>

    <?php else: ?>
        <div class="no-bookings">
            You have no bookings yet.<br><br>
            <a href="cars.php" style="color: #007BFF; text-decoration: underline;">Browse cars and book now!</a>
        </div>
    <?php endif; ?>
</div>

</main>

<?php include 'footer.php'; ?>