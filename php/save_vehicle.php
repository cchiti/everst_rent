<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Validate required fields
$required = ['make', 'model', 'year', 'license', 'daily_rate', 'status'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        die("Error: Missing required field '$field'");
    }
}

// Process form data
$make = trim($_POST['make']);
$model = trim($_POST['model']);
$year = (int)$_POST['year'];
$license = trim($_POST['license']);
$color = trim($_POST['color'] ?? '');
$daily_rate = (float)$_POST['daily_rate'];
$status = $_POST['status'];
$description = trim($_POST['description'] ?? '');

// Validate and process new fields
$mileage = isset($_POST['mileage']) ? intval($_POST['mileage']) : 0;
$seating_capacity = isset($_POST['seating_capacity']) ? intval($_POST['seating_capacity']) : 0;
$transmission = isset($_POST['transmission']) ? $_POST['transmission'] : '';
$fuel_type = isset($_POST['fuel_type']) ? $_POST['fuel_type'] : '';

// Validate thumbnail exists
if (empty($_FILES['thumbnail']['name'])) {
    http_response_code(400);
    die("Error: Thumbnail image is required");
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        die("Error: Could not create upload directory");
    }
}

// Begin database transaction
$conn->begin_transaction();

try {
    // Insert vehicle details
    $query = "INSERT INTO vehicles (make, model, year, license_plate, color, daily_rate, status, mileage, seating_capacity, transmission, fuel_type, description)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssissdsiisss', $make, $model, $year, $license, $color, $daily_rate, $status, $mileage, $seating_capacity, $transmission, $fuel_type, $description);
    
    if (!$stmt->execute()) {
        throw new Exception("Error saving vehicle: " . $stmt->error);
    }
    
    $vehicle_id = $stmt->insert_id;

    // Save features
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        $features = array_filter($_POST['features']); // Remove empty features
        error_log("Features to save: " . json_encode($features)); // Debugging
    
        $query = "INSERT INTO vehicle_features (vehicle_id, feature) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
    
        foreach ($features as $feature) {
            $stmt->bind_param('is', $vehicle_id, $feature);
            if (!$stmt->execute()) {
                error_log("Error saving feature '$feature': " . $stmt->error); // Debugging
                throw new Exception("Error saving feature '$feature': " . $stmt->error);
            }
        }
    }

    $stmt->close();

    // Process thumbnail
    $thumbnail = $_FILES['thumbnail'];
    $thumbnailExt = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($thumbnailExt, $allowedExts)) {
        throw new Exception("Invalid thumbnail format. Only JPG, PNG, GIF allowed.");
    }
    
    $thumbnailName = 'vehicle_' . $vehicle_id . '_thumb_' . uniqid() . '.' . $thumbnailExt;
    $thumbnailPath = $uploadDir . $thumbnailName;
    
    if (!move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
        error_log("Failed to upload thumbnail: " . $_FILES['thumbnail']['error']);
        error_log("Temporary file: " . $thumbnail['tmp_name']);
        error_log("Target path: " . $thumbnailPath);
        error_log('Thumbnail upload error code: ' . $_FILES['thumbnail']['error']);
error_log('Thumbnail tmp_name: ' . $_FILES['thumbnail']['tmp_name']);
error_log('Thumbnail destination: ' . $thumbnailPath);

        throw new Exception("Failed to upload thumbnail");
    } else {
        error_log("Thumbnail uploaded successfully: $thumbnailPath");
    }
    
    // Save thumbnail to database
    $query = "INSERT INTO vehicle_images (vehicle_id, image_path, is_primary) VALUES (?, ?, TRUE)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $vehicle_id, $thumbnailPath);
    
    if (!$stmt->execute()) {
        throw new Exception("Error saving thumbnail: " . $stmt->error);
    }
    $stmt->close();

    // Process additional images if any
    if (!empty($_FILES['additional_images']['name'][0])) {
        foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['additional_images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue; // Skip files with upload errors
            }
            
            $imageExt = strtolower(pathinfo($_FILES['additional_images']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($imageExt, $allowedExts)) {
                continue; // Skip invalid formats
            }
            
            $imageName = 'vehicle_' . $vehicle_id . '_' . uniqid() . '.' . $imageExt;
            $imagePath = $uploadDir . $imageName;
            
            if (move_uploaded_file($tmpName, $imagePath)) {
                error_log("Additional image uploaded successfully: $imagePath");
                $query = "INSERT INTO vehicle_images (vehicle_id, image_path) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('is', $vehicle_id, $imagePath);
                if (!$stmt->execute()) {
                    error_log("Failed to save additional image to database: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Failed to upload additional image: " . $_FILES['additional_images']['error'][$key]);
            }
        }
    }

    // Commit transaction if everything succeeded
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Vehicle added successfully!']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>