<?php
include 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize response array
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Get form data with sanitization
        $first_name = mysqli_real_escape_string($conn, $_POST['firstName']);
        $last_name = mysqli_real_escape_string($conn, $_POST['lastName']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        $dob = mysqli_real_escape_string($conn, $_POST['dob']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $license_number = mysqli_real_escape_string($conn, $_POST['license']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        // Validate role against ENUM values
        $allowed_roles = ['staff', 'customer', 'mechanic'];
        $role = in_array($_POST['role'], $allowed_roles) ? $_POST['role'] : 'customer';

        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || 
            empty($dob) || empty($phone) || empty($license_number) || empty($address)) {
            throw new Exception("All fields are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("Email is already registered");
        }
        $stmt->close();

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, dob, phone, license_number, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $hashed_password, $dob, $phone, $license_number, $address, $role);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "User registered successfully!";
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// If not a POST request, redirect
header("Location: manage_users.php");
exit();
?>