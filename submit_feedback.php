<?php
session_start();
include 'php/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to submit feedback.'); window.location.href = '/car_a/php/login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = intval($_POST['car_id']);
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback']);
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID

    // Validate inputs
    if ($rating < 1 || $rating > 5 || empty($feedback)) {
        echo "<script>alert('Invalid input. Please provide a valid rating and feedback.'); window.history.back();</script>";
        exit();
    }

    // Insert feedback into the database
    $stmt = $conn->prepare("INSERT INTO car_feedback (car_id, user_id, rating, feedback, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $car_id, $user_id, $rating, $feedback);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your feedback!'); window.location.href = 'book_car.php?id=$car_id';</script>";
    } else {
        echo "<script>alert('Error submitting feedback: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>