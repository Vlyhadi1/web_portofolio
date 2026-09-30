<?php
declare(strict_types=1);

function vlyhady_config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }

    $localPath = __DIR__ . '/config.local.php';
    $local = is_file($localPath) ? require $localPath : [];
    if (!is_array($local)) {
        $local = [];
    }

    $read = static function (string $key, string $environment, string $default = '') use ($local): string {
        $value = getenv($environment);
        if ($value !== false && $value !== '') {
            return $value;
        }
        return (string)($local[$key] ?? $default);
    };

    $config = [
        'midtrans_server_key' => $read('midtrans_server_key', 'VLYHADY_MIDTRANS_SERVER_KEY'),
        'midtrans_environment' => $read('midtrans_environment', 'VLYHADY_MIDTRANS_ENVIRONMENT', 'sandbox'),
        'biteship_api_key' => $read('biteship_api_key', 'VLYHADY_BITESHIP_API_KEY'),
        'biteship_origin_postal_code' => $read('biteship_origin_postal_code', 'VLYHADY_BITESHIP_ORIGIN_POSTAL_CODE', '46196'),
        'biteship_couriers' => $read('biteship_couriers', 'VLYHADY_BITESHIP_COURIERS', 'jne,sicepat,jnt,anteraja,pos,tiki,ninja'),
    ];
    return $config;
}

function biteship_rates(array $items, string $destinationPostal): array
{
    $config = vlyhady_config();
    if ($config['biteship_api_key'] === '') {
        throw new RuntimeException('Kunci Biteship belum diatur di api/config.local.php.');
    }
    if (!preg_match('/^\d{5,6}$/', $config['biteship_origin_postal_code']) || !preg_match('/^\d{5,6}$/', $destinationPostal)) {
        throw new DomainException('Masukkan kode pos asal dan tujuan yang valid.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Ekstensi PHP cURL belum aktif.');
    }

    $packages = [];
    foreach ($items as $item) {
        foreach (['weight_grams', 'length_cm', 'width_cm', 'height_cm'] as $measurement) {
            if ((int)$item[$measurement] < 1) {
                throw new DomainException('Lengkapi berat dan dimensi produk di panel admin sebelum menghitung ongkir.');
            }
        }
        $packages[] = [
            'name' => $item['name'],
            'description' => $item['name'],
            'value' => (int)$item['price'],
            'length' => (int)$item['length_cm'],
            'width' => (int)$item['width_cm'],
            'height' => (int)$item['height_cm'],
            'weight' => (int)$item['weight_grams'],
            'quantity' => (int)$item['quantity'],
        ];
    }

    $body = json_encode([
        'origin_postal_code' => (int)$config['biteship_origin_postal_code'],
        'destination_postal_code' => (int)$destinationPostal,
        'couriers' => $config['biteship_couriers'],
        'items' => $packages,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $curl = curl_init('https://api.biteship.com/v1/rates/couriers');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $config['biteship_api_key'],
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $body,
    ]);
    $response = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if (!is_string($response) || $status < 200 || $status >= 300) {
        error_log('Biteship rates failed: HTTP ' . $status . ' ' . $curlError);
        throw new RuntimeException('Tarif kurir belum dapat dimuat. Periksa konfigurasi Biteship dan coba lagi.');
    }
    $result = json_decode($response, true);
    if (!is_array($result) || !is_array($result['pricing'] ?? null)) {
        throw new RuntimeException('Format tarif dari Biteship tidak dikenali.');
    }

    $rates = [];
    foreach ($result['pricing'] as $rate) {
        if (!is_array($rate) || (int)($rate['price'] ?? 0) < 0) {
            continue;
        }
        $rates[] = [
            'courier_code' => (string)($rate['courier_code'] ?? ''),
            'courier_name' => (string)($rate['courier_name'] ?? $rate['courier_code'] ?? ''),
            'service_code' => (string)($rate['courier_service_code'] ?? ''),
            'service_name' => (string)($rate['courier_service_name'] ?? ''),
            'price' => (int)$rate['price'],
            'duration' => (string)($rate['duration'] ?? ''),
        ];
    }
    return $rates;
}