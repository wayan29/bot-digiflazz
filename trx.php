<?php
date_default_timezone_set('Asia/Makassar');
// Load data from data.json file if exists
if (file_exists('data.json')) {
    $data = json_decode(file_get_contents('data.json'), true);
    $username = $data['username'] ?? '';
    $apikey = $data['apikey'] ?? '';
$kunci = $apikey;
} else {
    $username = '';
    $apikey = '';
}
// Prompt user to enter values if they are not set
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

$endpointsal = 'https://api.digiflazz.com/v1/cek-saldo';
$cmd1 = 'deposit';
$sign = md5($username .$kunci . 'depo');

$data = array(
    'cmd' => $cmd1,
    'username' => $username,
    'sign' => $sign
);

$options = array(
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data)
    )
);

$context = stream_context_create($options);
$response = file_get_contents($endpointsal, false, $context);
$json = json_decode($response, true);

if (isset($json['data']['deposit'])) {
    $saldo = $json['data']['deposit'];
    echo 'Saldo Anda adalah: Rp ' . number_format($saldo, 0, ',', '.') ."\n";
} else {
    echo 'Error: ' . $json['message'];
}
//Program cek prepaid product
$endpoint = 'https://api.digiflazz.com/v1/price-list';
$cmd = 'prepaid';
$sign = md5($username .$apikey . 'pricelist');

$data = array(
    'cmd' => $cmd,
    'username' => $username,
    'sign' => $sign
);

$options = array(
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data)
    )
);

$context = stream_context_create($options);
$response = file_get_contents($endpoint, false, $context);
$json = json_decode($response, true);

if (isset($json['data'])) {
    $data = $json['data'];

    // Menampilkan semua kategori yang tersedia
    $categories = array();
    foreach ($data as $item) {
        if (!in_array($item['category'], $categories)) {
            $categories[] = $item['category'];
        }
    }
    echo "Pilih Kategori:\n";
    for ($i = 0; $i < count($categories); $i++) {
        echo $i+1 . ". " . $categories[$i] . "\n";
    }
    $categoryIndex = readline("Masukkan nomor kategori: ");

    // Menampilkan semua brand yang sesuai dengan kategori yang dipilih
    $selectedCategory = $categories[$categoryIndex-1];
    $brands = array();
    foreach ($data as $item) {
        if ($item['category'] == $selectedCategory && !in_array($item['brand'], $brands)) {
            $brands[] = $item['brand'];
        }
    }
    echo "Brand yang tersedia untuk " . $selectedCategory . ":\n";
    for ($i = 0; $i < count($brands); $i++) {
        echo $i+1 . ". " . $brands[$i] . "\n";
    }
    $brandIndex = readline("Masukkan nomor brand: ");

    // Menampilkan semua product name yang sesuai dengan brand yang dipilih
    $selectedBrand = $brands[$brandIndex-1];
    $productNames = array();
    foreach ($data as $item) {
        if ($item['category'] == $selectedCategory && $item['brand'] == $selectedBrand && !in_array($item['product_name'], $productNames)) {
            $productNames[] = $item['product_name'];
        }
    }
    echo "Product Name yang tersedia untuk " . $selectedBrand . ":\n";
    for ($i = 0; $i < count($productNames); $i++) {
        echo $i+1 . ". " . $productNames[$i] . "\n";
    }
    $productNameIndex = readline("Masukkan nomor product name: ");

    // Menampilkan detail harga dan SKU dari product name yang dipilih
    $selectedProductName = $productNames[$productNameIndex-1];
    foreach ($data as $item) {
        if ($item['category'] == $selectedCategory && $item['brand'] == $selectedBrand && $item['product_name'] == $selectedProductName) {
            $product = $item;
            break;
        }
    }

echo "Detail harga dan SKU untuk " . $selectedProductName . ":\n";
echo "Harga : Rp " . number_format($product['price'], 0, ',', '.') . "\n";
echo "SKU   : " . $product['buyer_sku_code'] . "\n";
echo "Seler : " .$product['seller_name']. "\n";
$buyer_sku_code =  $product['buyer_sku_code'];
if ($product['seller_product_status']) {
    echo "Seller product status : Aktif\n";
} else {
    echo "Seller product status : Nonaktif\n";
}

if ($product['buyer_product_status']) {
    echo "Buyer product status  : Aktif\n";
} else {
    echo "Buyer product status  : Nonaktif\n";
}


//trx Program
$trx = 'https://api.digiflazz.com/v1/transaction';
$ref_id = "REF" .date('YmdHis') ."WAYAN";
echo "Ref ID : " .$ref_id ."\n";

echo "Input customer_no: ";
$customer_no = trim(fgets(STDIN));


$sign2 = md5($username .$kunci .$ref_id);

$data = array(
    'username' => $username,
    'buyer_sku_code' => $buyer_sku_code,
    'customer_no' => $customer_no,
    'ref_id' => $ref_id,
    'sign' => $sign2
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $trx);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);


$response_json = json_decode($response, true);

if (isset($response_json['data']) && in_array($response_json['data']['status'], ['Sukses', 'Pending'])) {
    $data = $response_json['data'];
    $history_file = 'history.json';
    if (file_exists($history_file)) {
        $history_data = file_get_contents($history_file);
        $history_arr = json_decode($history_data, true);

        array_push($history_arr, $data);

        file_put_contents($history_file, json_encode($history_arr));
    } else {
        $history_arr = array($data);
        file_put_contents($history_file, json_encode($history_arr));
    }
}

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
}
?>
