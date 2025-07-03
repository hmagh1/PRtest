<?php
class ItemController {
    private $db;
    private $cache;

    public function __construct() {
        global $mysqli, $memcached;
        $this->db = $mysqli;
        $this->cache = $memcached;
    }

    public function getAll() {
        $cacheKey = 'items_all';
        $items = $this->cache->get($cacheKey);
        if (!$items) {
            $result = $this->db->query('SELECT * FROM items');
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $this->cache->set($cacheKey, $items, 60);
        }
        echo json_encode($items);
    }

    public function get($id) {
        $cacheKey = 'item_' . $id;
        $item = $this->cache->get($cacheKey);
        if (!$item) {
            $stmt = $this->db->prepare('SELECT * FROM items WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $this->cache->set($cacheKey, $item, 60);
        }
        echo json_encode($item);
    }

    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->db->prepare('INSERT INTO items (name, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $data['name'], $data['description']);
        $stmt->execute();
        $id = $stmt->insert_id;
        $this->cache->delete('items_all');
        echo json_encode(['id' => $id]);
    }

    public function update($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $this->db->prepare('UPDATE items SET name = ?, description = ? WHERE id = ?');
        $stmt->bind_param('ssi', $data['name'], $data['description'], $id);
        $stmt->execute();
        $this->cache->delete('items_all');
        $this->cache->delete('item_' . $id);
        echo json_encode(['success' => true]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM items WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $this->cache->delete('items_all');
        $this->cache->delete('item_' . $id);
        echo json_encode(['success' => true]);
    }
}
