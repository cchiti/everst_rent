<?php
session_start();

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db_connect.php';

// Fetch users from the database
$sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS username, email, phone, role, joinAt FROM users";
$result = $conn->query($sql);

if (!$result) {
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
        } #addUserBtn {
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
        }tr:hover {
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
        }.action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }@media (max-width: 768px) {
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
            overflow-y: auto; /* Enable vertical scrolling */
            padding-top: 50px; /* Add padding to push the modal slightly down */
        }
        
        .modal-2-content {
            background-color: #fff;
            margin: 0 auto; /* Center horizontally */
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            animation: modalopen 0.3s;
            max-height: 90vh; /* Limit the height of the modal */
            overflow-y: auto; /* Enable scrolling inside the modal */
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
      
    </style>
    
</head>
<body>

<div class="container">
    <!-- Top container -->
    <div class="top">
        <h3>Manage Users</h3>
        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">Add New User</button>
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td><?= htmlspecialchars($row['joinAt']) ?></td>
                            <td>
                                <!-- <button class="action-btn btn-edit" onclick="editUser(<?= $row['id'] ?>)">Edit</button> -->
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
                <input type="nu" id="phone" name="phone" required>
            </div>
            
            <!-- License Information -->
            <div class="form-group-2">
                <label for="license">License Registered Number *</label>
                <input type="text" id="license" name="license" required>
            </div>
            
            <!-- Address Information -->            
            <div class="form-group-2">
                <label for="address">Address *</label>
                <input type="text" id="address" name="address" required>
            </div>
            
         
            <!-- Account Type -->
            <div class="form-group-2">
                <label for="role">Account Type *</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">mechanic</option>
                    <option value="staff">staff</option>
                    <option value="user">customer</option>
                </select>
            </div>
            
            <!-- Form Actions -->
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
    modal.style.display = 'block'; // Show the modal
}

// Close the modal when the "Cancel" button or close icon is clicked
document.querySelector('.close-btn-2').addEventListener('click', closeModal2);
document.querySelector('.btn-cancel-2').addEventListener('click', closeModal2);

function closeModal2() {
    const modal = document.getElementById('addUserModal-2');
    modal.style.display = 'none'; // Hide the modal
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        // Send a DELETE request to the backend
        fetch(`delete_user.php?id=${userId}`, {
            method: 'GET', // Use GET for simplicity, but DELETE is more appropriate
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                window.location.reload(); // Refresh the page to reflect changes
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

function showSavedAlert() {
    // Prevent the default form submission
    event.preventDefault();
    
    // Get the form element
    const form = event.target;
    
    // Show a loading state (optional)
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    // Submit the form via AJAX
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
        // Show success message
        alert('Saved');
        
        // Close the modal (assuming you have this function)
        if (typeof closeModal2 === 'function') {
            closeModal2();
        }
        
        // Refresh the page to show the new user
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving user');
    })
    .finally(() => {
        // Restore button state
        submitBtn.textContent = originalBtnText;
        submitBtn.disabled = false;
    });
    
    // Return false to prevent normal form submission
    return false;
}
</script>

</body>
</html>