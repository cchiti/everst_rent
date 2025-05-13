<?php
session_start();



// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<style>
    .maintenance-container {
    font-family: 'Arial', sans-serif;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.maintenance-container h2 {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
}

.maintenance-list {
    overflow-x: auto;
}

.maintenance-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.maintenance-table thead {
    background-color: #2c3e50;
    color: white;
}

.maintenance-table th, 
.maintenance-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.maintenance-table th {
    font-weight: 600;
    position: sticky;
    top: 0;
}

.maintenance-table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Status badges */
.maintenance-table .status-pending {
    background-color: #f39c12;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}

.maintenance-table .status-completed {
    background-color: #2ecc71;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}

.maintenance-table .status-in-progress {
    background-color: #3498db;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}

/* Priority indicators */
.maintenance-table .priority-high {
    color: #e74c3c;
    font-weight: bold;
}

.maintenance-table .priority-medium {
    color: #f39c12;
    font-weight: bold;
}

.maintenance-table .priority-low {
    color: #2ecc71;
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .maintenance-table {
        display: block;
    }
    
    .maintenance-table thead {
        display: none;
    }
    
    .maintenance-table tbody, 
    .maintenance-table tr, 
    .maintenance-table td {
        display: block;
        width: 100%;
    }
    
    .maintenance-table tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
    }
    
    .maintenance-table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    
    .maintenance-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        padding-right: 10px;
        font-weight: bold;
        text-align: left;
    }
}
</style>
<div class="vehicles-container">
    <h1>Manage Vehicles</h1>
    
    <div class="vehicle-actions">
        <button id="add-vehicle-btn" class="btn-primary">Add New Vehicle</button>
        <div class="search-box">
            <input type="text" id="vehicle-search" placeholder="Search vehicles...">
            <button id="search-btn">Search</button>
        </div>
    </div>

    <div class="vehicle-list">
        <table class="vehicle-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>License</th>
                    <th>Color</th>
                    <th>Daily Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php include('fetch_vehicles.php'); ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Vehicle Modal -->
<div id="vehicle-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modal-title">Add New Vehicle</h2>
        <form id="vehicle-form">
            <input type="hidden" id="vehicle-id" name="id" value="">
            <input type="hidden" id="existing-thumbnail" name="existing_thumbnail" value="">

            <div class="form-group">
                <label for="make">Make*:</label>
                <input type="text" id="make" name="make" required>
            </div>
            
            <div class="form-group">
                <label for="model">Model*:</label>
                <input type="text" id="model" name="model" required>
            </div>
            
            <div class="form-group">
                <label for="year">Year*:</label>
                <input type="number" id="year" name="year" min="2000" max="<?= date('Y') + 1 ?>" required>
            </div>
            
            <div class="form-group">
                <label for="license">License Plate*:</label>
                <input type="text" id="license" name="license" required>
            </div>
            
            <div class="form-group">
                <label for="color">Color:</label>
                <input type="text" id="color" name="color">
            </div>

            <div class="form-group">
                <label for="mileage">Mileage (km):</label>
                <input type="number" id="mileage" name="mileage" min="0" placeholder="Enter mileage">
            </div>

            <div class="form-group">
                <label for="seating_capacity">Seating Capacity:</label>
                <input type="number" id="seating_capacity" name="seating_capacity" min="1" placeholder="Enter seating capacity">
            </div>

            <div class="form-group">
                <label for="transmission">Transmission:</label>
                <select id="transmission" name="transmission" required>
                    <option value="automatic">Automatic</option>
                    <option value="manual">Manual</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fuel_type">Fuel Type:</label>
                <select id="fuel_type" name="fuel_type" required>
                    <option value="petrol">Petrol</option>
                    <option value="diesel">Diesel</option>
                    <option value="electric">Electric</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="daily_rate">Daily Rate ($)*:</label>
                <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="status">Status*:</label>
                <select id="status" name="status" required>
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>

            <div class="form-group">
    <label for="description">Vehicle Description:</label>
    <textarea id="description" name="description" rows="4" placeholder="Enter vehicle description"></textarea>
</div>

            <div class="form-group">
                <label for="features">Vehicle Features:</label>
                <div id="features-container">
                    <!-- Dynamically add feature fields -->
                    <div class="feature-field">
                        <input type="text" name="features[]" placeholder="Enter feature" maxlength="50">
                    </div>
                </div>
                <button type="button" id="add-feature-btn" class="btn-secondary">Add Feature</button>
                <small class="form-text text-muted">You can add up to 10 features.</small>
            </div>
            
            <div class="form-group">
                <label for="thumbnail">Thumbnail Image:</label>
                <div id="thumbnail-preview-container"></div>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                <small class="form-text text-muted">
                    Only required when adding new vehicle. Leave empty to keep existing image.
                </small>
            </div>
            <div class="form-group">
                <label for="additional_images">Additional Images:</label>
                <div id="additional-images-preview"></div>
                <input type="file" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                <small class="form-text text-muted">
                    Select multiple images to add to existing ones.
                </small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Vehicle</button>
                <button type="button" class="btn-secondary" id="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="maintenance-container">
    <h2>Maintenance History</h2>
    <div class="maintenance-list">
        <table class="maintenance-table">
            <thead>
                <tr>
                    <th>Vehicle ID</th>
                    <th>Description</th>
                    <th>Cost</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Updated Date</th>
                </tr>
            </thead>
<tbody id="maintenanceBody">
                <?php include('fetch_maintenance.php'); ?>
            </tbody>
        </table>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadMaintenanceData() {
    $.ajax({
        url: 'fetch_maintenance.php', 
        method: 'GET',
        success: function(response) {
            $('#maintenanceBody').html(response);
        },
        error: function() {
            $('#maintenanceBody').html('<tr><td colspan="7">Please Refresh For New Data.</td></tr>');
        }
    });
}

// Call the function once the page loads
$(document).ready(function() {
    loadMaintenanceData();

    // Optionally refresh every 30 seconds
    setInterval(loadMaintenanceData, 30000);
});
</script>


<script>
$(document).ready(function() {
    const modal = $('#vehicle-modal');
    const addBtn = $('#add-vehicle-btn');
    const closeBtn = $('.close-btn, #cancel-btn');
    const form = $('#vehicle-form');
    const modalTitle = $('#modal-title');

    console.log('Modal display state on page load:', modal.css('display'));

    // Open modal for adding a new vehicle
    addBtn.click(function() {
        console.log('Add Vehicle button clicked'); // Debugging
        form.trigger('reset'); // Clear the form
        $('#vehicle-id').val(''); // Clear the hidden ID field
        modalTitle.text('Add New Vehicle');
        modal.css('display', 'flex'); // Show the modal
    });

    // Close modal
    closeBtn.click(function() {
        modal.css('display', 'none'); // Hide the modal
    });

    $(window).click(function(event) {
        if (event.target == modal[0]) {
            modal.css('display', 'none'); // Hide the modal when clicking outside
        }
    });

    // Open modal for editing a vehicle
    $(document).on('click', '.btn-edit', function() {
        const vehicleId = $(this).data('id');
        
        // Clear any existing previews
        $('#thumbnail-preview-container').empty();
        $('#additional-images-preview').empty();
        
        $.ajax({
            type: 'GET',
            url: 'get_vehicle.php',
            data: { id: vehicleId },
            dataType: 'json',
            success: function(vehicle) {
                // Populate form fields
                $('#vehicle-id').val(vehicle.id);
                $('#make').val(vehicle.make);
                $('#model').val(vehicle.model);
                $('#year').val(vehicle.year);
                $('#license').val(vehicle.license_plate);
                $('#color').val(vehicle.color);
                $('#daily_rate').val(vehicle.daily_rate);
                $('#status').val(vehicle.status);
                $('#mileage').val(vehicle.mileage);
                $('#seating_capacity').val(vehicle.seating_capacity);
                $('#transmission').val(vehicle.transmission);
                $('#fuel_type').val(vehicle.fuel_type);
                $('#description').val(vehicle.description);

                    // Populate features
    $('#features-container').empty();
    if (vehicle.features && vehicle.features.length > 0) {
        vehicle.features.forEach(function(feature) {
            $('#features-container').append(`
                <div class="feature-field">
                    <input type="text" name="features[]" value="${feature}" placeholder="Enter feature" maxlength="50">
                    <button type="button" class="remove-feature-btn">Remove</button>
                </div>
            `);
        });
    } else {
        $('#features-container').append(`
            <div class="feature-field">
                <input type="text" name="features[]" placeholder="Enter feature" maxlength="50">
            </div>
        `);
    }
                
                // Handle thumbnail image
                if (vehicle.thumbnail) {
                    $('#existing-thumbnail').val(vehicle.thumbnail);
                    $('#thumbnail-preview-container').html(`
                        <img src="${vehicle.thumbnail}" alt="Current Thumbnail" style="max-width: 100px; margin-bottom: 10px;">
                        <div class="form-check">
                            <input type="checkbox" id="change-thumbnail" name="change_thumbnail">
                            <label for="change-thumbnail">Change thumbnail</label>
                        </div>
                    `);
                }
                
                // Handle additional images
                if (vehicle.additional_images && vehicle.additional_images.length > 0) {
                    let html = '<div style="margin-bottom: 10px;">Current Additional Images:</div>';
                    vehicle.additional_images.forEach(function(image) {
                        html += `<img src="${image}" alt="Additional Image" style="max-width: 80px; margin-right: 5px;">`;
                    });
                    $('#additional-images-preview').html(html);
                }
                
                modalTitle.text('Edit Vehicle');
                modal.css('display', 'flex');
            },
            error: function() {
                alert('Error fetching vehicle details');
            }
        });
    });

    // Form submission for adding or editing a vehicle
    form.submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const isEdit = $('#vehicle-id').val() !== '';

        // Show loading indicator
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            type: 'POST',
            url: isEdit ? 'update_vehicle.php' : 'save_vehicle.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response, status, xhr) {
                console.log('Full response:', response);
                console.log('Status:', status);
                console.log('XHR:', xhr);
                
                if (response && response.success) {
                    alert(response.message);
                    modal.css('display', 'none');
                    $('.vehicle-list tbody').load('fetch_vehicles.php');
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                
                let errorMsg = 'Error saving vehicle';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = xhr.responseText || error;
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    errorMsg = xhr.responseText || error;
                }
                // close dialog
                modal.css('display', 'none');
                $('.vehicle-list tbody').load('fetch_vehicles.php');

            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Save Vehicle');
            }
        });
    });

    // Delete vehicle
    $(document).on('click', '.btn-delete', function() {
        if (confirm('Are you sure you want to delete this vehicle?')) {
            const vehicleId = $(this).data('id'); // Get the vehicle ID from the button

            $.ajax({
                type: 'POST',
                url: 'delete_vehicle.php', // Backend script to handle deletion
                data: { id: vehicleId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('.vehicle-list tbody').load('fetch_vehicles.php'); // Reload the vehicle list
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error deleting vehicle');
                    console.error(xhr.responseText);
                }
            });
        }
    });

    // Search functionality
    $('#search-btn').click(function() {
        const searchTerm = $('#vehicle-search').val();
        $.ajax({
            url: 'search_vehicles.php',
            data: {search: searchTerm},
            success: function(data) {
                $('.vehicle-list tbody').html(data);
            }
        });
    });

    const maxFeatures = 10;

    $('#add-feature-btn').click(function() {
        const featureFields = $('#features-container .feature-field').length;
        if (featureFields < maxFeatures) {
            $('#features-container').append(`
                <div class="feature-field">
                    <input type="text" name="features[]" placeholder="Enter feature" maxlength="50">
                    <button type="button" class="remove-feature-btn">Remove</button>
                </div>
            `);
        } else {
            alert('You can only add up to 10 features.');
        }
    });

    $(document).on('click', '.remove-feature-btn', function() {
        $(this).parent('.feature-field').remove();
    });
});
</script>