<?php

//warna
$MT = "\033[1;31m";
$BT = "\033[1;34m";
$HT = "\033[1;32m";
$KT = "\033[1;33m";
$BM = "\033[1;96m";
$PT = "\033[1m";

// Load data from data.json file if exists
if (file_exists('data.json')) {
    $data = json_decode(file_get_contents('data.json'), true);
    $username = $data['username'] ?? '';                          $apikey = $data['apikey'] ?? '';
$kunci = $apikey;                                             } else {
    $username = '';                                               $apikey = '';
}                                                             // Prompt user to enter values if they are not set
if (!$username) {
    echo "Enter User Name: ";
    $username = trim(fgets(STDIN));
}
if (!$apikey) {
echo "Enter Apikey: ";
    $apikey = trim(fgets(STDIN));
}
 // Save data to data.json file
$data = [
    'username' => $username,
    'apikey' => $apikey,
 ];
file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));

$endpoint = 'https://api.digiflazz.com/v1/transaction';

// membaca data dari file history.json
$datafi = file_get_contents('history.json');

// decode data JSON menjadi array PHP
$datafi1 = json_decode($datafi, true);

echo "Pilih nomor urut ref_id yang ingin ditampilkan:\n";
foreach ($datafi1 as $key => $item) {
    echo ($key+1) . ". " . $item['ref_id'] . "\n";
}

echo "Masukkan pilihan Ref Id : ";
$selected = trim(fgets(STDIN));
$ref_id = $datafi1[$selected-1];

// mengambil data yang dibutuhkan dari variabel $ref_id
$ref_id_number = $ref_id['ref_id'];
$customer_no = $ref_id['customer_no'];
$buyer_sku_code = $ref_id['buyer_sku_code'];

// menampilkan data dengan variabel yang sesuai
echo "Data Ref Id: " . $ref_id_number . "\n";
echo "Customer Number: " . $customer_no . "\n";
echo "Buyer SKU Code: " . $buyer_sku_code . "\n";
echo "\n";
$sign = md5($username . $kunci . $ref_id_number);

$data = array(
    'username' => $username,
    'buyer_sku_code' => $buyer_sku_code,
    'customer_no' => $customer_no,
    'ref_id' => $ref_id_number,
    'sign' => $sign
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$response_json = json_decode($response, true);

if ($response_json['data']['status'] === 'Sukses') {
    echo "Ref ID: " . $response_json['data']['ref_id'] . "\n";
    echo "Customer No: " . $response_json['data']['customer_no'] . "\n";
    echo "Buyer SKU Code: " . $response_json['data']['buyer_sku_code'] . "\n";
    echo "Message: " . $response_json['data']['message'] . "\n";
    echo "Status: " . $response_json['data']['status'] . "\n";
    echo "RC: " . $response_json['data']['rc'] . "\n";
    echo "SN: " . $response_json['data']['sn'] . "\n";
    echo "Buyer Last Saldo: RP " . number_format($response_json['data']['buyer_last_saldo'], 0, ',', '.') . "\n";
    echo "Price: RP " . number_format($response_json['data']['price'], 0, ',', '.') . "\n";
    echo "Tele: " . $response_json['data']['tele'] . "\n";
    echo "WA: " . $response_json['data']['wa'] . "\n";
} elseif ($response_json['data']['status'] === 'Pending') {
    echo "Ref ID: " . $response_json['data']['ref_id'] . "\n";
    echo "Customer No: " . $response_json['data']['customer_no'] . "\n";
    echo "Buyer SKU Code: " . $response_json['data']['buyer_sku_code'] . "\n";
    echo "Message: " . $response_json['data']['message'] . "\n";
    echo "Status: " . $response_json['data']['status'] . "\n";
    echo "RC: " . $response_json['data']['rc'] . "\n";
    echo "SN: " . $response_json['data']['sn'] . "\n";
    echo "Buyer Last Saldo: RP " . number_format($response_json['data']['buyer_last_saldo'], 0, ',', '.') . "\n";
    echo "Price: RP " . number_format($response_json['data']['price'], 0, ',', '.') . "\n";
    echo "Tele: " . $response_json['data']['tele'] . "\n";
    echo "WA: " . $response_json['data']['wa'] . "\n";
} elseif ($response_json['data']['status'] === 'Gagal') {
    //echo "Ref ID: " . $response_json['data']['ref_id'] . "\n";
    //echo "Customer No: " . $response_json['data']['customer_no'] . "\n";
    //echo "Buyer SKU Code: " . $response_json['data']['buyer_sku_code'] . "\n";
    echo "Message: " . $response_json['data']['message'] . "\n";
    echo "Status: " . $response_json['data']['status'] . "\n";
    //echo "RC: " . $response_json['data']['rc'] . "\n";
} else {
    echo "Gagal Terhubung Ke Server !\n";
}

curl_close($ch);
?>
