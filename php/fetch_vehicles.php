<?php
include 'db_connect.php';

// Fetch vehicles data from the database
$sql = "SELECT id, make, model, year, license_plate, color, daily_rate, status FROM vehicles";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['make']}</td>
                <td>{$row['model']}</td>
                <td>{$row['year']}</td>
                <td>{$row['license_plate']}</td>
                <td>{$row['color']}</td>
                <td>\${$row['daily_rate']}</td>
                <td>{$row['status']}</td>
                <td>
                    <button class='btn-edit' data-id='{$row['id']}'>Edit</button>
                    <button class='btn-delete' data-id='{$row['id']}'>Delete</button>";
        if ($row['status'] === 'maintenance') {
            echo "<button class='btn-repair' data-id='{$row['id']}'>Repair</button>";
        }
        echo "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No vehicles found.</td></tr>";
}
?>

<style>
    /* Modal container */
    .modalRepair {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        overflow-y: auto;
    }

    /* Modal content */
    .modalRepair .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 25px;
        border-radius: 8px;
        width: 50%;
        max-width: 600px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.3s ease-out;
        position: relative;
    }

    /* Modal header */
    .modalRepair .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .modalRepair .modal-header h2 {
        margin: 0;
        color: #333;
        font-size: 1.5rem;
    }

    /* Close button */
    .modalRepair .close-btn {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
    }

    .modalRepair .close-btn:hover {
        color: #333;
    }

    /* Form styling */
    .modalRepair form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .modalRepair .form-group {
        margin-bottom: 15px;
    }

    .modalRepair label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #444;
    }

    .modalRepair input,
    .modalRepair select,
    .modalRepair textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .modalRepair input:focus,
    .modalRepair select:focus,
    .modalRepair textarea:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
    }

    .modalRepair textarea {
        min-height: 100px;
        resize: vertical;
    }

    /* Button styling */
    .modalRepair .btn {
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .modalRepair .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    .modalRepair .btn-primary:hover {
        background-color: #0069d9;
    }

    /* Status-specific styling */
    .modalRepair select[name="repair_status"] option[value="pending"] {
        color: #ffc107;
    }

    .modalRepair select[name="repair_status"] option[value="in_progress"] {
        color: #17a2b8;
    }

    .modalRepair select[name="repair_status"] option[value="completed"] {
        color: #28a745;
    }

    /* Priority-specific styling */
    .modalRepair select[name="priority"] option[value="low"] {
        color: #28a745;
    }

    .modalRepair select[name="priority"] option[value="moderate"] {
        color: #ffc107;
    }

    .modalRepair select[name="priority"] option[value="high"] {
        color: #dc3545;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modalRepair .modal-content {
            width: 85%;
            margin: 10% auto;
            padding: 15px;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    button{
        margin-top:5px;
    }
</style>

<!-- Repair Modal -->
<div id="repairModal" class="modalRepair" style="display:none;">
    <div class="modal-content">
        <span class="close-btn" id="closeRepairModal">&times;</span>
        <h2>Create Maintenance Request</h2>
        <form id="repairForm">
            <input type="hidden" id="repair_vehicle_id" name="vehicle_id">
            <div>
                <label>Repair Status:</label>
                <select name="repair_status" required>
    <option value="pending">Pending</option>
    <option value="in_progress">In Progress</option>
    <option value="completed">Completed</option>
</select>
            </div>
            <div>
                <label>Description:</label>
                <textarea name="description" required></textarea>
            </div>
            <div>
                <label>Priority:</label>
                <select name="priority" required>
                    <option value="low">Low</option>
                    <option value="moderate">Moderate</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div>
                <label>Assign Mechanic:</label>
                <select name="assigned_to" required>
                    <?php
                    // Fetch mechanic list from the database
                    $mechanics_sql = "SELECT id, first_name, last_name FROM users WHERE role = 'mechanic'";
                    $mechanics_result = $conn->query($mechanics_sql);
                    while ($mechanic = $mechanics_result->fetch_assoc()) {
                        echo "<option value='{$mechanic['id']}'>{$mechanic['first_name']} {$mechanic['last_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
            <button type="button" class="btn btn-primary" onclick="createMaintenanceRequest()">Create Maintenance Request</button>            </div>
        </form>
    </div>
</div>

<script>
// Open Repair Modal
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-repair')) {
        const vehicleId = e.target.getAttribute('data-id');
        document.getElementById('repair_vehicle_id').value = vehicleId;
        document.getElementById('repairModal').style.display = 'block';
    }
});

// Close Modal
document.getElementById('closeRepairModal').addEventListener('click', function() {
    document.getElementById('repairModal').style.display = 'none';
});


function createMaintenanceRequest() {
    const vehicleId = document.getElementById('repair_vehicle_id').value;
    const repairStatus = document.querySelector('[name="repair_status"]').value;
    const description = document.querySelector('[name="description"]').value;
    const priority = document.querySelector('[name="priority"]').value;
    const assignedTo = document.querySelector('[name="assigned_to"]').value;

    // Ensure all required fields are filled out
    if (!repairStatus || !description || !priority || !assignedTo) {
        alert("Please fill in all fields.");
        return;
    }

    // Create FormData for Ajax request
    const formData = new FormData();
    formData.append('vehicle_id', vehicleId);
    formData.append('repair_status', repairStatus);
    formData.append('description', description);
    formData.append('priority', priority);
    formData.append('assigned_to', assignedTo);

    // Make an Ajax call to backend to create maintenance request
    fetch('create_maintenance_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);  // Display server response
        document.getElementById('repairModal').style.display = 'none';  // Close modal
        alert('Maintenance request created successfully!');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred.');
    });
}
</script>
