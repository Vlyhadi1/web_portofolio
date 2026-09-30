<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/integrations.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body === false ? '' : $body, true);
    if (!is_array($data)) {
        respond(400, ['error' => 'Format permintaan tidak valid.']);
    }
    return $data;
}

function required_text(array $data, string $key, int $maxLength): string
{
    $value = trim((string)($data[$key] ?? ''));
    if ($value === '' || mb_strlen($value) > $maxLength) {
        respond(422, ['error' => 'Lengkapi semua informasi dengan benar.']);
    }
    return $value;
}

function cart_quantities(mixed $items): array
{
    if (!is_array($items) || count($items) === 0 || count($items) > 25) {
        respond(422, ['error' => 'Daftar produk pesanan tidak valid.']);
    }
    $quantities = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            respond(422, ['error' => 'Daftar produk tidak valid.']);
        }
        $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
        if ($id === false || $quantity === false) {
            respond(422, ['error' => 'Jumlah produk tidak valid.']);
        }
        $quantities[$id] = ($quantities[$id] ?? 0) + $quantity;
        if ($quantities[$id] > 99) {
            respond(422, ['error' => 'Jumlah produk melebihi batas.']);
        }
    }
    return $quantities;
}

function load_shipping_items(PDO $pdo, array $quantities): array
{
    $findProduct = $pdo->prepare(
        'SELECT id, name, price, stock, is_active, is_demo, weight_grams, length_cm, width_cm, height_cm
         FROM products WHERE id = :id'
    );
    $items = [];
    foreach ($quantities as $id => $quantity) {
        $findProduct->execute(['id' => $id]);
        $product = $findProduct->fetch();
        if (!$product || !(bool)$product['is_active']) {
            respond(409, ['error' => 'Salah satu produk sudah tidak tersedia. Muat ulang katalog.']);
        }
        if ((int)$product['stock'] < $quantity) {
            respond(409, ['error' => 'Stok ' . $product['name'] . ' tidak mencukupi.']);
        }
        $product['quantity'] = $quantity;
        $items[] = $product;
    }
    return $items;
}

try {
    $pdo = vlyhady_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = (string)($_GET['action'] ?? '');

    if ($method === 'GET' && $action === 'products') {
        $statement = $pdo->query(
            'SELECT id, name, category, price, rating, reviews, badge, image, alt,
                    description, material, dimensions, stock, is_demo
             FROM products WHERE is_active = 1 ORDER BY id'
        );
        $products = array_map(static function (array $product): array {
            $product['id'] = (int)$product['id'];
            $product['price'] = (int)$product['price'];
            $product['rating'] = $product['rating'] === null ? null : (float)$product['rating'];
            $product['reviews'] = (int)$product['reviews'];
            $product['stock'] = (int)$product['stock'];
            $product['is_demo'] = (bool)$product['is_demo'];
            return $product;
        }, $statement->fetchAll());
        respond(200, ['data' => $products]);
    }

    if ($method !== 'POST') {
        respond(405, ['error' => 'Metode permintaan tidak didukung.']);
    }

    $input = read_json();

    if ($action === 'shipping-rates') {
        $postal = required_text($input, 'postal', 16);
        $quantities = cart_quantities($input['items'] ?? null);
        $items = load_shipping_items($pdo, $quantities);
        try {
            $rates = biteship_rates($items, $postal);
        } catch (DomainException $error) {
            respond(422, ['error' => $error->getMessage()]);
        } catch (RuntimeException $error) {
            respond(503, ['error' => $error->getMessage()]);
        }
        respond(200, ['data' => $rates]);
    }

    if ($action === 'newsletter') {
        $email = filter_var(trim((string)($input['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        if ($email === false || strlen($email) > 190) {
            respond(422, ['error' => 'Masukkan alamat email yang valid.']);
        }
        $statement = $pdo->prepare(
            "INSERT INTO newsletter_subscribers (email) VALUES (:email)
             ON DUPLICATE KEY UPDATE status = 'active'"
        );
        $statement->execute(['email' => $email]);
        respond(201, ['message' => 'Permintaan berlangganan tersimpan.']);
    }

    if ($action === 'orders') {
        $name = required_text($input, 'name', 160);
        $phone = required_text($input, 'phone', 32);
        $email = filter_var(trim((string)($input['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $address = required_text($input, 'address', 2000);
        $city = required_text($input, 'city', 120);
        $postal = required_text($input, 'postal', 16);
        $items = $input['items'] ?? null;

        if ($email === false) {
            respond(422, ['error' => 'Data pesanan tidak valid.']);
        }
        $quantities = cart_quantities($items);
        $preflightItems = load_shipping_items($pdo, $quantities);
        $hasDemoProduct = count(array_filter($preflightItems, static fn (array $item): bool => (bool)$item['is_demo'])) > 0;
        $shippingFee = 0;
        $courierCode = null;
        $courierService = null;
        $config = vlyhady_config();

        if (!$hasDemoProduct) {
            $courierCode = required_text($input, 'courier_code', 50);
            $courierService = required_text($input, 'courier_service', 120);
            if ($config['midtrans_server_key'] === '') {
                respond(503, ['error' => 'Kunci Midtrans belum diatur di api/config.local.php. Pesanan belum dibuat.']);
            }
            try {
                $rates = biteship_rates($preflightItems, $postal);
            } catch (DomainException $error) {
                respond(422, ['error' => $error->getMessage()]);
            } catch (RuntimeException $error) {
                respond(503, ['error' => $error->getMessage()]);
            }
            $selectedRate = null;
            foreach ($rates as $rate) {
                if ($rate['courier_code'] === $courierCode && $rate['service_code'] === $courierService) {
                    $selectedRate = $rate;
                    break;
                }
            }
            if ($selectedRate === null) {
                respond(409, ['error' => 'Tarif kurir berubah atau kedaluwarsa. Periksa ongkir kembali.']);
            }
            $shippingFee = $selectedRate['price'];
        }

        $pdo->beginTransaction();
        $findProduct = $pdo->prepare(
            'SELECT id, name, price, stock, is_active, is_demo
             FROM products WHERE id = :id FOR UPDATE'
        );
        $orderItems = [];
        $subtotal = 0;
        $hasDemoProduct = false;

        foreach ($quantities as $id => $quantity) {
            $findProduct->execute(['id' => $id]);
            $product = $findProduct->fetch();
            if (!$product || !(bool)$product['is_active']) {
                $pdo->rollBack();
                respond(409, ['error' => 'Salah satu produk sudah tidak tersedia. Muat ulang katalog.']);
            }
            if ((int)$product['stock'] < $quantity) {
                $pdo->rollBack();
                respond(409, ['error' => 'Stok ' . $product['name'] . ' tidak mencukupi.']);
            }
            $unitPrice = (int)$product['price'];
            $subtotal += $unitPrice * $quantity;
            $hasDemoProduct = $hasDemoProduct || (bool)$product['is_demo'];
            $orderItems[] = [
                'id' => (int)$product['id'],
                'name' => $product['name'],
                'quantity' => $quantity,
                'price' => $unitPrice,
                'demo' => (bool)$product['is_demo'],
            ];
        }

        $orderCode = 'VL-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $status = $hasDemoProduct ? 'demo' : 'menunggu_pembayaran';
        $paymentStatus = $hasDemoProduct ? 'manual' : 'pending';
        $paymentMethod = $hasDemoProduct ? 'Simulasi' : 'Midtrans Snap';
        $totalAmount = $subtotal + $shippingFee;
        $insertOrder = $pdo->prepare(
            'INSERT INTO orders
             (order_code, customer_name, customer_phone, customer_email, shipping_address,
              city, postal_code, payment_method, subtotal, shipping_fee, total_amount,
              courier_code, courier_service, payment_status, status)
             VALUES (:code, :name, :phone, :email, :address, :city, :postal, :payment,
                 :subtotal, :shipping_fee, :total_amount, :courier_code, :courier_service,
                 :payment_status, :status)'
        );
        $insertOrder->execute([
            'code' => $orderCode,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'postal' => $postal,
            'payment' => $paymentMethod,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total_amount' => $totalAmount,
            'courier_code' => $courierCode,
            'courier_service' => $courierService,
            'payment_status' => $paymentStatus,
            'status' => $status,
        ]);
        $orderId = (int)$pdo->lastInsertId();
        $insertItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price)
             VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price)'
        );
        $decrementStock = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id');

        foreach ($orderItems as $item) {
            $insertItem->execute([
                'order_id' => $orderId,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
            ]);
            if (!$hasDemoProduct) {
                $decrementStock->execute(['quantity' => $item['quantity'], 'id' => $item['id']]);
            }
        }

        $pdo->commit();
        $paymentUrl = null;
        if (!$hasDemoProduct) {
            try {
                require_once dirname(__DIR__) . '/vendor/autoload.php';
                \Midtrans\Config::$serverKey = $config['midtrans_server_key'];
                \Midtrans\Config::$isProduction = $config['midtrans_environment'] === 'production';
                \Midtrans\Config::$isSanitized = true;
                \Midtrans\Config::$is3ds = true;
                $itemDetails = array_map(static fn (array $item): array => [
                    'id' => (string)$item['id'],
                    'price' => (int)$item['price'],
                    'quantity' => (int)$item['quantity'],
                    'name' => mb_substr($item['name'], 0, 50),
                ], $orderItems);
                if ($shippingFee > 0) {
                    $itemDetails[] = ['id' => 'ONGKIR', 'price' => $shippingFee, 'quantity' => 1, 'name' => 'Ongkos kirim'];
                }
                $nameParts = preg_split('/\s+/', $name, 2) ?: [$name];
                $snap = \Midtrans\Snap::createTransaction([
                    'transaction_details' => ['order_id' => $orderCode, 'gross_amount' => $totalAmount],
                    'item_details' => $itemDetails,
                    'customer_details' => [
                        'first_name' => $nameParts[0],
                        'last_name' => $nameParts[1] ?? '',
                        'email' => $email,
                        'phone' => $phone,
                        'shipping_address' => [
                            'first_name' => $nameParts[0],
                            'last_name' => $nameParts[1] ?? '',
                            'address' => $address,
                            'city' => $city,
                            'postal_code' => $postal,
                            'country_code' => 'IDN',
                        ],
                    ],
                ]);
                $paymentUrl = (string)($snap->redirect_url ?? '');
                $paymentHost = (string)parse_url($paymentUrl, PHP_URL_HOST);
                $expectedHost = $config['midtrans_environment'] === 'production' ? 'app.midtrans.com' : 'app.sandbox.midtrans.com';
                if ($paymentHost !== $expectedHost || !str_starts_with($paymentUrl, 'https://')) {
                    throw new RuntimeException('Midtrans tidak mengembalikan alamat pembayaran yang valid.');
                }
                $pdo->prepare('UPDATE orders SET midtrans_redirect_url = :url WHERE id = :id')->execute(['url' => $paymentUrl, 'id' => $orderId]);
            } catch (Throwable $error) {
                error_log('Midtrans Snap create failed: ' . $error->getMessage());
                $pdo->beginTransaction();
                $releaseItems = $pdo->prepare('UPDATE products SET stock = stock + :quantity WHERE id = :id');
                foreach ($orderItems as $item) {
                    $releaseItems->execute(['quantity' => $item['quantity'], 'id' => $item['id']]);
                }
                $pdo->prepare("UPDATE orders SET status='dibatalkan', payment_status='failed', stock_released=1 WHERE id=:id")->execute(['id' => $orderId]);
                $pdo->commit();
                respond(502, ['error' => 'Midtrans belum dapat membuat pembayaran. Pesanan dibatalkan dan stok dikembalikan.']);
            }
        }

        respond(201, [
            'orderCode' => $orderCode,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'totalAmount' => $totalAmount,
            'demo' => $hasDemoProduct,
            'status' => $status,
            'paymentUrl' => $paymentUrl,
        ]);
    }

    respond(404, ['error' => 'Endpoint tidak ditemukan.']);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($error->getMessage());
    respond(500, ['error' => 'Layanan database sedang tidak tersedia.']);
}