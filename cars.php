<?php 
include 'php/header.php'; 
include 'php/db_connect.php';
?>

<style>
    main {
        padding: 40px 20px;
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }

    h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 30px;
    }

    .search-bar {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
}

.search-form {
    display: flex;
    width: 100%;
    max-width: 600px;
}

.search-bar input[type="text"] {
    width: 100%;
    padding: 12px 20px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 25px 0 0 25px;
    outline: none;
    border-right: none; /* Remove right border to connect with button */
}

.search-bar button {
    background-color: #007bff;
    border: 1px solid #007bff;
    border-left: none; /* Remove left border to connect with input */
    padding: 12px 20px;
    border-radius: 0 25px 25px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-bar button img {
    width: 20px;
    height: 20px;
}

    .cars-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .car-card {
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        background: white;
        transition: transform 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .car-card:hover {
        transform: translateY(-5px);
    }

    .car-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .car-card-content {
        padding: 15px;
    }

    .car-card-content h3 {
        margin: 0;
        font-size: 20px;
        color: #333;
    }

    .car-card-content p {
        font-size: 14px;
        color: #666;
        margin: 10px 0 15px;
    }

    .book-now-btn {
        display: inline-block;
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        transition: background-color 0.3s;
        margin-bottom: 15px;
    }

    .book-now-btn:hover {
        background-color: #218838;
    }

    .car-details {
        text-align: left;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .car-details img {
        width: 100%;
        height: auto;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .car-details h1 {
        font-size: 28px;
        color: #333;
        margin-bottom: 15px;
    }

    .car-details p {
        font-size: 16px;
        color: #666;
        margin-bottom: 10px;
    }
</style>


<main>
    <?php
    if (isset($_GET['id'])) {
        // Car details page logic (no changes here)
        $car_id = intval($_GET['id']);
        
        // Fetch car details and primary image
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
            ?>
            <div class="car-details">
            <img src="uploads/<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
            <h1><?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']); ?></h1>
                <p>Daily Rate: $<?php echo htmlspecialchars($car['daily_rate']); ?></p>
                <p>Color: <?php echo htmlspecialchars($car['color'] ?? 'N/A'); ?></p>
                <p>Mileage: <?php echo htmlspecialchars($car['mileage'] ?? 'N/A'); ?> km</p>
                <p>Seating Capacity: <?php echo htmlspecialchars($car['seating_capacity']); ?></p>
                <p>Transmission: <?php echo htmlspecialchars($car['transmission']); ?></p>
                <p>Fuel Type: <?php echo htmlspecialchars($car['fuel_type']); ?></p>
                <a href="book_car.php?id=<?php echo $car['id']; ?>" class="book-now-btn">Book Now</a>
            </div>
            <?php
        } else {
            echo "<p>Car not found.</p>";
        }
    } else {
        ?>
        <h1>Book Your Car Now</h1>
        <div class="search-bar">
    <form action="cars.php" method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search cars..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button type="submit">
            <img src="images/search-icon.png" alt="Search">
        </button>
    </form>
</div>


        <div class="cars-grid">
            <?php
            $search = $_GET['search'] ?? ''; // Get the search term from the query string
            $search = trim($search); // Remove extra spaces

            $sql = "SELECT 
                        v.id, 
                        v.make, 
                        v.model, 
                        v.year, 
                        v.daily_rate, 
                        vi.image_path 
                    FROM vehicles v 
                    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1";

            if (!empty($search)) {
                $sql .= " WHERE v.make LIKE ? OR v.model LIKE ? OR v.year LIKE ?";
            }

            $stmt = $conn->prepare($sql);

            if (!empty($search)) {
                $search_param = '%' . $search . '%';
                $stmt->bind_param('sss', $search_param, $search_param, $search_param);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            ?>
            <div class="cars-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($car = $result->fetch_assoc()) {
                        ?>
                        <div class="car-card">
                            <img src="uploads/<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                            <div class="car-card-content">
                                <h3><?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']); ?></h3>
                                <p>Daily Rate: $<?php echo htmlspecialchars($car['daily_rate']); ?></p>
                                <a href="book_car.php?id=<?php echo $car['id']; ?>" class="book-now-btn">Book Now</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No cars found matching your search.</p>";
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</main>



<?php include 'php/footer.php'; ?>
