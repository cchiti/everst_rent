<?php
session_start();

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

<!-- Rating and Feedback Section -->
<div class="rating-feedback-section">
    <h3 class="section-title">Rate and Review This Vehicle</h3>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Feedback Form (only shown to logged-in users) -->
        <div class="feedback-form-container">
            <form action="submit_feedback.php" method="POST" id="feedbackForm">
                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">

                <!-- Star Rating System -->
                <div class="form-group rating-group">
                    <label>Your Rating:</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" title="5 stars"></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" title="4 stars"></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" title="3 stars"></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" title="2 stars"></label><input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" title="1 star"></label>
                    </div>
                </div>

                <!-- Feedback Text -->
                <div class="form-group">
                    <label for="feedback">Your Review:</label>
                    <textarea name="feedback" id="feedback" rows="5" 
                              placeholder="Share your experience with this vehicle..." 
                              minlength="20" maxlength="500" required></textarea>
                    <div class="char-counter"><span id="charCount">0</span>/500 characters</div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit-feedback">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="login-prompt">
            <p>Please <a href="/car_a/php/login.php">log in</a> to submit your review.</p>
        </div>
    <?php endif; ?>

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
        <div class="dialog-row">
            <span class="dialog-label">Total Cost:</span>
            <span class="dialog-value" id="totalCost">$0.00</span>
        </div>
        
        <!-- Payment Form (initially hidden) -->
        <div id="paymentFormContainer" style="display: none;">
            <h3>Payment Information</h3>
            <div class="form-group">
                <label for="cardNumber">Card Number</label>
                <input type="text" id="cardNumber" class="dialog-input" placeholder="1234 5678 9012 3456" required>
            </div>
            <div class="form-group">
                <label for="cardName">Name on Card</label>
                <input type="text" id="cardName" class="dialog-input" placeholder="John Doe" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="expiryDate">Expiry Date</label>
                    <input type="text" id="expiryDate" class="dialog-input" placeholder="MM/YY" required>
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" class="dialog-input" placeholder="123" required>
                </div>
            </div>
        </div>
        
        <div class="dialog-buttons">
            <button type="button" id="cancelBooking" class="dialog-button cancel">Cancel</button>
            <button type="button" id="bookNowBtn" class="dialog-button book-now">Book Now (Pay Later)</button>
            <button type="button" id="payNowBtn" class="dialog-button pay-now">Pay Now & Confirm</button>
            <button type="button" id="submitPaymentBtn" class="dialog-button confirm" style="display: none;">Submit Payment</button>
        </div>
    </div>
</div>

<!-- Hidden form for payment submission -->
<form method="post" action="process_payment.php" id="paymentSubmissionForm" style="display: none;">
    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
    <input type="hidden" name="start_date" id="formStartDate">
    <input type="hidden" name="end_date" id="formEndDate">
    <input type="hidden" name="total_amount" id="formTotalAmount">
    <input type="hidden" name="payment_method" value="card">
    <input type="hidden" name="card_last4" id="cardLast4">
</form>

         <!-- Customer Reviews Section -->
    <div class="customer-reviews">
        <h3 class="section-title">Customer Reviews</h3>
        
        <?php
        // Calculate average rating
        $avgRatingStmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM car_feedback WHERE car_id = ?");
        $avgRatingStmt->bind_param("i", $car_id);
        $avgRatingStmt->execute();
        $avgRatingResult = $avgRatingStmt->get_result();
        $ratingData = $avgRatingResult->fetch_assoc();
        $averageRating = round($ratingData['avg_rating'] ?? 0, 1);
        $reviewCount = $ratingData['review_count'] ?? 0;
        ?>

         <!-- Rating Summary -->
         <div class="rating-summary">
            <div class="average-rating">
                <span class="rating-number"><?php echo $averageRating; ?></span>
                <span class="rating-out-of">/5</span>
                <div class="stars">
                    <?php
                    $fullStars = floor($averageRating);
                    $halfStar = ($averageRating - $fullStars) >= 0.5;
                    
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($halfStar && $i == $fullStars + 1) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="review-count">Based on <?php echo $reviewCount; ?> reviews</div>
        </div>
         <!-- Individual Reviews -->
         <div class="reviews-list">
            <?php
            $stmt = $conn->prepare("SELECT cf.*, u.first_name, u.last_name 
                                   FROM car_feedback cf
                                   JOIN users u ON cf.user_id = u.id
                                   WHERE cf.car_id = ? 
                                   ORDER BY cf.created_at DESC");
            $stmt->bind_param("i", $car_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="review-item">';
                    echo '<div class="review-header">';
                    echo '<div class="reviewer-name">' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name'][0] . '.') . '</div>';
                    echo '<div class="review-date">' . date('F j, Y', strtotime($row['created_at'])) . '</div>';
                    echo '</div>';


                    echo '<div class="review-rating">';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $row['rating']) {
                            echo '<i class="fas fa-star"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    echo '</div>';
                    
                    echo '<div class="review-content">' . nl2br(htmlspecialchars($row['feedback'])) . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-reviews">';
                echo '<i class="far fa-comment-dots"></i>';
                echo '<p>No reviews yet. Be the first to share your experience!</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
    const dialog = document.getElementById('bookingDialog');
    const openButton = document.getElementById('openBookingDialog');
    const cancelButton = document.getElementById('cancelBooking');
    const bookNowBtn = document.getElementById('bookNowBtn');
    const payNowBtn = document.getElementById('payNowBtn');
    const submitPaymentBtn = document.getElementById('submitPaymentBtn');
    const paymentFormContainer = document.getElementById('paymentFormContainer');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const totalCostSpan = document.getElementById('totalCost');
    const paymentSubmissionForm = document.getElementById('paymentSubmissionForm');
    const formStartDate = document.getElementById('formStartDate');
    const formEndDate = document.getElementById('formEndDate');
    const formTotalAmount = document.getElementById('formTotalAmount');
    const cardLast4 = document.getElementById('cardLast4');
    const dailyRate = <?php echo $car['daily_rate']; ?>;
    
    let currentBookingType = ''; // 'book_now' or 'pay_now'

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    startDateInput.min = today;
    
    // Update end date min date when start date changes
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
        calculateTotal();
    });

    // Calculate total when end date changes
    endDateInput.addEventListener('change', calculateTotal);

    function calculateTotal() {
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            const total = days * dailyRate;
            totalCostSpan.textContent = '$' + total.toFixed(2);
        } else {
            totalCostSpan.textContent = '$0.00';
        }
    }

    // Open dialog
    openButton.addEventListener('click', function() {
        dialog.style.display = 'flex';
        resetPaymentForm();
    });

    // Close dialog
    cancelButton.addEventListener('click', function() {
        dialog.style.display = 'none';
        resetPaymentForm();
    });

    // Handle Book Now button
    bookNowBtn.addEventListener('click', function() {
        if (!validateDates()) return;
        
        // Set the hidden form values
        formStartDate.value = startDateInput.value;
        formEndDate.value = endDateInput.value;
        formTotalAmount.value = calculateTotalAmount();
        
        // Submit the form to confirm_booking.php
        paymentSubmissionForm.action = 'confirm_booking.php';
        paymentSubmissionForm.submit();
    });

    // Handle Pay Now button
    payNowBtn.addEventListener('click', function() {
        if (!validateDates()) return;
        
        // Show payment form and hide other buttons
        paymentFormContainer.style.display = 'block';
        bookNowBtn.style.display = 'none';
        payNowBtn.style.display = 'none';
        submitPaymentBtn.style.display = 'inline-block';
        
        // Store the booking details
        formStartDate.value = startDateInput.value;
        formEndDate.value = endDateInput.value;
        formTotalAmount.value = calculateTotalAmount();
    });

    // Handle Submit Payment button
    submitPaymentBtn.addEventListener('click', function() {
        if (!validatePaymentForm()) return;
        
        // Get last 4 digits of card
        const cardNumber = document.getElementById('cardNumber').value.replace(/\s+/g, '');
        cardLast4.value = cardNumber.slice(-4);
        
        // Submit the form to process_payment.php
        paymentSubmissionForm.action = 'process_payment.php';
        paymentSubmissionForm.submit();
    });

    // Helper functions
    function validateDates() {
        if (!startDateInput.value || !endDateInput.value) {
            alert('Please select both start and end dates');
            return false;
        }
        
        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);
        
        if (start > end) {
            alert('End date must be after start date');
            return false;
        }
        
        return true;
    }

    function validatePaymentForm() {
        const cardNumber = document.getElementById('cardNumber').value.replace(/\s+/g, '');
        const cardName = document.getElementById('cardName').value.trim();
        const expiryDate = document.getElementById('expiryDate').value.trim();
        const cvv = document.getElementById('cvv').value.trim();
        
        // Simple validation - in production use a proper library
        if (!/^\d{13,16}$/.test(cardNumber)) {
            alert('Please enter a valid card number');
            return false;
        }
        
        if (cardName.length < 3) {
            alert('Please enter the name on card');
            return false;
        }
        
        if (!/^\d{3,4}$/.test(cvv)) {
            alert('Please enter a valid CVV');
            return false;
        }
        
        return true;
    }

    function calculateTotalAmount() {
        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);
        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        return days * dailyRate;
    }

    function resetPaymentForm() {
        paymentFormContainer.style.display = 'none';
        bookNowBtn.style.display = 'inline-block';
        payNowBtn.style.display = 'inline-block';
        submitPaymentBtn.style.display = 'none';
    }
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
/* Rating and Feedback Section Styles */
.rating-feedback-section {
    margin: 40px 0;
    padding: 30px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 22px;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

/* Feedback Form Styles */
.feedback-form-container {
    margin-bottom: 40px;
    padding: 25px;
    background-color: #f9f9f9;
    border-radius: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
}

/* Star Rating System */
.rating-group {
    margin-bottom: 25px;
}

.star-rating {
    display: inline-block;
    font-size: 0;
    unicode-bidi: bidi-override;
    direction: rtl;
}
.star-rating input {
    display: none;
}

.star-rating label {
    color: #ccc;
    font-size: 24px;
    padding: 0 3px;
    cursor: pointer;
    display: inline-block;
    transition: color 0.2s;
}.star-rating label:before {
    content: "★";
}

.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}

.star-rating input:checked + label {
    color: #ffc107;
}

/* Textarea Styles */
textarea#feedback {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
    resize: vertical;
}textarea#feedback:focus {
    border-color: #4a90e2;
    outline: none;
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
}

.char-counter {
    text-align: right;
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}


/* Submit Button */
.btn-submit-feedback {
    background-color: #4a90e2;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
}

.btn-submit-feedback i {
    margin-right: 8px;
}

.btn-submit-feedback:hover {
    background-color: #3a7bc8;
}


.login-prompt {
    padding: 20px;
    background-color: #f5f5f5;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 30px;
}

.login-prompt a {
    color: #4a90e2;
    text-decoration: none;
    font-weight: 600;
}

.login-prompt a:hover {
    text-decoration: underline;
}

/* Customer Reviews Section */
.customer-reviews {
    margin-top: 40px;
}
.rating-summary {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.average-rating {
    display: flex;
    align-items: baseline;
    margin-right: 30px;
}

.rating-number {
    font-size: 42px;
    font-weight: 700;
    color: #333;
}
.rating-out-of {
    font-size: 20px;
    color: #777;
    margin-right: 15px;
}

.stars {
    color: #ffc107;
    font-size: 20px;
}

.review-count {
    font-size: 16px;
    color: #666;
}/* Individual Reviews */
.reviews-list {
    margin-top: 20px;
}

.review-item {
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.review-item:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.reviewer-name {
    font-weight: 600;
    color: #333;
}
.review-date {
    color: #777;
    font-size: 14px;
}

.review-rating {
    color: #ffc107;
    margin-bottom: 10px;
}

.review-content {
    line-height: 1.6;
    color: #444;
}

.no-reviews {
    text-align: center;
    padding: 40px 20px;
    color: #777;
}

no-reviews i {
    font-size: 40px;
    margin-bottom: 15px;
    color: #ccc;
}

.no-reviews p {
    margin: 0;
    font-size: 16px;
}


/* Responsive Design */
@media (max-width: 768px) {
    .rating-feedback-section {
        padding: 20px;
    }
    
    .rating-summary {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .average-rating {
        margin-bottom: 15px;
    }
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for feedback textarea
    const feedbackTextarea = document.getElementById('feedback');
    const charCount = document.getElementById('charCount');
    
    if (feedbackTextarea && charCount) {
        feedbackTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Form validation
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            const rating = document.querySelector('input[name="rating"]:checked');
            const feedback = document.getElementById('feedback').value.trim();
            
            if (!rating) {
                e.preventDefault();
                alert('Please select a rating');
                return;
            } if (feedback.length < 20) {
                e.preventDefault();
                alert('Please provide more detailed feedback (at least 20 characters)');
                return;
            }
        });
    }
});
</script>