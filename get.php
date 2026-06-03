<?php
$conn = new mysqli("localhost", "root", "", "water_quality");

$result = $conn->query("SELECT turbidity, temperature, tds FROM sensor_data ORDER BY id DESC LIMIT 1");

$data = $result->fetch_assoc();

echo json_encode($data);
?>
