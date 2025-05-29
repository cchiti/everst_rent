<?php
// db_connect.php

$servername = "";  //  server name (localhost for local XAMPP)
$username = "i9808830_q0cr1";          // default username for XAMPP
$password = "S.RVu9QHw0ImqMHs2YJ91";              // default password is empty for XAMPP
$database = "car_a";         

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
