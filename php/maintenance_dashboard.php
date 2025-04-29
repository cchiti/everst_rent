<?php
session_start();
include 'db_connect.php';


// Fetch maintenance request data
$sql = "SELECT 
            r.id AS request_id, 
            v.make, 
            v.model, 
            v.license_plate, 
            r.description, 
            r.status, 
            r.cost,
            CONCAT(u.first_name, ' ', u.last_name) AS staff_name
        FROM maintenance_requests r 
        JOIN vehicles v ON r.vehicle_id = v.id
        LEFT JOIN users u ON r.requested_by = u.id"; // Get staff full name
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- For FontAwesome icons -->
</head>
<style>
/* Base Styles */
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --info-color: #1abc9c;
    --light-gray: #ecf0f1;
    --dark-gray: #7f8c8d;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f7fa;
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header Styles */
.top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.welcome-message h2 {
    margin: 0;
    color: var(--secondary-color);
    font-size: 1.8rem;
}

h1 {
    color: var(--secondary-color);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Button Styles */
.btn-logout {
    background-color: var(--danger-color);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
}

.btn-update-status {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-update-status:hover {
    background-color: #2980b9;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Table Styles */
.styled-table {
    width: 100%;
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    overflow: hidden;
}

.styled-table thead tr {
    background-color: var(--primary-color);
    color: white;
    text-align: left;
    font-weight: 600;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
}

.styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
    transition: background-color 0.2s;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f8f9fa;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid var(--primary-color);
}

.styled-table tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

/* Status Badges */
.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: capitalize;
}

.badge-pending {
    background-color: var(--warning-color);
    color: white;
}

.badge-in_progress {
    background-color: var(--info-color);
    color: white;
}

.badge-completed {
    background-color: var(--success-color);
    color: white;
}

.badge-cancelled {
    background-color: var(--danger-color);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .top-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .styled-table {
        display: block;
        overflow-x: auto;
    }
    
    .container {
        padding: 15px;
    }
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.styled-table tbody tr {
    animation: fadeIn 0.3s ease-out;
    animation-fill-mode: both;
}

.styled-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
.styled-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
.styled-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
/* Continue this pattern as needed */
</style><style>
/* Base Styles */
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --info-color: #1abc9c;
    --light-gray: #ecf0f1;
    --dark-gray: #7f8c8d;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f7fa;
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header Styles */
.top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.welcome-message h2 {
    margin: 0;
    color: var(--secondary-color);
    font-size: 1.8rem;
}

h1 {
    color: var(--secondary-color);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Button Styles */
.btn-logout {
    background-color: var(--danger-color);
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
}

.btn-update-status {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-update-status:hover {
    background-color: #2980b9;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Table Styles */
.styled-table {
    width: 100%;
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    overflow: hidden;
}

.styled-table thead tr {
    background-color: var(--primary-color);
    color: white;
    text-align: left;
    font-weight: 600;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
}

.styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
    transition: background-color 0.2s;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f8f9fa;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid var(--primary-color);
}

.styled-table tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

/* Status Badges */
.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: capitalize;
}

.badge-pending {
    background-color: var(--warning-color);
    color: white;
}

.badge-in_progress {
    background-color: var(--info-color);
    color: white;
}

.badge-completed {
    background-color: var(--success-color);
    color: white;
}

.badge-cancelled {
    background-color: var(--danger-color);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .top-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .styled-table {
        display: block;
        overflow-x: auto;
    }
    
    .container {
        padding: 15px;
    }
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.styled-table tbody tr {
    animation: fadeIn 0.3s ease-out;
    animation-fill-mode: both;
}

.styled-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
.styled-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
.styled-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
/* Continue this pattern as needed */

/* Modal styles */
.modal {
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border-radius: 10px;
    width: 400px;
    position: relative;
}

.close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
}

.btn-save {
    background-color: #28a745;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

</style>
<body>

<div class="container">
    <!-- Top Row: Welcome Message and Logout Button -->
    <div class="top-row">
        <div class="welcome-message">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
        </div>
        <div class="logout-btn">
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <h1><i class="fas fa-tools"></i> Maintenance Dashboard</h1>

    <!-- Table: Maintenance Requests Details -->
    <table class="styled-table">
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Vehicle</th>
            <th>Cost</th>
            <th>License Plate</th>
            <th>Problem Description</th>
            <th>Status</th>
            <th>Requested By</th> <!-- Added -->
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>#<?= htmlspecialchars($row['request_id']) ?></td>
            <td><?= htmlspecialchars($row['make'] . ' ' . $row['model']) ?></td>
            <td><?= htmlspecialchars($row['cost']) ?></td>
            <td><?= htmlspecialchars($row['license_plate']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>
                <span class="badge badge-<?= htmlspecialchars($row['status']) ?>">
                    <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                </span>
            </td>
            <td><?= htmlspecialchars($row['staff_name']) ?></td> <!-- Staff Full Name -->
            <td>
                <button 
                    type="button" 
                    class="btn-update-status" 
                    data-request-id="<?= $row['request_id'] ?>">
                    Update Status
                </button>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>


</div>

<!-- Update Status Modal -->
<div id="updateModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Update Maintenance Request</h2>
        <form id="updateForm">
            <input type="hidden" id="modal-request-id" name="request_id">
            
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>

            <label for="cost">Cost:</label>
            <input type="number" id="cost" name="cost" step="0.01" required>

            <button type="submit" class="btn-save">Save</button>
        </form>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script>
$(document).ready(function(){
    // Open modal on button click
    $('.btn-update-status').click(function(){
        const requestId = $(this).data('request-id');
        $('#modal-request-id').val(requestId);
        $('#updateModal').show();
    });

    // Close modal
    $('.close').click(function(){
        $('#updateModal').hide();
    });

    // Handle form submission
    $('#updateForm').submit(function(e){
        e.preventDefault();

        const requestId = $('#modal-request-id').val();
        const status = $('#status').val();
        const cost = $('#cost').val();

        $.ajax({
            url: 'update_request.php',
            method: 'POST',
            data: { request_id: requestId, status: status, cost: cost },
            success: function(response){
                alert('Maintenance request updated successfully!');
                location.reload(); // Reload page to show updated status and cost
            },
            error: function(){
                alert('Error updating maintenance request.');
            }
        });
    });
});

</script>

</body>
</html>
