<?php include 'php/header.php'; ?>

<style>
    /* Hero Section */
    .hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: url('https://cdn.vectorstock.com/i/500p/35/82/car-rental-vector-13423582.jpg') no-repeat center center/cover;
        color: white;
        padding: 100px 20px;
    }
    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent overlay */
        z-index: 1;
    }
    .hero-content {
        position: relative;
        z-index: 2; /* Ensure content is above the overlay */
        max-width: 50%;
        text-align: left;
        color: white; /* Ensure text is white for better contrast */
    }
    .hero h2 {
        font-size: 48px;
        margin-bottom: 20px;
    }
    .hero p {
        font-size: 18px;
        margin-bottom: 30px;
    }
    .hero .btn {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        font-size: 16px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    .hero .btn:hover {
        background-color: #0056b3;
    }
    .hero-image {
        position: relative;
        z-index: 2; /* Ensure image is above the overlay */
        max-width: 40%;
    }
    .hero-image img {
        width: 100%;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Features Section */
    .features {
        display: flex;
        justify-content: space-around;
        padding: 50px 20px;
        background-color: #f9f9f9;
    }
    .feature {
        text-align: center;
        max-width: 300px;
    }
    .feature h3 {
        font-size: 24px;
        margin-bottom: 15px;
        color: #333;
    }
    .feature p {
        font-size: 16px;
        color: #666;
    }
    
    /* Reviews Section */
    .reviews {
        padding: 60px 20px;
        background-color: #fff;
        text-align: center;
    }
    .reviews h2 {
        font-size: 36px;
        margin-bottom: 40px;
        color: #333;
    }
    .reviews-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 30px;
    }
    .review-card {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 25px;
        max-width: 350px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .review-card:hover {
        transform: translateY(-10px);
    }
    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .review-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
    }
    .review-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    .review-rating {
        color: #FFD700;
        font-size: 16px;
    }
    .review-text {
        font-size: 16px;
        color: #555;
        line-height: 1.6;
        text-align: left;
    }
    .review-date {
        font-size: 14px;
        color: #888;
        margin-top: 15px;
        text-align: right;
    }
    
    /* FAQ Section */
    .faq {
        padding: 60px 20px;
        background-color: #f9f9f9;
        text-align: center;
    }
    .faq h2 {
        font-size: 36px;
        margin-bottom: 40px;
        color: #333;
    }
    .faq-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .faq-item {
        margin-bottom: 15px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .faq-question {
        background-color: #007bff;
        color: white;
        padding: 15px 20px;
        cursor: pointer;
        font-size: 18px;
        font-weight: 600;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s;
    }
    .faq-question:hover {
        background-color: #0069d9;
    }
    .faq-question::after {
        content: '+';
        font-size: 24px;
        transition: transform 0.3s;
    }
    .faq-question.active::after {
        content: '-';
    }
    .faq-answer {
        background-color: white;
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease;
    }
    .faq-answer.show {
        padding: 20px;
        max-height: 500px;
    }
    .faq-answer p {
        color: #555;
        line-height: 1.6;
        text-align: left;
        margin: 0;
    }

    /* Team Section */
    .team {
        padding: 60px 20px;
        background-color: #fff;
        text-align: center;
    }
    .team h2 {
        font-size: 36px;
        margin-bottom: 40px;
        color: #333;
    }
    .team-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 30px;
    }
    .team-member {
        background-color: #f9f9f9;
        border-radius: 10px;
        padding: 25px;
        width: 250px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .team-member:hover {
        transform: translateY(-10px);
    }
    .team-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 15px;
        border: 5px solid #007bff;
    }
    .team-name {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    .team-position {
        font-size: 16px;
        color: #007bff;
        margin-bottom: 10px;
        font-weight: 600;
    }
    .team-id {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }
    .team-contact {
        font-size: 14px;
        color: #555;
    }
</style>

<section class="hero">
    <div class="hero-content">
        <h2>Welcome to Car Rental</h2>
        <p>Your trusted partner for comfortable and affordable vehicle rentals.</p>
        <a href="cars.php" class="btn">Browse Cars</a>
    </div>
    <div class="hero-image">
        <!-- <img src="images/hero-car.png" alt="Hero Car" /> -->
    </div>
</section>

<section class="features">
    <div class="feature">
        <h3>Wide Range of Vehicles</h3>
        <p>Choose from a variety of models and brands to suit your journey.</p>
    </div>
    <div class="feature">
        <h3>Affordable Prices</h3>
        <p>Get the best deals and discounts on your favorite vehicles.</p>
    </div>
    <div class="feature">
        <h3>Easy Booking</h3>
        <p>Book your car in just a few simple steps with instant confirmation.</p>
    </div>
</section>

<section class="reviews">
    <h2>What Our Customers Say</h2>
    <div class="reviews-container">
        <div class="review-card">
            <div class="review-header">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John D." class="review-avatar">
                <div>
                    <div class="review-name">John D.</div>
                    <div class="review-rating">★★★★★</div>
                </div>
            </div>
            <p class="review-text">"Excellent service! The car was clean, well-maintained, and exactly as described. The pickup and drop-off process was smooth and hassle-free. Will definitely rent from them again!"</p>
            <div class="review-date">March 15, 2023</div>
        </div>
        
        <div class="review-card">
            <div class="review-header">
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah M." class="review-avatar">
                <div>
                    <div class="review-name">Sarah M.</div>
                    <div class="review-rating">★★★★☆</div>
                </div>
            </div>
            <p class="review-text">"Great experience overall. The staff was friendly and helpful. The car ran perfectly throughout my trip. Only minor complaint was the slightly higher price compared to competitors."</p>
            <div class="review-date">February 28, 2023</div>
        </div>
        
        <div class="review-card">
            <div class="review-header">
                <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Robert T." class="review-avatar">
                <div>
                    <div class="review-name">Robert T.</div>
                    <div class="review-rating">★★★★★</div>
                </div>
            </div>
            <p class="review-text">"Best car rental experience I've had! The online booking was simple, and when I arrived, my car was ready and waiting. The vehicle was practically new and drove like a dream. 10/10 would recommend!"</p>
            <div class="review-date">January 10, 2023</div>
        </div>
    </div>
</section>

<section class="faq">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-container">
        <div class="faq-item">
            <div class="faq-question">What documents do I need to rent a car?</div>
            <div class="faq-answer">
                <p>You'll need a valid driver's license, a credit card in your name for the security deposit, and proof of insurance if you're not purchasing our coverage. International renters may need an International Driving Permit along with their native license.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">What is the minimum age to rent a car?</div>
            <div class="faq-answer">
                <p>The minimum age is 21 years old. Drivers under 25 may be subject to a young driver fee. Some premium or luxury vehicles may have higher age requirements.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">Can I extend my rental period?</div>
            <div class="faq-answer">
                <p>Yes, you can extend your rental as long as the vehicle is available. Please contact us at least 24 hours before your scheduled return time to arrange an extension. Additional charges will apply.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">What's included in the rental price?</div>
            <div class="faq-answer">
                <p>Our base rate includes unlimited mileage, basic liability coverage, and 24/7 roadside assistance. Optional extras like additional insurance, GPS, child seats, or additional drivers may incur extra charges.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">What is your fuel policy?</div>
            <div class="faq-answer">
                <p>We operate on a full-to-full policy. You'll receive the car with a full tank and should return it full to avoid refueling charges. We provide a list of nearby gas stations when you pick up your vehicle.</p>
            </div>
        </div>
    </div>
</section>

<section class="team">
    <h2>Meet Our Team</h2>
    <div class="team-container">
        <div class="team-member">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Adarsha Thapa" class="team-avatar">
            <h3 class="team-name">Adarsha Thapa</h3>
            <div class="team-position">Rental Staff</div>
            <div class="team-id">ID: 987417</div>
            <div class="team-contact">contact@carrental.com</div>
        </div>
        
        <div class="team-member">
            <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="Ashish Subedi" class="team-avatar">
            <h3 class="team-name">Ashish Subedi</h3>
            <div class="team-position">Mechanic</div>
            <div class="team-id">ID: 987544</div>
            <div class="team-contact">contact@carrental.com</div>
        </div>
        <div class="team-member">
            <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Sangay Wangdi" class="team-avatar">
            <h3 class="team-name">Sangay Wangdi</h3>
            <div class="team-position">Rental Staff</div>
            <div class="team-id">ID: 987560</div>
            <div class="team-contact">contact@carrental.com</div>
        </div>
        
        <div class="team-member">
            <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Sandeep Timilsena" class="team-avatar">
            <h3 class="team-name">Sandeep Timilsena</h3>
            <div class="team-position">Mechanic</div>
            <div class="team-id">ID: 987543</div>
            <div class="team-contact">contact@carrental.com</div>
        </div>
    </div>
</section>

<script>
    // FAQ Accordion Functionality
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const isActive = question.classList.contains('active');
            
            // Close all other items
            document.querySelectorAll('.faq-question').forEach(q => {
                q.classList.remove('active');
                q.nextElementSibling.classList.remove('show');
            });
            
            // Open current if it wasn't active
            if (!isActive) {
                question.classList.add('active');
                answer.classList.add('show');
            }
        });
    });
</script>

<?php include 'php/footer.php'; ?>