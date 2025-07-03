<?php
use PHPUnit\Framework\TestCase;

require __DIR__ . '/bootstrap.php';

class ItemControllerTest extends TestCase {
    private static ItemController $controller;

    public static function setUpBeforeClass(): void {
        global $pdo, $memcached;
        // Créer la table en mémoire
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    description TEXT
)
SQL
        );
        // Vider le cache
        $memcached->flush();
        self::$controller = new ItemController();
    }

    public function testCreateGetDelete(): void {
        $data = ['name' => 'Test', 'description' => 'Desc'];
        file_put_contents('php://input', json_encode($data));

        // create
        ob_start();
        self::$controller->create();
        $out = json_decode(ob_get_clean(), true);
        $this->assertArrayHasKey('id', $out);
        $id = $out['id'];

        // get
        ob_start();
        self::$controller->get($id);
        $item = json_decode(ob_get_clean(), true);
        $this->assertEquals('Test', $item['name']);

        // delete
        ob_start();
        self::$controller->delete($id);
        $del = json_decode(ob_get_clean(), true);
        $this->assertTrue($del['success']);
    }

    public function testUpdate(): void {
        global $pdo;
        $pdo->exec("INSERT INTO items (name, description) VALUES ('Old','OldDesc')");
        $id = $pdo->lastInsertId();

        $upd = ['name'=>'New','description'=>'NewDesc'];
        file_put_contents('php://input', json_encode($upd));

        ob_start();
        self::$controller->update($id);
        ob_get_clean();

        ob_start();
        self::$controller->get($id);
        $item = json_decode(ob_get_clean(), true);
        $this->assertEquals('New', $item['name']);
    }
}
