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
                
                <!-- Booking Form -->
                <form method="post" action="confirm_booking.php">
                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                    <input type="submit" value="Confirm Booking" class="book-now-btn">
                </form>
            </div>
        </div>

        <!-- Description Section -->
        <h3>Description</h3>
        <div class="description-card">
            <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p> <!-- Display Description -->
        </div>

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

</style>