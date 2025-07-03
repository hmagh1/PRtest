<?php
class ItemController {
    private \PDO $db;
    private \Memcached $cache;

    public function __construct() {
        global $pdo, $memcached;
        $this->db    = $pdo;
        $this->cache = $memcached;
    }

    public function getAll(): void {
        $cacheKey = 'items_all';
        $items = $this->cache->get($cacheKey);
        if ($items === false) {
            $stmt = $this->db->query('SELECT * FROM items');
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->cache->set($cacheKey, $items, 60);
        }
        echo json_encode($items);
    }

    public function get(int $id): void {
        $cacheKey = 'item_' . $id;
        $item = $this->cache->get($cacheKey);
        if ($item === false) {
            $stmt = $this->db->prepare('SELECT * FROM items WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->cache->set($cacheKey, $item, 60);
        }
        echo json_encode($item);
    }

    public function create(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->db->prepare(
            'INSERT INTO items (name, description) VALUES (:name, :description)'
        );
        $stmt->execute([
            'name'        => $data['name'],
            'description' => $data['description']
        ]);
        $id = $this->db->lastInsertId();
        $this->cache->delete('items_all');
        echo json_encode(['id' => $id]);
    }

    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->db->prepare(
            'UPDATE items SET name = :name, description = :description WHERE id = :id'
        );
        $stmt->execute([
            'name'        => $data['name'],
            'description' => $data['description'],
            'id'          => $id
        ]);
        $this->cache->delete('items_all');
        $this->cache->delete('item_' . $id);
        echo json_encode(['success' => true]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare('DELETE FROM items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $this->cache->delete('items_all');
        $this->cache->delete('item_' . $id);
        echo json_encode(['success' => true]);
    }
}
