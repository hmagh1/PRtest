<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
$memcached = new Memcached();
$memcached->addServer($_ENV['MEMCACHED_HOST'], (int)$_ENV['MEMCACHED_PORT']);

require __DIR__ . '/ItemController.php';
