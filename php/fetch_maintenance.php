<?php
include('db_connect.php'); 

$query = "SELECT * FROM maintenance_requests ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "<tr><td colspan='6'>Error fetching maintenance data.</td></tr>";
    exit;
}

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$row['vehicle_id']}</td>
        <td>{$row['description']}</td>
        <td>\${$row['cost']}</td>
        <td>" . ucfirst($row['priority']) . "</td>
        <td>" . ucfirst($row['status']) . "</td>
        <td>{$row['created_at']}</td>
        <td>{$row['updated_at']}</td>
    </tr>";
}
?>
