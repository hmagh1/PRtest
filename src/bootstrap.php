<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dbHost = $_ENV['DB_HOST'] ?? '';
if ($dbHost === 'sqlite') {
    // CI mode : base SQLite en mémoire
    $pdo = new PDO('sqlite::memory:');
    // Création de la table pour les tests
    $pdo->exec('CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        description TEXT
    )');
} else {
    // Mode dev/prod : MySQL
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

// Cache Memcached (on peut ignorer en CI si besoin)
$mem = new Memcached();
$mem->addServer($_ENV['MEMCACHED_HOST'], (int)($_ENV['MEMCACHED_PORT'] ?? 11211));

require __DIR__ . '/ItemController.php';
