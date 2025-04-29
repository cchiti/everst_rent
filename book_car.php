<?php
include 'php/header.php'; 
include 'php/db_connect.php';

if (isset($_GET['id'])) {
    $car_id = intval($_GET['id']);
    
    // Fetch car details along with description
    $sql = "SELECT v.*, vi.image_path
            FROM vehicles v
            LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1
            WHERE v.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $car_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $car = $result->fetch_assoc();

        // Fetch vehicle features
        $query = "SELECT feature FROM vehicle_features WHERE vehicle_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $car_id);
        $stmt->execute();
        $featuresResult = $stmt->get_result();
        $features = [];
        while ($row = $featuresResult->fetch_assoc()) {
            $features[] = $row['feature'];
        }

        ?>
        <div class="car-details-container">
            <!-- Left Side: Image -->
            <div class="car-details-left">
                <img src="uploads/<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>" class="car-image">
            </div>

            <!-- Right Side: Vehicle Features -->
            <div class="car-details-right">
                <h1><?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']); ?></h1>
                <p><strong>Model:</strong> <?php echo htmlspecialchars($car['model']); ?></p>
                <p><strong>Daily Rate:</strong> $<?php echo htmlspecialchars($car['daily_rate']); ?></p>
                <p><strong>Color:</strong> <?php echo htmlspecialchars($car['color'] ?? 'N/A'); ?></p>

                <div class="features-card">
                    <h3>Vehicle Features</h3>
                    <div class="features-list">
                        <div class="feature-column">
                            <?php 
                            // Display features in two columns
                            $column_count = ceil(count($features) / 2);
                            $first_column = array_slice($features, 0, $column_count);
                            $second_column = array_slice($features, $column_count);
                            
                            foreach ($first_column as $feature) {
                                echo "<p>- " . htmlspecialchars($feature) . "</p>";
                            }
                            ?>
                        </div>
                        <div class="feature-column">
                            <?php 
                            foreach ($second_column as $feature) {
                                echo "<p>- " . htmlspecialchars($feature) . "</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Button (triggers modal) -->
                <button id="openBookingDialog" class="book-now-btn">Confirm Booking</button>
            </div>
        </div>

        <!-- Description Section -->
        <h3>Description</h3>
        <div class="description-card">
            <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p> <!-- Display Description -->
        </div>

        <!-- Booking Dialog (hidden by default) -->
        <div id="bookingDialog" class="dialog-overlay">
            <div class="dialog-content">
                <h2>Booking Details</h2>
                <div class="dialog-row">
                    <span class="dialog-label">Car Name:</span>
                    <span class="dialog-value"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']); ?></span>
                </div>
                <div class="dialog-row">
                    <label class="dialog-label" for="startDate">Start Date:</label>
                    <input type="date" id="startDate" name="startDate" class="dialog-input" required>
                </div>
                <div class="dialog-row">
                    <label class="dialog-label" for="endDate">End Date:</label>
                    <input type="date" id="endDate" name="endDate" class="dialog-input" required>
                </div>
                <div class="dialog-row">
                    <span class="dialog-label">Rate per day:</span>
                    <span class="dialog-value">$<?php echo htmlspecialchars($car['daily_rate']); ?></span>
                </div>
                <div class="dialog-buttons">
                    <button type="button" id="cancelBooking" class="dialog-button cancel">Cancel</button>
                    <form method="post" action="confirm_booking.php" id="bookingForm">
                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                        <input type="hidden" name="start_date" id="formStartDate">
                        <input type="hidden" name="end_date" id="formEndDate">
                        <button type="submit" class="dialog-button confirm">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dialog = document.getElementById('bookingDialog');
                const openButton = document.getElementById('openBookingDialog');
                const cancelButton = document.getElementById('cancelBooking');
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                const formStartDate = document.getElementById('formStartDate');
                const formEndDate = document.getElementById('formEndDate');
                const bookingForm = document.getElementById('bookingForm');

                // Set minimum date to today
                const today = new Date().toISOString().split('T')[0];
                startDateInput.min = today;
                
                // Update end date min date when start date changes
                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                    if (endDateInput.value && endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                });

                // Open dialog
                openButton.addEventListener('click', function() {
                    dialog.style.display = 'flex';
                });

                // Close dialog
                cancelButton.addEventListener('click', function() {
                    dialog.style.display = 'none';
                });

                // Handle form submission
                bookingForm.addEventListener('submit', function(e) {
                    if (!startDateInput.value || !endDateInput.value) {
                        e.preventDefault();
                        alert('Please select both start and end dates');
                        return;
                    }
                    
                    // Set the hidden form values
                    formStartDate.value = startDateInput.value;
                    formEndDate.value = endDateInput.value;
                });

                // Close dialog when clicking outside
                window.addEventListener('click', function(event) {
                    if (event.target === dialog) {
                        dialog.style.display = 'none';
                    }
                });
            });
        </script>

        <?php
    } else {
        echo "<p>Car not found.</p>";
    }
} else {
    echo "<p>No car selected.</p>";
}

include 'php/footer.php';
?>

<style>
    /* General Container */
.car-details-container {
    display: flex;
    justify-content: space-between;
    margin: 20px;
}

/* Left Side: Image */
.car-details-left {
    flex: 1;
    padding-right: 20px;
}

.car-details-left .car-image {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 8px;
}

/* Right Side: Features and Info */
.car-details-right {
    flex: 1;
    padding-left: 20px;
}

.car-details-right h1 {
    font-size: 28px;
    margin-bottom: 10px;
}

.car-details-right p {
    font-size: 16px;
    margin-bottom: 10px;
}

.features-card {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.features-list {
    display: flex;
    justify-content: space-between;
}

.feature-column {
    width: 45%;
}

.book-now-btn {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
    margin-top: 20px;
}

.book-now-btn:hover {
    background-color: #0056b3;
}

/* Description Section */
.description-card {
    background-color: #f9f9f9;
    padding: 20px;
    margin-top: 40px;
    border-radius: 8px;
}

.description-card h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.description-card p {
    font-size: 16px;
    line-height: 1.6;
}

/* Booking Dialog Styles */
.dialog-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.dialog-content {
    background-color: white;
    padding: 25px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.dialog-content h2 {
    margin-top: 0;
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
}

.dialog-row {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.dialog-label {
    font-weight: bold;
    width: 120px;
    margin-right: 10px;
}

.dialog-value {
    flex: 1;
}

.dialog-input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.dialog-buttons {
    display: flex;
    justify-content: flex-end;
    margin-top: 25px;
    gap: 10px;
}

.dialog-button {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.dialog-button.confirm {
    background-color: #28a745;
    color: white;
}

.dialog-button.confirm:hover {
    background-color: #218838;
}

.dialog-button.cancel {
    background-color: #dc3545;
    color: white;
}

.dialog-button.cancel:hover {
    background-color: #c82333;
}
</style>