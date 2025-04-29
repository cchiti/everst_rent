<?php
session_start(); // Start session to store user info

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_a"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = ''; // Initialize an empty variable for error message

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $license_number = $_POST['license_number'];
    $address = $_POST['address'];
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists (prepared statement for security)
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Email is already registered!";
    } else {
        // Insert new user into database (prepared statement for security)
        $sql = "INSERT INTO users (first_name, last_name, email, password, dob, phone, license_number, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $hashed_password, $dob, $phone, $license_number, $address);

        if ($stmt->execute()) {
            // Registration successful, redirect to login page
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>

<div class="register-container">
    <div class="left-side">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo" width="100">
        </div>

        <!-- Display error message if any -->
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="register-form">
            <div class="input-row">
                <div class="input-group half-width">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" required>
                </div>
                <div class="input-group half-width">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" required>
                </div>
            </div>

            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>

            <div class="input-row">
                <div class="input-group half-width">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" name="dob" id="dob" required>
                </div>
                <div class="input-group half-width">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" required>
                </div>
            </div>

            <div class="input-group">
                <label for="license_number">License Registered Number:</label>
                <input type="text" name="license_number" id="license_number" placeholder="Enter your license number" required>
            </div>

            <div class="input-group">
                <label for="address">Address:</label>
                <input type="text" name="address" id="address" placeholder="Enter your address" required>
            </div>

            <div class="buttons">
                <button type="submit" name="register" class="login-btn">Register</button>
            </div>
        </form>
    </div>

    <!-- Right Side: Registration Information -->
    <div class="right-side">
        <h2>Why Register?</h2>
        <p>Just 3 easy steps: Fill the form, verify your email, choose your car.<br>Start driving in under 5 minutes.</p>

        <h3>Key Benefits</h3>
        <ul>
            <li>24/7 availability—book anytime, day or night</li>
            <li>Easy online reservation—no phone calls required</li>
            <li>Flexible cancellation up to 24 hrs before pick‑up</li>
        </ul>

        <h3>Special Offers</h3>
        <ul>
            <li>Get $10 off your first rental when you sign up today</li>
            <li>Join our Loyalty Program for free upgrades & discounts</li>
        </ul>

        <h3>Trust & Security</h3>
        <ul>
            <li>Trusted by 10,000+ travelers worldwide</li>
            <li>Customer reviews & 4.8★ average rating—read real stories</li>
            <li>Your data is encrypted and PCI‑compliant for total peace of mind</li>
        </ul>

        <h3>Need Help?</h3>
        <p>Live chat & phone support 24/7—we’re here whenever you need us</p>

        <p>Already have an account? <a href="login.php">Log in here</a> to skip registration and pick up where you left off.</p>
    </div>
</div>

</body>
</html>
