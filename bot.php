<?php
date_default_timezone_set('Asia/Makassar');

$url = "https://api.digiflazz.com";

// Load data from data.json file if exists
if (file_exists('data.json')) {
    $data = json_decode(file_get_contents('data.json'), true);
    $username = $data['username'] ?? '';
    $apikey = $data['apikey'] ?? '';
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

$endpointsal = $url .'/v1/cek-saldo';
$cmd1 = 'deposit';
$sign = md5($username .$apikey . 'depo');

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
    echo 'Saldo Anda adalah: Rp ' . number_format($saldo, 0, ',', '.') . "\n";
} else {
    echo 'Error: ' . $json['message'];
}

// Program cek prepaid product
$endpoint = $url .'/v1/price-list';
$cmd = 'prepaid';
$sign = md5($username . $apikey . 'pricelist');

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
        echo $i + 1 . ". " . $categories[$i] . "\n";
    }
    $categoryIndex = readline("Masukkan nomor kategori: ");

    // Menampilkan semua brand yang sesuai dengan kategori yang dipilih
    $selectedCategory = $categories[$categoryIndex - 1];
    $brands = array();
    foreach ($data as $item) {
        if ($item['category'] == $selectedCategory && !in_array($item['brand'], $brands)) {
            $brands[] = $item['brand'];
        }
    }
    echo "Pilih Brand:\n";
    for ($i = 0; $i < count($brands); $i++) {
        echo $i + 1 . ". " . $brands[$i] . "\n";
    }
    $brandIndex = readline("Masukkan nomor brand: ");

    // Menampilkan semua produk yang sesuai dengan kategori dan brand yang dipilih
    $selectedBrand = $brands[$brandIndex - 1];
    $products = array();
    foreach ($data as $item) {
        if ($item['category'] == $selectedCategory && $item['brand'] == $selectedBrand) {
            $products[] = $item;
        }
    }
    echo "Pilih Produk:\n";
    for ($i = 0; $i < count($products); $i++) {
        echo $i + 1 . ". " . $products[$i]['product_name'] . "\n";
    }
    $productIndex = readline("Masukkan nomor produk: ");
    
    // Menampilkan detail produk yang dipilih
    $selectedProduct = $products[$productIndex - 1];
    echo "Detail Produk:\n";
    echo "Harga: " . number_format($selectedProduct['price'] , 0, ',', '.') . "\n";
    echo "SKU: " . $selectedProduct['buyer_sku_code'] . "\n";
    echo "Nama Penjual: " . $selectedProduct['seller_name'] . "\n";
    //echo "Status: " . $selectedProduct['status'] . "\n";
    if ($selectedProduct['seller_product_status']) {
        echo "Seller product status : Aktif\n";
    } else {
        echo "Seller product status : Nonaktif\n";
    }
    
    if ($selectedProduct['buyer_product_status']) {
        echo "Buyer product status  : Aktif\n";
    } else {
        echo "Buyer product status  : Nonaktif\n";
    }
    // Proses transaksi
    $endpointTransaksi = $url .'/v1/transaction';
    //$refId = uniqid();
    $refId = "REF" .date('YmdHis') ."WAYAN";
    echo "Ref ID : " .$refId ."\n";
    $customerNumber = readline("Masukkan nomor pelanggan: ");

    $dataTransaksi = array(
        'ref_id' => $refId,
        'username' => $username,
        'buyer_sku_code' => $selectedProduct['buyer_sku_code'],
        'customer_no' => $customerNumber,
        'sign' => md5($username . $apikey . $refId)
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpointTransaksi);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataTransaksi));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_close($ch);
    $responseTransaksi = curl_exec($ch);
    $jsonTransaksi = json_decode($responseTransaksi, true);

    if (isset($jsonTransaksi['data']) && in_array($jsonTransaksi['data']['status'], ['Sukses', 'Pending'])) {
        $data = $jsonTransaksi['data'];
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
    
    if ($jsonTransaksi['data']['status'] === 'Pending') {
        echo "Ref ID: " . $jsonTransaksi['data']['ref_id'] . "\n";
        echo "Customer No: " . $jsonTransaksi['data']['customer_no'] . "\n";
        echo "Buyer SKU Code: " . $jsonTransaksi['data']['buyer_sku_code'] . "\n";
        echo "Message: " . $jsonTransaksi['data']['message'] . "\n";
        echo "Status: " . $jsonTransaksi['data']['status'] . "\n";
        //echo "RC: " . $jsonTransaksi['data']['rc'] . "\n";
        //echo "SN: " . $jsonTransaksi['data']['sn'] . "\n";
        echo "Buyer Last Saldo: RP " . number_format($jsonTransaksi['data']['buyer_last_saldo'], 0, ',', '.') . "\n";
        echo "Price: RP " . number_format($jsonTransaksi['data']['price'], 0, ',', '.') . "\n";
        //echo "Tele: " . $jsonTransaksi['data']['tele'] . "\n";
        //echo "WA: " . $jsonTransaksi['data']['wa'] . "\n";
    } elseif ($jsonTransaksi['data']['status'] === 'Gagal') {
        echo "Message: " . $jsonTransaksi['data']['message'] . "\n";
        echo "Status: " . $jsonTransaksi['data']['status'] . "\n";
    } else {
        echo "Gagal Terhubung Ke Server!\n";
    }
    
    
}
?>
