<?php
// db_connect.php

//   ----------------------------------------------------------------

// -- to check the connection to the database after front-end load we can check with regisiter.php if there is error then it throws an error.
// -- if the connection is correct, we can use this file to connect to the database in other files

//   ----------------------------------------------------------------


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
