<?php
// db_connect.php

$servername = "localhost";  //  server name (localhost for local XAMPP)
$username = "root";          // default username for XAMPP
$password = "";              // default password is empty for XAMPP
$database = "car_a";         

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
