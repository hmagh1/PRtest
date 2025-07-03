<?php
require __DIR__ . '/../vendor/autoload.php';

// Load .env if present
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Determine DB connection mode
$dbHost = getenv('DB_HOST') ?: 'mysql';

if ($dbHost === 'sqlite') {
    // CI mode: in-memory SQLite
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec("CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        description TEXT
    )");
} else {
    // Dev/Prod mode: MySQL
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $dbHost,
        getenv('DB_NAME')
    );
    $pdo = new PDO(
        $dsn,
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

// Initialize Memcached
$memcached = new Memcached();
$memcached->addServer(
    getenv('MEMCACHED_HOST'),
    (int)getenv('MEMCACHED_PORT')
);

require __DIR__ . '/ItemController.php';
