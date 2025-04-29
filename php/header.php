<?php 
session_start(); // This must be at the very top of the file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Rental</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        header {
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        nav {
            flex: 1;
            text-align: center;
        }
        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
            position: relative;
        }
        nav a:hover {
            color: #007BFF;
        }
        .user-btn {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        .user-btn:hover {
            background-color: #0056b3;
        }
        .user-menu {
            display: flex;
            align-items: center;
        }
        main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            align-items: center; /* Align items vertically */
            margin-bottom: 40px;
        }
        .search-bar input[type="text"] {
            width: 100%;
            max-width: 600px;
            padding: 12px 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 25px 0 0 25px;
            outline: none;
            box-sizing: border-box; /* Ensure proper box model */
        }
        .search-bar button {
            background-color: #007bff;
            border: none;
            padding: 12px 20px;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            display: flex;
            align-items: center; /* Center the icon vertically */
            justify-content: center; /* Center the icon horizontally */
        }
        .search-bar button img {
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="CarRental Logo" style="height: 50px;">
            </a>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="cars.php">Our Cars</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="php/profile.php">My Profile</a>
                <a href="php/booking_history.php">Booking History</a>
            <?php endif; ?>
        </nav>

        <div class="user-menu">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span style="margin-right: 10px;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                <a href="php/logout.php" class="user-btn">Logout</a>
            <?php else: ?>
                <a href="php/login.php" class="user-btn">Login</a>
                <a href="php/register.php" class="user-btn">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>