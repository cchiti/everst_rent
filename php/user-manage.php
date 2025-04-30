<?php
session_start();

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db_connect.php';

// Fetch total bookings
$total_bookings_sql = "SELECT COUNT(*) AS total_bookings FROM bookings";
$total_bookings_result = $conn->query($total_bookings_sql);
$total_bookings = $total_bookings_result->fetch_assoc()['total_bookings'];

// Fetch available vehicles
$available_vehicles_sql = "SELECT COUNT(*) AS available_vehicles FROM vehicles WHERE status = 'available'";
$available_vehicles_result = $conn->query($available_vehicles_sql);
$available_vehicles = $available_vehicles_result->fetch_assoc()['available_vehicles'];

// Fetch pending maintenance requests
$pending_maintenance_sql = "SELECT COUNT(*) AS pending_maintenance FROM maintenance_requests WHERE status = 'pending'";
$pending_maintenance_result = $conn->query($pending_maintenance_sql);
$pending_maintenance = $pending_maintenance_result->fetch_assoc()['pending_maintenance'];

// Fetch total reviews
$total_reviews_sql = "SELECT COUNT(*) AS total_reviews FROM car_feedback";
$total_reviews_result = $conn->query($total_reviews_sql);
$total_reviews = $total_reviews_result->fetch_assoc()['total_reviews'];


// Fetch reviews from the database
$review_sql = "
    SELECT 
        CONCAT(u.first_name, ' ', u.last_name) AS full_name,
        u.email,
        f.rating,
        f.feedback,
        f.created_at,
        v.model AS car_model
    FROM car_feedback f
    JOIN users u ON f.user_id = u.id
    JOIN vehicles v ON f.car_id = v.id
    ORDER BY f.created_at DESC
";
$review_result = $conn->query($review_sql);

// Fetch users from the database
$user_sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS username, email, phone, role, joinAt FROM users";
$user_result = $conn->query($user_sql);

if (!$review_result || !$user_result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .top h3 {
            font-size: 24px;
            color: #2c3e50;
        }
        
        #addUserBtn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        #addUserBtn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #3498db;
            color: white;
            font-weight: 500;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
            transition: all 0.2s;
        }
        
        .btn-edit {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            .top {
                flex-direction: column;
                align-items: flex-start;
            }
            
            #addUserBtn {
                margin-top: 10px;
            }
        }
        
        /* Modal-2 Styles */
        .modal-2 {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            padding-top: 50px;
        }
        
        .modal-2-content {
            background-color: #fff;
            margin: 0 auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            animation: modalopen 0.3s;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        @keyframes modalopen {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-2-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-2-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .close-btn-2 {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .form-group-2 {
            margin-bottom: 15px;
        }
        
        .form-group-2 label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-group-2 input,
        .form-group-2 select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-actions-2 {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn-2 {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }
        
        .btn-submit-2 {
            background-color: #3498db;
            color: white;
        }
        
        .btn-cancel-2 {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-2:hover {
            opacity: 0.9;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        
        .hint {
            color: #7f8c8d;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
    </style>

<style>
    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }
    
    .container h2 {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 25px;
        font-weight: 600;
    }
    
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .card {
        background-color: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }
    
    .booking-card::before {
        background: linear-gradient(90deg, #3498db, #2ecc71);
    }
    
    .vehicle-card::before {
        background: linear-gradient(90deg, #e74c3c, #f39c12);
    }
    
    .maintenance-card::before {
        background: linear-gradient(90deg, #9b59b6, #34495e);
    }
    
    .review-card::before {
        background: linear-gradient(90deg, #1abc9c, #27ae60);
    }
    
    .card-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .booking-card .card-icon {
        background-color: rgba(52, 152, 219, 0.1);
        color: #3498db;
    }
    
    .vehicle-card .card-icon {
        background-color: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
    }
    
    .maintenance-card .card-icon {
        background-color: rgba(155, 89, 182, 0.1);
        color: #9b59b6;
    }
    
    .review-card .card-icon {
        background-color: rgba(26, 188, 156, 0.1);
        color: #1abc9c;
    }
    
    .card-icon svg {
        width: 30px;
        height: 30px;
    }
    
    .card-content h3 {
        margin: 0 0 10px;
        font-size: 18px;
        font-weight: 500;
        color: #7f8c8d;
    }
    
    .card-content p {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
    }
    
    @media (max-width: 768px) {
        .summary-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .summary-cards {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>

<div class="container">
    <h2>Dashboard Summary</h2>
    <div class="summary-cards">
        <div class="card booking-card">
            <div class="card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/>
                </svg>
            </div>
            <div class="card-content">
                <h3>Total Bookings</h3>
                <p><?= htmlspecialchars($total_bookings) ?></p>
            </div>
        </div>
        <div class="card vehicle-card">
            <div class="card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.08 3.11H5.77L6.85 7zM19 17H5v-5h14v5z"/>
                </svg>
            </div>
            <div class="card-content">
                <h3>Available Vehicles</h3>
                <p><?= htmlspecialchars($available_vehicles) ?></p>
            </div>
        </div>
        <div class="card maintenance-card">
            <div class="card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.14 7.5c-.17-.15-.37-.29-.58-.41l-1.79 1.79c.12.21.26.41.41.58l1.78-1.78-.02-.02zm1.36 5.5c-.08-.49-.21-.96-.41-1.41l-1.8 1.8c.45.2.92.33 1.41.41l.8-1.8zm-18.01-8.48l1.8-1.8c-.49-.08-.96-.21-1.41-.41l-1.8 1.8c.2.45.33.92.41 1.41zm15.71 15.71l-1.79-1.79c-.21.12-.41.26-.58.41l1.78 1.78.02-.02.57-.38zm-9.69-2.28c-.85.24-1.72.39-2.61.45v1.49c1.32-.09 2.59-.35 3.8-.75l-1.19-1.19zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
            </div>
            <div class="card-content">
                <h3>Pending Maintenance</h3>
                <p><?= htmlspecialchars($pending_maintenance) ?></p>
            </div>
        </div>
        <div class="card review-card">
            <div class="card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c.61 0 1.1.49 1.1 1.1s-.49 1.1-1.1 1.1-1.1-.49-1.1-1.1.49-1.1 1.1-1.1z"/>
                </svg>
            </div>
            <div class="card-content">
                <h3>Total Reviews</h3>
                <p><?= htmlspecialchars($total_reviews) ?></p>
            </div>
        </div>
    </div>
</div>



<div class="container">
    <h3>Review From Customer</h3>

    <!-- Middle container -->
    <div class="middle">
        <!-- Review table -->
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Ratings</th>
                    <th>Car Model</th>
                    <th>Message</th>
                    <th>Review At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($review_result->num_rows > 0): ?>
                    <?php while ($row = $review_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['rating']) ?>/5</td>
                            <td><?= htmlspecialchars($row['car_model']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['feedback'])) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No reviews found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="container">
    <!-- Top container -->
    <div class="top">
        <h3>Manage Users</h3>
        <button id="addUserBtn" type="button" onclick="openAddUserModal()">Add New User</button>
    </div>

    <!-- Middle container -->
    <div class="middle">
        <!-- User table -->
        <table>
            <thead>
                <tr>
                    <th>FullName</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($user_result->num_rows > 0): ?>
                    <?php while ($row = $user_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td><?= htmlspecialchars($row['joinAt']) ?></td>
                            <td>
                                <button class="action-btn btn-delete" onclick="deleteUser(<?= $row['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal-2 -->
<div id="addUserModal-2" class="modal-2">
    <div class="modal-2-content">
        <div class="modal-2-header">
            <h3>Add New User</h3>
            <button class="close-btn-2">&times;</button>
        </div>
        <form action="reg.php" method="POST" class="register-form" onsubmit="return showSavedAlert(event)">          
            <div class="form-group-2">
                <label for="firstName">First Name *</label>
                <input type="text" id="firstName" name="firstName" required>
            </div>
            
            <div class="form-group-2">
                <label for="lastName">Last Name *</label>
                <input type="text" id="lastName" name="lastName" required>
            </div>
            
            <div class="form-group-2">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group-2">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required minlength="8">
                <small class="hint">Minimum 8 characters</small>
            </div>
            
            <div class="form-group-2">
                <label for="dob">Date of Birth *</label>
                <input type="date" id="dob" name="dob" required>
            </div>
            
            <div class="form-group-2">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            
            <div class="form-group-2">
                <label for="license">License Registered Number *</label>
                <input type="text" id="license" name="license" required>
            </div>
            
            <div class="form-group-2">
                <label for="address">Address *</label>
                <input type="text" id="address" name="address" required>
            </div>
            
            <div class="form-group-2">
                <label for="role">Account Type *</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="mechanic">Mechanic</option>
                    <option value="staff">Staff</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
            
            <div class="form-actions-2">
                <button type="button" class="btn-2 btn-cancel-2">Cancel</button>
                <button type="submit" class="btn-2 btn-submit-2">Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddUserModal() {
        const modal = document.getElementById('addUserModal-2');
        modal.style.display = 'block';
    }

    // Close the modal when the "Cancel" button or close icon is clicked
    document.querySelector('.close-btn-2').addEventListener('click', closeModal2);
    document.querySelector('.btn-cancel-2').addEventListener('click', closeModal2);

    function closeModal2() {
        const modal = document.getElementById('addUserModal-2');
        modal.style.display = 'none';
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`delete_user.php?id=${userId}`, {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    window.location.reload();
                } else {
                    alert('Error deleting user: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting user.');
            });
        }
    }

    function showSavedAlert(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            alert('Saved');
            closeModal2();
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving user');
        })
        .finally(() => {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        });
        
        return false;
    }

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('addUserModal-2');
        if (event.target === modal) {
            closeModal2();
        }
    });
</script>

</body>
</html>