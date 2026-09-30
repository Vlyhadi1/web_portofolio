<?php
declare(strict_types=1);

function vlyhady_db(): PDO
{
    $host = getenv('VLYHADY_DB_HOST') ?: '127.0.0.1';
    $database = getenv('VLYHADY_DB_NAME') ?: 'vlyhady_store';
    $username = getenv('VLYHADY_DB_USER') ?: 'root';
    $password = getenv('VLYHADY_DB_PASSWORD');
    $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if ($username === 'root' && !in_array($remoteAddress, ['127.0.0.1', '::1'], true)) {
        throw new RuntimeException('Akun database root hanya boleh digunakan dari server lokal.');
    }

    return new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password === false ? '' : $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}