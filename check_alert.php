<?php

// ===== DATABASE CONNECTION =====
$conn = new mysqli("localhost", "root", "", "water_quality");

if ($conn->connect_error) {
    die("Database Connection Failed");
}

// ===== GET LATEST SENSOR DATA =====
$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $tds = $row['tds'];
    $turbidity = $row['turbidity'];
    $temperature = $row['temperature']; // only for display

    // ===== ALERT CONDITION (NO TEMP CHECK) =====
    if ($tds > 450 || $turbidity > 120) {

      

        $message = "⚠ ALERT: Water Not Drinkable!\n";
        $message .= "TDS: $tds ppm\n";
        $message .= "Turbidity: $turbidity NTU\n";
        $message .= "Temperature: $temperature °C";

        $postData = [
            'From' =>$phonenumber
            'To'   =>$phonenumber
            'Body' => $message
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "SMS Sent";
         }

        curl_close($ch);
    } 
    else {
        echo "Water Safe";
    }

} else {
    echo "No Data Found";
}

$conn->close();
?>
