<?php
use PHPUnit\Framework\TestCase;

class ItemControllerTest extends TestCase {
    private static $controller;

    public static function setUpBeforeClass(): void {
        global $mysqli, $memcached;
        $mysqli->query('CREATE TABLE IF NOT EXISTS items (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), description TEXT)');
        $memcached->flush();
        self::$controller = new ItemController();
    }

    public function testCreateGetDelete() {
        $data = ['name' => 'Test', 'description' => 'Desc'];
        file_put_contents('php://input', json_encode($data));
        ob_start();
        self::$controller->create();
        $output = json_decode(ob_get_clean(), true);
        $this->assertArrayHasKey('id', $output);
        $id = $output['id'];

        ob_start();
        self::$controller->get($id);
        $item = json_decode(ob_get_clean(), true);
        $this->assertEquals('Test', $item['name']);

        ob_start();
        self::$controller->delete($id);
        $del = json_decode(ob_get_clean(), true);
        $this->assertTrue($del['success']);
    }

    public function testUpdate() {
        global $mysqli;
        $mysqli->query("INSERT INTO items (name, description) VALUES ('Old', 'OldDesc')");
        $id = $mysqli->insert_id;
        file_put_contents('php://input', json_encode(['name' => 'New', 'description' => 'NewDesc']));
        ob_start();
        self::$controller->update($id);
        ob_clean();

        ob_start();
        self::$controller->get($id);
        $item = json_decode(ob_get_clean(), true);
        $this->assertEquals('New', $item['name']);
    }
}
