<?php
require __DIR__ . '/../bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$controller = new ItemController();

if ($uri === '/items' && $method === 'GET') {
    $controller->getAll();
} elseif (preg_match('#^/items/(\d+)$#', $uri, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'GET') {
        $controller->get($id);
    } elseif ($method === 'PUT') {
        $controller->update($id);
    } elseif ($method === 'DELETE') {
        $controller->delete($id);
    }
} elseif ($uri === '/items' && $method === 'POST') {
    $controller->create();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
