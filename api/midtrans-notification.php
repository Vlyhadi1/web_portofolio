<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/integrations.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function notification_response(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    notification_response(405, ['error' => 'Metode permintaan tidak didukung.']);
}

$serverKey = vlyhady_config()['midtrans_server_key'];
if ($serverKey === '') {
    notification_response(503, ['error' => 'Konfigurasi Midtrans belum tersedia.']);
}

$notification = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($notification)) {
    notification_response(400, ['error' => 'Notifikasi tidak valid.']);
}

$orderCode = (string)($notification['order_id'] ?? '');
$statusCode = (string)($notification['status_code'] ?? '');
$grossAmount = (string)($notification['gross_amount'] ?? '');
$signature = (string)($notification['signature_key'] ?? '');
if ($orderCode === '' || $statusCode === '' || $grossAmount === '' || $signature === '') {
    notification_response(400, ['error' => 'Informasi signature tidak lengkap.']);
}

$expectedSignature = hash('sha512', $orderCode . $statusCode . $grossAmount . $serverKey);
if (!hash_equals($expectedSignature, $signature)) {
    notification_response(401, ['error' => 'Signature Midtrans tidak valid.']);
}

try {
    $pdo = vlyhady_db();
    $pdo->beginTransaction();
    $findOrder = $pdo->prepare(
        'SELECT id, total_amount, status, payment_status, stock_released
         FROM orders WHERE order_code = :order_code FOR UPDATE'
    );
    $findOrder->execute(['order_code' => $orderCode]);
    $order = $findOrder->fetch();
    if (!$order) {
        $pdo->rollBack();
        notification_response(404, ['error' => 'Pesanan tidak ditemukan.']);
    }

    $receivedCents = preg_match('/^\d+(?:\.\d{1,2})?$/', $grossAmount) ? (int)round((float)$grossAmount * 100) : -1;
    if ($receivedCents !== (int)$order['total_amount'] * 100) {
        $pdo->rollBack();
        notification_response(422, ['error' => 'Nominal pembayaran tidak sesuai dengan pesanan.']);
    }

    $transactionStatus = (string)($notification['transaction_status'] ?? '');
    $fraudStatus = (string)($notification['fraud_status'] ?? '');
    $transactionId = (string)($notification['transaction_id'] ?? '');
    $acceptedPayment = $statusCode === '200'
        && in_array($transactionStatus, ['settlement', 'capture'], true)
        && ($fraudStatus === '' || $fraudStatus === 'accept');
    $fraudRejected = in_array($transactionStatus, ['settlement', 'capture'], true) && $fraudStatus === 'deny';

    if ($acceptedPayment) {
        $newOrderStatus = $order['status'] === 'menunggu_pembayaran' ? 'menunggu_konfirmasi' : $order['status'];
        $update = $pdo->prepare(
            "UPDATE orders SET payment_status='paid', status=:status, midtrans_transaction_id=:transaction_id
             WHERE id=:id"
        );
        $update->execute(['status' => $newOrderStatus, 'transaction_id' => $transactionId ?: null, 'id' => $order['id']]);
    } elseif ($transactionStatus === 'pending') {
        $update = $pdo->prepare(
            "UPDATE orders SET payment_status='pending', midtrans_transaction_id=:transaction_id WHERE id=:id AND payment_status <> 'paid'"
        );
        $update->execute(['transaction_id' => $transactionId ?: null, 'id' => $order['id']]);
    } elseif ((in_array($transactionStatus, ['deny', 'cancel', 'failure', 'expire'], true) || $fraudRejected) && $order['payment_status'] !== 'paid') {
        if (!(bool)$order['stock_released']) {
            $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id=:order_id');
            $items->execute(['order_id' => $order['id']]);
            $restoreStock = $pdo->prepare('UPDATE products SET stock=stock+:quantity WHERE id=:id');
            foreach ($items->fetchAll() as $item) {
                $restoreStock->execute(['quantity' => $item['quantity'], 'id' => $item['product_id']]);
            }
        }
        $paymentStatus = $transactionStatus === 'expire' ? 'expired' : 'failed';
        $update = $pdo->prepare(
            'UPDATE orders SET payment_status=:payment_status, status=\'dibatalkan\',
             stock_released=1, midtrans_transaction_id=:transaction_id WHERE id=:id'
        );
        $update->execute(['payment_status' => $paymentStatus, 'transaction_id' => $transactionId ?: null, 'id' => $order['id']]);
    }

    $pdo->commit();
    notification_response(200, ['message' => 'Notifikasi diterima.']);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Midtrans notification failed: ' . $error->getMessage());
    notification_response(500, ['error' => 'Notifikasi belum dapat diproses.']);
}