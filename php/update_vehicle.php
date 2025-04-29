<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Process form data
$vehicleId = $_POST['id'];
$make = trim($_POST['make']);
$model = trim($_POST['model']);
$year = (int)$_POST['year'];
$license = trim($_POST['license']);
$color = trim($_POST['color'] ?? '');
$daily_rate = (float)$_POST['daily_rate'];
$status = $_POST['status'];
$existingThumbnail = $_POST['existing_thumbnail'] ?? '';
$description = trim($_POST['description'] ?? '');

// Validate and process new fields
$mileage = isset($_POST['mileage']) ? intval($_POST['mileage']) : 0;
$seating_capacity = isset($_POST['seating_capacity']) ? intval($_POST['seating_capacity']) : 0;
$transmission = isset($_POST['transmission']) ? $_POST['transmission'] : '';
$fuel_type = isset($_POST['fuel_type']) ? $_POST['fuel_type'] : '';

// Begin transaction
$conn->begin_transaction();

try {
    // Update vehicle details
    $query = "UPDATE vehicles SET 
              make = ?, model = ?, year = ?, license_plate = ?, 
              color = ?, daily_rate = ?, status = ?, mileage = ?, 
              seating_capacity = ?, transmission = ?, fuel_type = ?, description = ? 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssissdsiisssi', $make, $model, $year, $license, $color, $daily_rate, $status, $mileage, $seating_capacity, $transmission, $fuel_type, $description, $vehicleId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating vehicle: " . $stmt->error);
    }
    $stmt->close();

    // Handle thumbnail image update if provided
    if (!empty($_FILES['thumbnail']['name'])) {
        // Delete old thumbnail if exists
        if (!empty($existingThumbnail) && file_exists($existingThumbnail)) {
            unlink($existingThumbnail);
        }
        
        // Upload new thumbnail
        $thumbnail = $_FILES['thumbnail'];
        $thumbnailExt = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($thumbnailExt, $allowedExts)) {
            throw new Exception("Invalid thumbnail format. Only JPG, PNG, GIF allowed.");
        }
        
        $thumbnailName = 'vehicle_' . $vehicleId . '_thumb_' . uniqid() . '.' . $thumbnailExt;
        $thumbnailPath = '../uploads/' . $thumbnailName;
        
        if (!move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
            throw new Exception("Failed to upload thumbnail");
        }
        
        // Update thumbnail in database
        $query = "UPDATE vehicle_images SET image_path = ? 
                  WHERE vehicle_id = ? AND is_primary = TRUE";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $thumbnailPath, $vehicleId);
        
        if (!$stmt->execute()) {
            // If no existing thumbnail, insert new one
            $query = "INSERT INTO vehicle_images (vehicle_id, image_path, is_primary) VALUES (?, ?, TRUE)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('is', $vehicleId, $thumbnailPath);
            
            if (!$stmt->execute()) {
                throw new Exception("Error saving thumbnail: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    // Handle additional images if any
    if (!empty($_FILES['additional_images']['name'][0])) {
        foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['additional_images']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $imageExt = strtolower(pathinfo($_FILES['additional_images']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($imageExt, $allowedExts)) {
                continue;
            }
            
            $imageName = 'vehicle_' . $vehicleId . '_' . uniqid() . '.' . $imageExt;
            $imagePath = '../uploads/' . $imageName;
            
            if (move_uploaded_file($tmpName, $imagePath)) {
                $query = "INSERT INTO vehicle_images (vehicle_id, image_path) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('is', $vehicleId, $imagePath);
                
                if (!$stmt->execute()) {
                    error_log("Failed to save additional image: " . $stmt->error);
                }
                $stmt->close();
            }
        }
    }

    // Handle features update
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        $features = array_filter($_POST['features']); // Remove empty features

        // Delete existing features for the vehicle
        $query = "DELETE FROM vehicle_features WHERE vehicle_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $vehicleId);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting existing features: " . $stmt->error);
        }
        $stmt->close();

        // Insert new features
        if (!empty($features)) {
            $query = "INSERT INTO vehicle_features (vehicle_id, feature) VALUES (?, ?)";
            $stmt = $conn->prepare($query);

            foreach ($features as $feature) {
                $stmt->bind_param('is', $vehicleId, $feature);
                if (!$stmt->execute()) {
                    throw new Exception("Error saving feature '$feature': " . $stmt->error);
                }
            }
            $stmt->close();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>