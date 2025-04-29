<?php
// include 'header.php'; 

session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $return_url = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
    header("Location: login.php?return_url=$return_url");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// Fetch user data
$sql = "SELECT first_name, last_name, email, dob, phone, license_number, address FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL error: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $error_message = "User not found.";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = $_POST['dob'];
    $phone = trim($_POST['phone']);
    $license_number = trim($_POST['license_number']);
    $address = trim($_POST['address']);

    $update_sql = "UPDATE users SET first_name = ?, last_name = ?, dob = ?, phone = ?, license_number = ?, address = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ssssssi', $first_name, $last_name, $dob, $phone, $license_number, $address, $user_id);
    $updated = $update_stmt->execute();

    if ($updated) {
        $success_message = "Profile updated successfully!";
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['dob'] = $dob;
        $user['phone'] = $phone;
        $user['license_number'] = $license_number;
        $user['address'] = $address;
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}
?>

<main>
<div class="profile-container">
    <div class="profile-header">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo" width="100">
        </div>
        <h1>My Profile</h1>
    </div>

    <!-- Display success or error message -->
    <?php if ($success_message): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="profile-content">
        <div class="profile-form">
            <?php if ($user): ?>
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email (cannot edit):</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="license_number">License Registered Number:</label>
                    <input type="text" name="license_number" id="license_number" value="<?php echo htmlspecialchars($user['license_number']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea name="address" id="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <h2>Manage Your Profile</h2>
            <p>Keep your information up-to-date for a better rental experience. Changes will apply immediately.</p>

            <h3>Important Tips</h3>
            <ul>
                <li>Always keep your phone number current.</li>
                <li>License numbers must be valid for rentals.</li>
                <li>Correct address helps with verification faster.</li>
            </ul>

            <h3>Need Help?</h3>
            <p>Live chat & phone support 24/7—we’re here whenever you need us.</p>
        </div>
    </div>
</div>
</main>

<style>
    /* profile.css */

body {
    font-family: 'Poppins', sans-serif;
    background: #f2f6fc;
    margin: 0;
    padding: 0;
}

.profile-container {
    max-width: 1200px;
    margin: 40px auto;
    background: #ffffff;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
    border-radius: 12px;
    overflow: hidden;
    padding: 30px;
}

.profile-header {
    text-align: center;
    margin-bottom: 30px;
}

.profile-header img {
    margin-bottom: 10px;
}

.profile-header h1 {
    font-size: 32px;
    color: #333;
    margin-top: 0;
}

.success-message, .error-message {
    text-align: center;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
    font-size: 16px;
    width: 80%;
    margin: 20px auto;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
}

.profile-content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.profile-form {
    flex: 2;
}

.profile-info {
    flex: 1;
    background: #f9fbfd;
    padding: 20px;
    border-radius: 8px;
}

.profile-info h2 {
    margin-top: 0;
    color: #007BFF;
    font-size: 24px;
}

.profile-info ul {
    padding-left: 20px;
}

.profile-info li {
    margin-bottom: 8px;
    color: #555;
}

.profile-info p {
    color: #666;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: flex;
    gap: 20px;
}

.form-row .form-group {
    flex: 1;
}

label {
    display: block;
    margin-bottom: 6px;
    color: #555;
    font-weight: 600;
}

input[type="text"],
input[type="email"],
input[type="tel"],
input[type="date"],
textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: #fafafa;
    font-size: 16px;
}

textarea {
    resize: vertical;
}

input[disabled] {
    background-color: #eee;
}

.form-actions {
    text-align: center;
    margin-top: 20px;
}

.btn-primary {
    background-color: #007BFF;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: ease-in 0.3s;
}

.btn-primary:hover {
    background-color: #0056b3;
}

</style>


<?php include 'footer.php'; ?>
