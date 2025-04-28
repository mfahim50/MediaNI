<?php
// Konfigurasi Tripay
$apiKey       = 'API_KEY_KAMU';        // Ganti dengan API Key Tripay
$privateKey   = 'PRIVATE_KEY_KAMU';     // Ganti dengan Private Key Tripay
$merchantCode = 'MERCHANT_CODE_KAMU';   // Ganti dengan Merchant Code Tripay
$returnUrl    = 'https://drive.google.com/drive/folders/ID_FOLDER_KAMU'; // Link Google Drive tujuan

// Tangkap data dari form
$nama    = htmlspecialchars($_POST['nama']);
$nominal = intval($_POST['nominal']);

// Validasi data
if (empty($nama) || $nominal < 1000) {
    echo "Data tidak valid. Pastikan nama diisi dan nominal minimal Rp 1000.";
    exit;
}

// Persiapkan data untuk Tripay
$data = [
    'method'        => 'QRIS', // Ganti dengan metode pembayaran lain jika perlu
    'merchant_ref'  => 'DONASI' . time(),
    'amount'        => $nominal,
    'customer_name' => $nama,
    'customer_email'=> 'donatur@email.com', // Bisa diganti
    'order_items'   => [
        [
            'sku'         => 'DONASI',
            'name'        => 'Donasi Amal',
            'price'       => $nominal,
            'quantity'    => 1,
            'product_url' => 'https://yourwebsite.com',
            'image_url'   => '',
        ]
    ],
    'return_url'    => $returnUrl,
    'expired_time'  => (time() + (24 * 60 * 60)), // 24 jam dari sekarang
    'signature'     => hash_hmac('sha256', $merchantCode . $nominal . $merchantCode, $privateKey)
];

// Kirim request ke Tripay
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_FRESH_CONNECT  => true,
    CURLOPT_URL            => "https://tripay.co.id/api/transaction/create",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
    CURLOPT_FAILONERROR    => false,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($data)
]);

$response = curl_exec($curl);
$error = curl_error($curl);

curl_close($curl);

// Cek hasil
if ($error) {
    echo "Curl Error : " . $error;
} else {
    $result = json_decode($response, true);
    if ($result['success'] === true) {
        // Redirect ke halaman pembayaran Tripay
        header('Location: ' . $result['data']['checkout_url']);
        exit;
    } else {
        echo "Gagal membuat transaksi!<br>";
        echo "Pesan: " . $result['message'];
    }
}
?>
