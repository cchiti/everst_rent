<?php
session_start();

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="dashboard-container">
    <div class="sidebar">
        <h2>Staff Dashboard</h2>
        <ul>
            <li><a href="#" class="nav-link" data-page="user-manage">Dashboard</a></li>
            <li><a href="#" class="nav-link" data-page="manage_bookings">Manage Bookings</a></li>
            <li><a href="#" class="nav-link" data-page="manage_vehicles">Manage Vehicles</a></li>
            <li><a href="#" class="nav-link" data-page="view_reports">View Reports</a></li>
        </ul>
        <div class="bottom">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="main-content" id="main-content">
        <!-- Content will be loaded here dynamically -->
        <h1>Welcome, Staff Member!</h1>
        <p>Select an option from the sidebar to manage your tasks.</p>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load content when a nav link is clicked
    $('.nav-link').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        
        // Highlight active link
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        
        // Load the corresponding content
        if(page === 'dashboard') {
            $('#main-content').html(`
                <h1>Welcome, Staff Member!</h1>
                <p>Select an option from the sidebar to manage your tasks.</p>
            `);
        } else {
            $('#main-content').load(page + '.php');
        }
    });
});
</script>

</body>
</html>