<?php
/**
 * MakersLab - Database Unit Tests
 */

declare(strict_types=1);

namespace MakersLab\Tests\Unit;

use MakersLab\Tests\TestCase;
use PDO;

class DatabaseTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->createTestDatabase();
    }

    // ==========================================
    // TABLE CREATION TESTS
    // ==========================================

    public function testModulesTableExists(): void
    {
        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='modules'");
        $this->assertNotFalse($result->fetch());
    }

    public function testStarterKitsTableExists(): void
    {
        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='starter_kits'");
        $this->assertNotFalse($result->fetch());
    }

    public function testSettingsTableExists(): void
    {
        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'");
        $this->assertNotFalse($result->fetch());
    }

    public function testContactsTableExists(): void
    {
        $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contacts'");
        $this->assertNotFalse($result->fetch());
    }

    // ==========================================
    // MODULES CRUD TESTS
    // ==========================================

    public function testCanInsertModule(): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO modules (title, subtitle, description, week_number, month_number, icon)
            VALUES (:title, :subtitle, :description, :week_number, :month_number, :icon)
        ");
        
        $result = $stmt->execute([
            'title' => 'Test Module',
            'subtitle' => 'Test Subtitle',
            'description' => 'Test Description',
            'week_number' => 1,
            'month_number' => 1,
            'icon' => '🔧'
        ]);

        $this->assertTrue($result);
        $this->assertEquals(1, $this->db->lastInsertId());
    }

    public function testCanReadModule(): void
    {
        // Insert test data
        $this->seedTestData($this->db);

        $stmt = $this->db->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([1]);
        $module = $stmt->fetch();

        $this->assertNotFalse($module);
        $this->assertEquals('Test Module', $module['title']);
        $this->assertEquals('Test Project', $module['subtitle']);
        $this->assertEquals(1, $module['week_number']);
    }

    public function testCanUpdateModule(): void
    {
        $this->seedTestData($this->db);

        $stmt = $this->db->prepare("UPDATE modules SET title = ? WHERE id = ?");
        $result = $stmt->execute(['Updated Title', 1]);

        $this->assertTrue($result);

        $stmt = $this->db->prepare("SELECT title FROM modules WHERE id = ?");
        $stmt->execute([1]);
        $module = $stmt->fetch();

        $this->assertEquals('Updated Title', $module['title']);
    }

    public function testCanDeleteModule(): void
    {
        $this->seedTestData($this->db);

        $stmt = $this->db->prepare("DELETE FROM modules WHERE id = ?");
        $result = $stmt->execute([1]);

        $this->assertTrue($result);

        $stmt = $this->db->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([1]);
        
        $this->assertFalse($stmt->fetch());
    }

    public function testCanGetActiveModulesOnly(): void
    {
        // Insert active and inactive modules
        $this->db->exec("INSERT INTO modules (title, is_active, sort_order) VALUES ('Active 1', 1, 1)");
        $this->db->exec("INSERT INTO modules (title, is_active, sort_order) VALUES ('Inactive', 0, 2)");
        $this->db->exec("INSERT INTO modules (title, is_active, sort_order) VALUES ('Active 2', 1, 3)");

        $stmt = $this->db->query("SELECT * FROM modules WHERE is_active = 1 ORDER BY sort_order");
        $modules = $stmt->fetchAll();

        $this->assertCount(2, $modules);
        $this->assertEquals('Active 1', $modules[0]['title']);
        $this->assertEquals('Active 2', $modules[1]['title']);
    }

    public function testModulesAreSortedByOrder(): void
    {
        $this->db->exec("INSERT INTO modules (title, sort_order) VALUES ('Third', 3)");
        $this->db->exec("INSERT INTO modules (title, sort_order) VALUES ('First', 1)");
        $this->db->exec("INSERT INTO modules (title, sort_order) VALUES ('Second', 2)");

        $modules = $this->db->query("SELECT title FROM modules ORDER BY sort_order ASC")->fetchAll();

        $this->assertEquals('First', $modules[0]['title']);
        $this->assertEquals('Second', $modules[1]['title']);
        $this->assertEquals('Third', $modules[2]['title']);
    }

    // ==========================================
    // STARTER KITS TESTS
    // ==========================================

    public function testCanInsertStarterKit(): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO starter_kits (name, description, price_range, recommended)
            VALUES (:name, :description, :price_range, :recommended)
        ");
        
        $result = $stmt->execute([
            'name' => 'Arduino Starter Kit',
            'description' => 'Complete kit for beginners',
            'price_range' => '100-150 zł',
            'recommended' => 1
        ]);

        $this->assertTrue($result);
    }

    public function testCanGetRecommendedKits(): void
    {
        $this->db->exec("INSERT INTO starter_kits (name, recommended) VALUES ('Kit 1', 1)");
        $this->db->exec("INSERT INTO starter_kits (name, recommended) VALUES ('Kit 2', 0)");
        $this->db->exec("INSERT INTO starter_kits (name, recommended) VALUES ('Kit 3', 1)");

        $stmt = $this->db->query("SELECT * FROM starter_kits WHERE recommended = 1");
        $kits = $stmt->fetchAll();

        $this->assertCount(2, $kits);
    }

    // ==========================================
    // SETTINGS TESTS
    // ==========================================

    public function testCanSaveSetting(): void
    {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
        $result = $stmt->execute(['test_key', 'test_value']);

        $this->assertTrue($result);
    }

    public function testCanReadSetting(): void
    {
        $this->db->exec("INSERT INTO settings (key, value) VALUES ('hero_title', 'Welcome')");

        $stmt = $this->db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute(['hero_title']);
        $result = $stmt->fetch();

        $this->assertEquals('Welcome', $result['value']);
    }

    public function testSettingReturnsNullForNonExistent(): void
    {
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute(['non_existent_key']);
        
        $this->assertFalse($stmt->fetch());
    }

    public function testCanUpdateSetting(): void
    {
        $this->db->exec("INSERT INTO settings (key, value) VALUES ('price', '100 zł')");
        
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
        $stmt->execute(['price', '150 zł']);

        $stmt = $this->db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute(['price']);
        $result = $stmt->fetch();

        $this->assertEquals('150 zł', $result['value']);
    }

    // ==========================================
    // CONTACTS TESTS
    // ==========================================

    public function testCanSaveContact(): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO contacts (parent_name, child_name, child_age, email, phone, preferred_mode, message)
            VALUES (:parent_name, :child_name, :child_age, :email, :phone, :preferred_mode, :message)
        ");
        
        $result = $stmt->execute([
            'parent_name' => 'Jan Kowalski',
            'child_name' => 'Tomek',
            'child_age' => 12,
            'email' => 'jan@example.com',
            'phone' => '+48 123 456 789',
            'preferred_mode' => 'online',
            'message' => 'Test message'
        ]);

        $this->assertTrue($result);
    }

    public function testContactHasDefaultNewStatus(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email) VALUES ('Test', 'test@example.com')");

        $contact = $this->db->query("SELECT status FROM contacts WHERE id = 1")->fetch();

        $this->assertEquals('new', $contact['status']);
    }

    public function testCanGetContactsOrderedByDate(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, created_at) VALUES ('First', 'a@example.com', '2024-01-01')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, created_at) VALUES ('Second', 'b@example.com', '2024-01-02')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, created_at) VALUES ('Third', 'c@example.com', '2024-01-03')");

        $contacts = $this->db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();

        $this->assertEquals('Third', $contacts[0]['parent_name']);
        $this->assertEquals('First', $contacts[2]['parent_name']);
    }

    // ==========================================
    // DATA VALIDATION TESTS
    // ==========================================

    public function testModuleRequiresTitle(): void
    {
        $this->expectException(\PDOException::class);

        $this->db->exec("INSERT INTO modules (subtitle) VALUES ('No title')");
    }

    public function testStarterKitRequiresName(): void
    {
        $this->expectException(\PDOException::class);

        $this->db->exec("INSERT INTO starter_kits (description) VALUES ('No name')");
    }

    public function testSettingsKeyIsPrimaryKey(): void
    {
        $this->db->exec("INSERT INTO settings (key, value) VALUES ('unique_key', 'value1')");
        
        // Second insert with same key should replace
        $this->db->exec("INSERT OR REPLACE INTO settings (key, value) VALUES ('unique_key', 'value2')");

        $count = $this->db->query("SELECT COUNT(*) FROM settings WHERE key = 'unique_key'")->fetchColumn();
        $this->assertEquals(1, $count);
    }

    // ==========================================
    // EDGE CASES
    // ==========================================

    public function testHandlesEmptyDatabase(): void
    {
        $modules = $this->db->query("SELECT * FROM modules")->fetchAll();
        $this->assertEmpty($modules);
    }

    public function testHandlesSpecialCharactersInData(): void
    {
        $stmt = $this->db->prepare("INSERT INTO modules (title, description) VALUES (?, ?)");
        $stmt->execute(['Test <script>alert("xss")</script>', "Description with 'quotes' and \"double quotes\""]);

        $module = $this->db->query("SELECT * FROM modules WHERE id = 1")->fetch();

        $this->assertStringContainsString('<script>', $module['title']);
        $this->assertStringContainsString("'quotes'", $module['description']);
    }

    public function testHandlesPolishCharacters(): void
    {
        $stmt = $this->db->prepare("INSERT INTO modules (title, description) VALUES (?, ?)");
        $stmt->execute(['Żółć ąęśćń', 'Zażółć gęślą jaźń']);

        $module = $this->db->query("SELECT * FROM modules WHERE id = 1")->fetch();

        $this->assertEquals('Żółć ąęśćń', $module['title']);
        $this->assertEquals('Zażółć gęślą jaźń', $module['description']);
    }

    public function testHandlesLongText(): void
    {
        $longText = str_repeat('Lorem ipsum ', 1000);
        
        $stmt = $this->db->prepare("INSERT INTO modules (title, description) VALUES (?, ?)");
        $stmt->execute(['Long Text Module', $longText]);

        $module = $this->db->query("SELECT description FROM modules WHERE id = 1")->fetch();

        $this->assertEquals($longText, $module['description']);
    }
}
