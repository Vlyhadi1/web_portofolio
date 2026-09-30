<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

session_name('vlyhady_admin');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
    'path' => '/',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function admin_respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function admin_input(): array
{
    $decoded = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($decoded)) {
        admin_respond(400, ['error' => 'Format permintaan tidak valid.']);
    }
    return $decoded;
}

function admin_is_authenticated(PDO $pdo): bool
{
    if (empty($_SESSION['admin_id'])) {
        return false;
    }
    $statement = $pdo->prepare('SELECT username FROM admin_users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $_SESSION['admin_id']]);
    $admin = $statement->fetch();
    if (!$admin) {
        unset($_SESSION['admin_id'], $_SESSION['admin_username']);
        session_regenerate_id(true);
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return false;
    }
    $_SESSION['admin_username'] = $admin['username'];
    return true;
}

function admin_require_login(PDO $pdo): void
{
    if (!admin_is_authenticated($pdo)) {
        admin_respond(401, ['error' => 'Silakan masuk sebagai admin.']);
    }
}

function admin_require_csrf(array $input): void
{
    $token = (string)($input['csrf'] ?? '');
    if ($token === '' || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
        admin_respond(419, ['error' => 'Sesi keamanan berakhir. Muat ulang halaman.']);
    }
}

function admin_text(array $input, string $key, int $maxLength): string
{
    $value = trim((string)($input[$key] ?? ''));
    if ($value === '' || mb_strlen($value) > $maxLength) {
        admin_respond(422, ['error' => 'Periksa kembali semua isian.']);
    }
    return $value;
}

try {
    $pdo = vlyhady_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = (string)($_GET['action'] ?? 'status');
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));

    if ($method === 'GET' && $action === 'status') {
        $adminCount = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        $authenticated = admin_is_authenticated($pdo);
        admin_respond(200, [
            'setupRequired' => $adminCount === 0,
            'authenticated' => $authenticated,
            'username' => $authenticated ? $_SESSION['admin_username'] : null,
            'csrf' => $_SESSION['csrf'],
        ]);
    }

    if (!in_array($method, ['GET', 'POST'], true)) {
        admin_respond(405, ['error' => 'Metode permintaan tidak didukung.']);
    }

    $input = [];
    if ($method === 'POST') {
        $input = admin_input();
        admin_require_csrf($input);
    }

    if ($method === 'POST' && $action === 'setup') {
        $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!in_array($remoteAddress, ['127.0.0.1', '::1'], true)) {
            admin_respond(403, ['error' => 'Setup admin hanya boleh dilakukan dari komputer lokal.']);
        }
        if ((int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0) {
            admin_respond(409, ['error' => 'Admin sudah dibuat. Silakan masuk.']);
        }
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username) || strlen($password) < 12 || strlen($password) > 200) {
            admin_respond(422, ['error' => 'Nama admin 3-50 karakter; kata sandi minimal 12 karakter.']);
        }
        if (!hash_equals($password, (string)($input['passwordConfirmation'] ?? ''))) {
            admin_respond(422, ['error' => 'Konfirmasi kata sandi tidak sama.']);
        }
        $statement = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)');
        $statement->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$pdo->lastInsertId();
        $_SESSION['admin_username'] = $username;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        admin_respond(201, ['message' => 'Admin berhasil dibuat.', 'csrf' => $_SESSION['csrf']]);
    }

    if ($method === 'POST' && $action === 'login') {
        $attempts = array_values(array_filter(
            $_SESSION['login_attempts'] ?? [],
            static fn (int $timestamp): bool => $timestamp > time() - 900
        ));
        $_SESSION['login_attempts'] = $attempts;
        if (count($attempts) >= 5) {
            admin_respond(429, ['error' => 'Terlalu banyak percobaan. Tunggu 15 menit lalu coba lagi.']);
        }
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $statement = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $_SESSION['login_attempts'][] = time();
            admin_respond(401, ['error' => 'Nama admin atau kata sandi tidak cocok.']);
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['login_attempts'] = [];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $pdo->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id')->execute(['id' => $admin['id']]);
        admin_respond(200, ['message' => 'Berhasil masuk.', 'csrf' => $_SESSION['csrf']]);
    }

    admin_require_login($pdo);

    if ($method === 'POST' && $action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        }
        session_destroy();
        admin_respond(200, ['message' => 'Anda sudah keluar.']);
    }

    if ($method === 'GET' && $action === 'products') {
        $products = $pdo->query('SELECT * FROM products ORDER BY is_active DESC, id DESC')->fetchAll();
        admin_respond(200, ['data' => $products]);
    }

    if ($method === 'GET' && $action === 'orders') {
        $orders = $pdo->query(
            'SELECT id, order_code, customer_name, customer_phone, customer_email, shipping_address,
                    city, postal_code, payment_method, subtotal, shipping_fee, total_amount,
                    courier_code, courier_service, payment_status, status, sales_channel,
                    amount_received, change_amount, created_at
             FROM orders ORDER BY created_at DESC LIMIT 100'
        )->fetchAll();
        if ($orders) {
            $ids = array_map(static fn (array $order): int => (int)$order['id'], $orders);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $statement = $pdo->prepare("SELECT order_id, product_name, quantity, unit_price FROM order_items WHERE order_id IN ({$placeholders}) ORDER BY id");
            $statement->execute($ids);
            $itemsByOrder = [];
            foreach ($statement->fetchAll() as $item) {
                $itemsByOrder[(int)$item['order_id']][] = $item;
            }
            foreach ($orders as &$order) {
                $order['items'] = $itemsByOrder[(int)$order['id']] ?? [];
            }
            unset($order);
        }
        admin_respond(200, ['data' => $orders]);
    }

    if ($method === 'GET' && $action === 'newsletter') {
        $subscribers = $pdo->query('SELECT id, email, status, subscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC LIMIT 500')->fetchAll();
        admin_respond(200, ['data' => $subscribers]);
    }

    if ($method === 'POST' && $action === 'pos-sale') {
        $items = $input['items'] ?? null;
        if (!is_array($items) || count($items) === 0 || count($items) > 50) {
            admin_respond(422, ['error' => 'Tambahkan minimal satu produk ke transaksi kasir.']);
        }
        $paymentMethod = (string)($input['payment_method'] ?? '');
        if (!in_array($paymentMethod, ['cash', 'qris_manual', 'card_manual'], true)) {
            admin_respond(422, ['error' => 'Pilih metode pembayaran kasir.']);
        }
        if ($paymentMethod !== 'cash' && empty($input['payment_confirmed'])) {
            admin_respond(422, ['error' => 'Konfirmasi pembayaran non-tunai sebelum menyimpan penjualan.']);
        }
        $quantities = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                admin_respond(422, ['error' => 'Daftar produk tidak valid.']);
            }
            $productId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 999]]);
            if ($productId === false || $quantity === false) {
                admin_respond(422, ['error' => 'Jumlah produk tidak valid.']);
            }
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
            if ($quantities[$productId] > 999) {
                admin_respond(422, ['error' => 'Jumlah produk melebihi batas.']);
            }
        }

        $pdo->beginTransaction();
        $findProduct = $pdo->prepare(
            'SELECT id, name, price, stock, is_active, is_demo
             FROM products WHERE id=:id FOR UPDATE'
        );
        $saleItems = [];
        $subtotal = 0;
        foreach ($quantities as $productId => $quantity) {
            $findProduct->execute(['id' => $productId]);
            $product = $findProduct->fetch();
            if (!$product || !(bool)$product['is_active']) {
                $pdo->rollBack();
                admin_respond(409, ['error' => 'Produk tidak ditemukan atau sudah nonaktif.']);
            }
            if ((bool)$product['is_demo']) {
                $pdo->rollBack();
                admin_respond(409, ['error' => $product['name'] . ' masih produk contoh dan tidak dapat dijual melalui kasir.']);
            }
            if ((int)$product['stock'] < $quantity) {
                $pdo->rollBack();
                admin_respond(409, ['error' => 'Stok ' . $product['name'] . ' tidak mencukupi.']);
            }
            $subtotal += (int)$product['price'] * $quantity;
            $saleItems[] = [
                'id' => (int)$product['id'],
                'name' => $product['name'],
                'price' => (int)$product['price'],
                'quantity' => $quantity,
            ];
        }

        $amountReceived = $subtotal;
        if ($paymentMethod === 'cash') {
            $amountReceived = filter_var($input['amount_received'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
            if ($amountReceived === false || $amountReceived < $subtotal) {
                $pdo->rollBack();
                admin_respond(422, ['error' => 'Uang diterima kurang dari total belanja.']);
            }
        }
        $changeAmount = $amountReceived - $subtotal;
        $orderCode = 'POS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $paymentLabel = match ($paymentMethod) {
            'cash' => 'Kasir tunai',
            'qris_manual' => 'Kasir QRIS (verifikasi manual)',
            'card_manual' => 'Kasir non-tunai (verifikasi manual)',
        };

        $insertOrder = $pdo->prepare(
            "INSERT INTO orders
             (order_code,customer_name,customer_phone,customer_email,shipping_address,city,postal_code,
              payment_method,subtotal,shipping_fee,total_amount,payment_status,status,sales_channel,
              amount_received,change_amount)
             VALUES (:code,'Pelanggan Kasir','-','kasir@local.invalid','Penjualan langsung / Kasir',
              'Tasikmalaya','46196',:payment,:subtotal,0,:total,'paid','selesai','pos',:received,:change)"
        );
        $insertOrder->execute([
            'code' => $orderCode,
            'payment' => $paymentLabel,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'received' => $amountReceived,
            'change' => $changeAmount,
        ]);
        $orderId = (int)$pdo->lastInsertId();
        $insertItem = $pdo->prepare(
            'INSERT INTO order_items (order_id,product_id,product_name,quantity,unit_price)
             VALUES (:order_id,:product_id,:name,:quantity,:price)'
        );
        $decrementStock = $pdo->prepare('UPDATE products SET stock=stock-:quantity WHERE id=:id');
        foreach ($saleItems as $saleItem) {
            $insertItem->execute([
                'order_id' => $orderId,
                'product_id' => $saleItem['id'],
                'name' => $saleItem['name'],
                'quantity' => $saleItem['quantity'],
                'price' => $saleItem['price'],
            ]);
            $decrementStock->execute(['quantity' => $saleItem['quantity'], 'id' => $saleItem['id']]);
        }
        $pdo->commit();
        admin_respond(201, [
            'orderCode' => $orderCode,
            'subtotal' => $subtotal,
            'amountReceived' => $amountReceived,
            'changeAmount' => $changeAmount,
            'paymentMethod' => $paymentLabel,
        ]);
    }

    if ($method === 'POST' && $action === 'product-save') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $name = admin_text($input, 'name', 160);
        $category = admin_text($input, 'category', 60);
        $allowedCategories = ['Rumah', 'Dapur', 'Ritual', 'Aksesori'];
        if (!in_array($category, $allowedCategories, true)) {
            admin_respond(422, ['error' => 'Pilih kategori yang tersedia.']);
        }
        $price = filter_var($input['price'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
        $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
        $weight = filter_var($input['weight_grams'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
        $length = filter_var($input['length_cm'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $width = filter_var($input['width_cm'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $height = filter_var($input['height_cm'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        if ($price === false || $stock === false || $weight === false || $length === false || $width === false || $height === false) {
            admin_respond(422, ['error' => 'Harga atau stok tidak valid.']);
        }
        $image = trim((string)($input['image'] ?? ''));
        $isUnsplashId = preg_match('/^photo-[a-zA-Z0-9-]{1,120}$/', $image) === 1;
        $imageUrl = filter_var($image, FILTER_VALIDATE_URL);
        if ($image !== '' && !$isUnsplashId && ($imageUrl === false || strtolower((string)parse_url($image, PHP_URL_SCHEME)) !== 'https')) {
            admin_respond(422, ['error' => 'Gambar harus berupa ID foto Unsplash atau URL HTTPS.']);
        }
        if ($image === '') {
            $image = 'photo-1616486338812-3dadae4b4ace';
        }
        $badge = trim((string)($input['badge'] ?? ''));
        $alt = trim((string)($input['alt'] ?? $name));
        $description = admin_text($input, 'description', 4000);
        $material = trim((string)($input['material'] ?? ''));
        $dimensions = trim((string)($input['dimensions'] ?? ''));
        if (mb_strlen($badge) > 60 || mb_strlen($alt) > 240 || mb_strlen($material) > 180 || mb_strlen($dimensions) > 120) {
            admin_respond(422, ['error' => 'Salah satu keterangan terlalu panjang.']);
        }
        $isDemo = !empty($input['is_demo']) ? 1 : 0;
        $isActive = !empty($input['is_active']) ? 1 : 0;
        $productData = [
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'badge' => $badge,
            'image' => $image,
            'alt' => $alt,
            'description' => $description,
            'material' => $material,
            'dimensions' => $dimensions,
            'weight_grams' => $weight,
            'length_cm' => $length,
            'width_cm' => $width,
            'height_cm' => $height,
            'stock' => $stock,
            'is_active' => $isActive,
            'is_demo' => $isDemo,
        ];

        if ($id !== false) {
            $statement = $pdo->prepare(
                'UPDATE products SET name=:name, category=:category, price=:price, badge=:badge,
                 image=:image, alt=:alt, description=:description, material=:material,
                 dimensions=:dimensions, weight_grams=:weight_grams, length_cm=:length_cm,
                 width_cm=:width_cm, height_cm=:height_cm, stock=:stock,
                 is_active=:is_active, is_demo=:is_demo WHERE id=:id'
            );
            $productData['id'] = $id;
            $statement->execute($productData);
            if ($statement->rowCount() === 0 && !(int)$pdo->query('SELECT COUNT(*) FROM products WHERE id=' . (int)$id)->fetchColumn()) {
                admin_respond(404, ['error' => 'Produk tidak ditemukan.']);
            }
            admin_respond(200, ['message' => 'Produk diperbarui.', 'id' => (int)$id]);
        }

        $statement = $pdo->prepare(
            'INSERT INTO products (name, category, price, badge, image, alt, description, material, dimensions, weight_grams, length_cm, width_cm, height_cm, stock, is_active, is_demo)
             VALUES (:name, :category, :price, :badge, :image, :alt, :description, :material, :dimensions, :weight_grams, :length_cm, :width_cm, :height_cm, :stock, :is_active, :is_demo)'
        );
        $statement->execute($productData);
        admin_respond(201, ['message' => 'Produk ditambahkan.', 'id' => (int)$pdo->lastInsertId()]);
    }

    if ($method === 'POST' && $action === 'product-archive') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            admin_respond(422, ['error' => 'ID produk tidak valid.']);
        }
        $statement = $pdo->prepare('UPDATE products SET is_active = 0 WHERE id = :id');
        $statement->execute(['id' => $id]);
        admin_respond(200, ['message' => 'Produk dinonaktifkan.']);
    }

    if ($method === 'POST' && $action === 'order-status') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = (string)($input['status'] ?? '');
        if ($id === false || !in_array($status, ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'], true)) {
            admin_respond(422, ['error' => 'Status pesanan tidak valid.']);
        }
        if (in_array($status, ['diproses', 'dikirim', 'selesai'], true)) {
            $payment = $pdo->prepare('SELECT payment_status FROM orders WHERE id = :id');
            $payment->execute(['id' => $id]);
            if ($payment->fetchColumn() !== 'paid') {
                admin_respond(409, ['error' => 'Pesanan hanya dapat diproses setelah pembayaran terverifikasi.']);
            }
        }
        $statement = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id AND status <> \'demo\'');
        $statement->execute(['status' => $status, 'id' => $id]);
        if ($statement->rowCount() === 0) {
            admin_respond(409, ['error' => 'Pesanan demo atau pesanan tidak ditemukan.']);
        }
        admin_respond(200, ['message' => 'Status pesanan diperbarui.']);
    }

    if ($method === 'POST' && $action === 'newsletter-unsubscribe') {
        $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            admin_respond(422, ['error' => 'ID pelanggan tidak valid.']);
        }
        $statement = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE id = :id");
        $statement->execute(['id' => $id]);
        admin_respond(200, ['message' => 'Status langganan diperbarui.']);
    }

    admin_respond(404, ['error' => 'Endpoint admin tidak ditemukan.']);
} catch (Throwable $error) {
    error_log($error->getMessage());
    admin_respond(500, ['error' => 'Permintaan admin gagal diproses.']);
}