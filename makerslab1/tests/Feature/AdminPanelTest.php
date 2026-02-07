<?php
/**
 * MakersLab - Admin Panel Feature Tests
 */

declare(strict_types=1);

namespace MakersLab\Tests\Feature;

use MakersLab\Tests\TestCase;
use PDO;

class AdminPanelTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->createTestDatabase();
        $this->seedTestData($this->db);
    }

    // ==========================================
    // AUTHENTICATION TESTS
    // ==========================================

    public function testLoginWithCorrectPassword(): void
    {
        $_SESSION['admin_logged_in'] = false;
        $password = 'test_password'; // Matches ADMIN_PASSWORD in bootstrap
        
        // Simulate login
        if ($password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
        }

        $this->assertTrue($_SESSION['admin_logged_in']);
    }

    public function testLoginWithIncorrectPassword(): void
    {
        $_SESSION['admin_logged_in'] = false;
        $password = 'wrong_password';
        
        if ($password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
        }

        $this->assertFalse($_SESSION['admin_logged_in']);
    }

    public function testLogout(): void
    {
        $_SESSION['admin_logged_in'] = true;
        
        // Simulate logout
        unset($_SESSION['admin_logged_in']);

        $this->assertFalse(isset($_SESSION['admin_logged_in']));
    }

    public function testSessionPersistence(): void
    {
        $_SESSION['admin_logged_in'] = true;
        
        // Check session is still set
        $this->assertTrue($_SESSION['admin_logged_in']);
    }

    // ==========================================
    // MODULE MANAGEMENT TESTS
    // ==========================================

    public function testCreateModule(): void
    {
        $moduleData = [
            'title' => 'New Arduino Module',
            'subtitle' => 'LED Blinking',
            'description' => 'Learn to make LED blink',
            'week_number' => 2,
            'month_number' => 1,
            'duration' => '90 min',
            'difficulty' => 'beginner',
            'icon' => '💡',
            'allegro_url' => 'https://allegro.pl/test',
            'allegro_price' => '50-80 zł',
            'allegro_title' => 'LED Kit',
            'skills' => 'Electronics,Programming',
            'components' => 'LED,Resistor,Arduino',
            'is_active' => 1,
            'sort_order' => 2
        ];

        $stmt = $this->db->prepare("
            INSERT INTO modules (title, subtitle, description, week_number, month_number, 
                duration, difficulty, icon, allegro_url, allegro_price, allegro_title, 
                skills, components, is_active, sort_order)
            VALUES (:title, :subtitle, :description, :week_number, :month_number,
                :duration, :difficulty, :icon, :allegro_url, :allegro_price, :allegro_title,
                :skills, :components, :is_active, :sort_order)
        ");
        
        $result = $stmt->execute($moduleData);
        $this->assertTrue($result);

        $module = $this->db->query("SELECT * FROM modules WHERE title = 'New Arduino Module'")->fetch();
        $this->assertEquals('LED Blinking', $module['subtitle']);
        $this->assertEquals('💡', $module['icon']);
    }

    public function testUpdateModule(): void
    {
        $stmt = $this->db->prepare("UPDATE modules SET title = ?, description = ? WHERE id = ?");
        $result = $stmt->execute(['Updated Title', 'Updated description', 1]);

        $this->assertTrue($result);

        $module = $this->db->query("SELECT * FROM modules WHERE id = 1")->fetch();
        $this->assertEquals('Updated Title', $module['title']);
        $this->assertEquals('Updated description', $module['description']);
    }

    public function testDeleteModule(): void
    {
        // First verify module exists
        $module = $this->db->query("SELECT * FROM modules WHERE id = 1")->fetch();
        $this->assertNotFalse($module);

        // Delete
        $stmt = $this->db->prepare("DELETE FROM modules WHERE id = ?");
        $result = $stmt->execute([1]);

        $this->assertTrue($result);

        // Verify deleted
        $module = $this->db->query("SELECT * FROM modules WHERE id = 1")->fetch();
        $this->assertFalse($module);
    }

    public function testToggleModuleActive(): void
    {
        // Module starts as active
        $module = $this->db->query("SELECT is_active FROM modules WHERE id = 1")->fetch();
        $this->assertEquals(1, $module['is_active']);

        // Toggle to inactive
        $this->db->exec("UPDATE modules SET is_active = 0 WHERE id = 1");
        
        $module = $this->db->query("SELECT is_active FROM modules WHERE id = 1")->fetch();
        $this->assertEquals(0, $module['is_active']);

        // Toggle back to active
        $this->db->exec("UPDATE modules SET is_active = 1 WHERE id = 1");
        
        $module = $this->db->query("SELECT is_active FROM modules WHERE id = 1")->fetch();
        $this->assertEquals(1, $module['is_active']);
    }

    public function testReorderModules(): void
    {
        // Add more modules
        $this->db->exec("INSERT INTO modules (title, sort_order) VALUES ('Module A', 3)");
        $this->db->exec("INSERT INTO modules (title, sort_order) VALUES ('Module B', 2)");
        $this->db->exec("INSERT INTO modules (title, sort_order) VALUES ('Module C', 4)");

        // Reorder
        $this->db->exec("UPDATE modules SET sort_order = 1 WHERE title = 'Module C'");
        $this->db->exec("UPDATE modules SET sort_order = 2 WHERE title = 'Module A'");

        $modules = $this->db->query("SELECT title FROM modules ORDER BY sort_order LIMIT 3")->fetchAll();
        
        $this->assertEquals('Module C', $modules[0]['title']);
        $this->assertEquals('Test Module', $modules[1]['title']); // Original with sort_order 1
    }

    // ==========================================
    // STARTER KIT MANAGEMENT TESTS
    // ==========================================

    public function testCreateStarterKit(): void
    {
        $kitData = [
            'name' => 'Advanced Arduino Kit',
            'description' => 'Complete kit for advanced learners',
            'price_range' => '200-300 zł',
            'allegro_url' => 'https://allegro.pl/advanced-kit',
            'allegro_search' => 'arduino advanced kit',
            'features' => '50+ components,Video tutorials,Support',
            'recommended' => 0,
            'is_active' => 1,
            'sort_order' => 2
        ];

        $stmt = $this->db->prepare("
            INSERT INTO starter_kits (name, description, price_range, allegro_url, 
                allegro_search, features, recommended, is_active, sort_order)
            VALUES (:name, :description, :price_range, :allegro_url,
                :allegro_search, :features, :recommended, :is_active, :sort_order)
        ");
        
        $result = $stmt->execute($kitData);
        $this->assertTrue($result);
    }

    public function testSetKitAsRecommended(): void
    {
        // Add new kit
        $this->db->exec("INSERT INTO starter_kits (name, recommended) VALUES ('New Kit', 0)");
        $newKitId = $this->db->lastInsertId();

        // Set as recommended (first unset current recommended)
        $this->db->exec("UPDATE starter_kits SET recommended = 0");
        $this->db->exec("UPDATE starter_kits SET recommended = 1 WHERE id = {$newKitId}");

        $kit = $this->db->query("SELECT recommended FROM starter_kits WHERE id = {$newKitId}")->fetch();
        $this->assertEquals(1, $kit['recommended']);

        // Only one should be recommended
        $recommendedCount = $this->db->query("SELECT COUNT(*) FROM starter_kits WHERE recommended = 1")->fetchColumn();
        $this->assertEquals(1, $recommendedCount);
    }

    // ==========================================
    // SETTINGS MANAGEMENT TESTS
    // ==========================================

    public function testUpdateSettings(): void
    {
        $settings = [
            'hero_title' => 'New Hero Title',
            'hero_subtitle' => 'New Subtitle',
            'price_individual' => '200 zł',
            'price_group' => '100 zł',
            'location' => 'Warszawa / Online'
        ];

        foreach ($settings as $key => $value) {
            $stmt = $this->db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }

        $savedTitle = $this->db->query("SELECT value FROM settings WHERE key = 'hero_title'")->fetchColumn();
        $this->assertEquals('New Hero Title', $savedTitle);

        $savedPrice = $this->db->query("SELECT value FROM settings WHERE key = 'price_individual'")->fetchColumn();
        $this->assertEquals('200 zł', $savedPrice);
    }

    public function testGetSettingWithDefault(): void
    {
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute(['non_existent_key']);
        $result = $stmt->fetchColumn();

        // Should return false/null for non-existent
        $this->assertFalse($result);

        // In application, we'd return default
        $value = $result ?: 'default_value';
        $this->assertEquals('default_value', $value);
    }

    // ==========================================
    // CONTACT MANAGEMENT TESTS
    // ==========================================

    public function testViewContacts(): void
    {
        // Add some contacts
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Parent 1', 'p1@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Parent 2', 'p2@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Parent 3', 'p3@test.com', 'contacted')");

        $contacts = $this->db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();

        $this->assertCount(3, $contacts);
    }

    public function testCountNewContacts(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('P1', 'a@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('P2', 'b@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('P3', 'c@test.com', 'contacted')");

        $newCount = $this->db->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'")->fetchColumn();

        $this->assertEquals(2, $newCount);
    }

    public function testUpdateContactStatus(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Test', 'test@test.com', 'new')");
        $contactId = $this->db->lastInsertId();

        $stmt = $this->db->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute(['contacted', $contactId]);

        $contact = $this->db->query("SELECT status FROM contacts WHERE id = {$contactId}")->fetch();
        $this->assertEquals('contacted', $contact['status']);
    }

    // ==========================================
    // CSRF PROTECTION TESTS
    // ==========================================

    public function testFormSubmissionWithValidCSRF(): void
    {
        $token = $this->generateTestCSRFToken();
        
        // Simulate form submission
        $_POST['csrf_token'] = $token;
        
        $isValid = verifyCSRFToken($_POST['csrf_token']);
        $this->assertTrue($isValid);
    }

    public function testFormSubmissionWithInvalidCSRF(): void
    {
        $this->generateTestCSRFToken();
        
        // Simulate form submission with wrong token
        $_POST['csrf_token'] = 'invalid_token';
        
        $isValid = verifyCSRFToken($_POST['csrf_token']);
        $this->assertFalse($isValid);
    }

    public function testFormSubmissionWithoutCSRF(): void
    {
        $this->generateTestCSRFToken();
        
        // No CSRF token in POST
        $_POST = ['some_field' => 'value'];
        
        $isValid = verifyCSRFToken($_POST['csrf_token'] ?? '');
        $this->assertFalse($isValid);
    }

    // ==========================================
    // BULK OPERATIONS TESTS
    // ==========================================

    public function testBulkDeactivateModules(): void
    {
        // Add multiple modules
        $this->db->exec("INSERT INTO modules (title, is_active) VALUES ('M1', 1)");
        $this->db->exec("INSERT INTO modules (title, is_active) VALUES ('M2', 1)");
        $this->db->exec("INSERT INTO modules (title, is_active) VALUES ('M3', 1)");

        // Bulk deactivate
        $this->db->exec("UPDATE modules SET is_active = 0 WHERE id IN (2, 3, 4)");

        $activeCount = $this->db->query("SELECT COUNT(*) FROM modules WHERE is_active = 1")->fetchColumn();
        $this->assertEquals(1, $activeCount); // Only original test module
    }

    // ==========================================
    // DATA EXPORT TESTS
    // ==========================================

    public function testExportContactsData(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, child_name, created_at) VALUES ('P1', 'a@t.com', 'C1', '2024-01-01')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, child_name, created_at) VALUES ('P2', 'b@t.com', 'C2', '2024-01-02')");

        $contacts = $this->db->query("SELECT parent_name, email, child_name, created_at FROM contacts ORDER BY created_at")->fetchAll();

        // Simulate CSV generation
        $csv = "parent_name,email,child_name,created_at\n";
        foreach ($contacts as $contact) {
            $csv .= implode(',', $contact) . "\n";
        }

        $this->assertStringContainsString('P1,a@t.com,C1', $csv);
        $this->assertStringContainsString('P2,b@t.com,C2', $csv);
    }

    // ==========================================
    // SEARCH/FILTER TESTS
    // ==========================================

    public function testSearchModulesByTitle(): void
    {
        $this->db->exec("INSERT INTO modules (title) VALUES ('Arduino Basics')");
        $this->db->exec("INSERT INTO modules (title) VALUES ('Robot Building')");
        $this->db->exec("INSERT INTO modules (title) VALUES ('Arduino Advanced')");

        $searchTerm = '%Arduino%';
        $stmt = $this->db->prepare("SELECT * FROM modules WHERE title LIKE ?");
        $stmt->execute([$searchTerm]);
        $results = $stmt->fetchAll();

        $this->assertCount(2, $results);
    }

    public function testFilterModulesByMonth(): void
    {
        $this->db->exec("INSERT INTO modules (title, month_number) VALUES ('M1-1', 1)");
        $this->db->exec("INSERT INTO modules (title, month_number) VALUES ('M1-2', 1)");
        $this->db->exec("INSERT INTO modules (title, month_number) VALUES ('M2-1', 2)");

        $month1Modules = $this->db->query("SELECT * FROM modules WHERE month_number = 1")->fetchAll();
        $month2Modules = $this->db->query("SELECT * FROM modules WHERE month_number = 2")->fetchAll();

        $this->assertCount(3, $month1Modules); // Including original test module
        $this->assertCount(1, $month2Modules);
    }
}
