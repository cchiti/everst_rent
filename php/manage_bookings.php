<?php
session_start();
include 'db_connect.php';

// Search and filter functionality
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Base query
$sql = "SELECT b.id, b.booking_date, b.start_date, b.end_date, b.total_cost, b.status, 
               u.first_name, u.last_name, u.email, u.phone, 
               v.make, v.model, v.license_plate
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id";

// Add conditions if filters are applied
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR v.make LIKE ? OR v.model LIKE ? OR v.license_plate LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    $types .= str_repeat('s', 6);
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $conditions[] = "b.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Combine conditions
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY b.booking_date DESC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
        }
        
        .btn {
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-group {
            position: relative;
            flex: 2;
        }
        
        .search-group input {
            padding-left: 40px;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 10px;
            color: #777;
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .bookings-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e1e5eb;
        }
        
        .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid #e1e5eb;
            vertical-align: top;
        }
        
        .bookings-table tr:hover {
            background-color: #f8f9fa;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }.status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-active {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view {
            background-color: #6c757d;
        }
        
        .btn-approve {
            background-color: var(--success);
        }
        
        .btn-cancel {
            background-color: var(--danger);
        }

        .no-bookings {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .bookings-table {
                display: block;
                overflow-x: auto;
            }
        }
        .styled-table {
    width: 100%;
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    overflow: hidden;
}

.styled-table thead tr {
    background-color: #3498db;
    color: #ffffff;
    text-align: left;
}

.styled-table th,
.styled-table td {
    padding: 15px 20px;
}

.styled-table tbody tr {
    border-bottom: 1px solid #e1e5eb;
    transition: all 0.2s;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f8fafc;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid #3498db;
}

.styled-table tbody tr:hover {
    background-color: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.customer-cell, .vehicle-cell {
    min-width: 200px;
}

.customer-info, .vehicle-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.customer-meta, .vehicle-meta {
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-size: 0.85em;
    color: #64748b;
}

.customer-meta i, .vehicle-meta i {
    margin-right: 5px;
    color: #94a3b8;
}

.date-cell {
    min-width: 180px;
}

.date-range {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.date-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.date-separator {
    color: #94a3b8;
    font-size: 0.8em;
    text-align: center;
    margin: 3px 0;
}

.price-cell {
    font-weight: 600;
    color: #1e40af;
    text-align: right;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background-color: #dcfce7;
    color: #166534;
}

.status-active {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-completed {
    background-color: #e0f2fe;
    color: #075985;
}

.status-cancelled {
    background-color: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-view, .btn-approve, .btn-cancel {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: white;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-view {
    background-color: #64748b;
}

.btn-view:hover {
    background-color: #475569;
    transform: scale(1.1);
}

.btn-approve {
    background-color: #10b981;
}

.btn-approve:hover {
    background-color: #059669;
    transform: scale(1.1);
}

.btn-cancel {
    background-color: #ef4444;
}

.btn-cancel:hover {
    background-color: #dc2626;
    transform: scale(1.1);
}

.no-bookings {
    text-align: center;
    padding: 40px 20px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    margin-top: 20px;
}

.no-bookings i {
    color: #94a3b8;
    margin-bottom: 15px;
}

.no-bookings h3 {
    color: #334155;
    margin-bottom: 10px;
}

.no-bookings p {
    color: #64748b;
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #3498db;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.btn:hover {
    background-color: #2563eb;
}

@media (max-width: 768px) {
    .styled-table {
        display: block;
        overflow-x: auto;
    }
    
    .customer-cell, .vehicle-cell {
        min-width: 180px;
    }
}

.bookingPopup {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.bookingPopup-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 25px;
    border-radius: 8px;
    width: 50%;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
}

.bookingPopup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.bookingPopup-header h2 {
    margin: 0;
    color: #2c3e50;
}

.bookingPopup-body p {
    margin: 12px 0;
    line-height: 1.6;
}

.bookingPopup-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.close-bookingPopup {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #777;
    transition: color 0.2s;
}

.close-bookingPopup:hover {
    color: #333;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-secondary:hover {
    background-color: #5a6268;
}
    </style>

<body>
<div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-alt"></i> Manage Bookings</h1>
        </div>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="all">All Statuses</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                      </div>
                
                <div class="filter-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bookings-content">
    <?php if ($result->num_rows > 0): ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Vehicle Details</th>
                    <th>Booking Period</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($row['id']) ?></td>
                        <td class="customer-cell">
                            <div class="customer-info">
                                <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                                <div class="customer-meta">
                                    <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?></span>
                                    <span><i class="fas fa-phone"></i> <?= htmlspecialchars($row['phone']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="vehicle-cell">
                            <div class="vehicle-info">
                                <strong><?= htmlspecialchars($row['make'] . ' ' . $row['model']) ?></strong>
                                <div class="vehicle-meta">
                                    <span><i class="fas fa-car"></i> <?= htmlspecialchars($row['license_plate']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="date-cell">
                            <div class="date-range">
                                <div class="date-item">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('M j, Y', strtotime($row['start_date'])) ?>
                                </div>
                                <div class="date-separator">to</div>
                                <div class="date-item">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('M j, Y', strtotime($row['end_date'])) ?>
                                </div>
                            </div>
                        </td>
                        <td class="price-cell">
                            $<?= number_format($row['total_cost'], 2) ?>
                        </td>
                        <td class="status-cell">
                            <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                                <?= ucfirst(htmlspecialchars($row['status'])) ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                        <button 
    type="button" 
    class="btn-view editBookingButton" 
    data-booking-id="<?= $row['id'] ?>" 
    onclick="openBookingPopup(<?= $row['id'] ?>)"
    title="Edit Booking">
    <i class="fas fa-eye"></i>
</button>
</td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>    

    <?php else: ?>
        <div class="no-bookings">
            <i class="fas fa-calendar-times fa-3x"></i>
            <h3>No Bookings Found</h3>
            <p>There are currently no bookings matching your criteria.</p>
            <a href="manage_bookings.php" class="btn btn-primary">Clear Filters</a>
        </div>
    <?php endif; ?>
</div>

<!-- Booking Details Popup -->
<div id="editBookingPopup" class="bookingPopup">
    <div class="bookingPopup-content">
        <div class="bookingPopup-header">
            <h2>Edit Booking Details</h2>
            <button class="close-bookingPopup" onclick="closeBookingPopup()">&times;</button>
        </div>
        <form id="updateBookingForm">
            <div class="bookingPopup-body">
                <input type="hidden" id="popupBookingIdInput" name="booking_id">
                <div class="form-group">
                    <label for="popupStartDate">Start Date:</label>
                    <input type="date" id="popupStartDate" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="popupEndDate">End Date:</label>
                    <input type="date" id="popupEndDate" name="end_date" required>
                </div>
                <div class="form-group">
                    <label for="popupStatus">Status:</label>
                    <select id="popupStatus" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="popupTotalCost">Total Cost:</label>
                    <input type="number" id="popupTotalCost" name="total_cost" step="0.01" required>
                </div>
            </div>
            <div class="bookingPopup-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBookingPopup()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitBookingForm()">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBookingPopup(bookingId) {
    console.log('Button clicked! Booking ID:', bookingId); // Debugging

    const bookingPopup = document.getElementById('editBookingPopup');

    // Fetch booking details via AJAX
    fetch(`get_booking_details.php?id=${bookingId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Booking Details:', data); // Debugging

            // Populate modal fields with booking details
            document.getElementById('popupBookingIdInput').value = data.id;
            document.getElementById('popupStartDate').value = data.start_date;
            document.getElementById('popupEndDate').value = data.end_date;
            document.getElementById('popupStatus').value = data.status.toLowerCase(); // Ensure lowercase for matching option values
            document.getElementById('popupTotalCost').value = data.total_cost;

            // Show the popup
            bookingPopup.style.display = 'block';
        })
        .catch(error => console.error('Error fetching booking details:', error));
}

function closeBookingPopup() {
    const bookingPopup = document.getElementById('editBookingPopup');
    console.log('Close button clicked'); // Debugging
    bookingPopup.style.display = 'none';
}

function submitBookingForm() {
    const updateBookingForm = document.getElementById('updateBookingForm');
    const bookingPopup = document.getElementById('editBookingPopup');

    const formData = new FormData(updateBookingForm);

    // Send updated details to the server
    fetch('update_booking.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking updated successfully!');
                bookingPopup.style.display = 'none';
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Error updating booking: ' + data.error);
            }
        })
        .catch(error => console.error('Error updating booking:', error));
}
</script>

</body>
</html>