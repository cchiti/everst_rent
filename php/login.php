<?php
session_start(); // Start session to store user info

// If user is already logged in, redirect based on role
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'staff':
            header("Location: /car_a/php/dashboard.php");
            break;
        case 'mechanic':
            header("Location: /car_a/php/maintenance_dashboard.php");
            break;
        case 'customer':
            header("Location: /car_a/php/cars.php");
            break;
        default:
            // Unknown role, logout for safety
            header("Location: logout.php");
            break;
    }
    exit();
}

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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signIn'])) {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists with the entered email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User found, fetch user data
        $user = $result->fetch_assoc();

        // Verify password using password_verify() with the stored hash
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_role'] = $user['role'];

            // Get the return_url parameter if it exists, default to 'cars.php'
            $return_url = isset($_GET['return_url']) ? $_GET['return_url'] : 'cars.php';
            
            // Redirect based on role
    if ($user['role'] === 'staff') {
        header("Location: /car_a/php/dashboard.php");
        exit();
    } elseif ($user['role'] === 'mechanic') {
        header("Location: /car_a/php/maintenance_dashboard.php");  // <-- mechanic goes here
        exit();
    } else {
        // Normal customer
        $return_url = isset($_GET['return_url']) ? $_GET['return_url'] : 'cars.php';
        header("Location: /car_a/$return_url");
        exit();
    }
        } else {
            $error_message = "Invalid password!"; // Set error message for wrong password
        }
    } else {
        $error_message = "No user found with this email!"; // Set error message for wrong email
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="login-container">
    <!-- Left Side -->
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

    <form action="login.php" method="POST" class="login-form">
    <div class="input-group">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" placeholder="Enter your email" required>
    </div>
    <div class="input-group">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>
    </div>
        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>

        <div class="buttons">
            <button type="submit" name="signIn" class="login-btn login">Login</button>
            <a href="register.php" class="login-btn register">Register</a>
            </button>
        </div>
    </form>

    <div class="bottom">
        <p>Terms and Conditions</p>
    </div>
</div>


    <!-- Right Side -->
    <div class="right-side">
        <img src="../images/image1.png" alt="Background" class="right-image">
    </div>
</div>


</body>
</html>
