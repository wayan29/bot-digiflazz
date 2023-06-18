<?php
echo "Masukkan Id pelanggan : ";
$noPelanggan = trim(fgets(STDIN));
// Data request
$requestData = array(
    "commands" => "pln-subscribe",
    "customer_no" => $noPelanggan
);

// Convert data to JSON
$jsonData = json_encode($requestData);

// Set headers
$headers = array(
    "Content-Type: application/json",
    "Content-Length: " . strlen($jsonData)
);

// API endpoint
$endpoint = "https://api.digiflazz.com/v1/transaction";

// Initialize cURL
$ch = curl_init($endpoint);

// Set cURL options
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($ch);

// Close cURL connection
curl_close($ch);

// Parse JSON response
$data = json_decode($response, true);

// Check if response contains data
if (isset($data['data'])) {
    // Extract relevant information
    $customerNo = $data['data']['customer_no'];
    $meterNo = $data['data']['meter_no'];
    $subscriberId = $data['data']['subscriber_id'];
    $name = $data['data']['name'];
    $segmentPower = $data['data']['segment_power'];

    // Display the information
    echo "Customer No: " . $customerNo . "\n";
    echo "Meter No: " . $meterNo . "\n";
    echo "Subscriber ID: " . $subscriberId . "\n";
    echo "Name: " . $name . "\n";
    echo "Segment Power: " . $segmentPower . "\n";
} else {
    // No data found
    echo "No data found\n";
}

?>
