<?php
include 'db_connect.php';

$sql = "SELECT id, first_name, last_name FROM users WHERE role = 'mechanic'";
$result = $conn->query($sql);

$mechanics = [];
while ($row = $result->fetch_assoc()) {
    $mechanics[] = [
        'id' => $row['id'],
        'name' => $row['first_name'] . ' ' . $row['last_name']
    ];
}

echo json_encode($mechanics);
?>
