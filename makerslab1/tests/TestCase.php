<?php
/**
 * MakersLab - Base Test Case
 */

declare(strict_types=1);

namespace MakersLab\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PDO;

abstract class TestCase extends PHPUnitTestCase
{
    protected ?PDO $db = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Start session if needed
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    protected function tearDown(): void
    {
        // Close database connection
        $this->db = null;
        
        // Clear session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        parent::tearDown();
    }

    /**
     * Create in-memory SQLite database for testing
     */
    protected function createTestDatabase(): PDO
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Create tables
        $this->initTestTables($db);
        
        $this->db = $db;
        return $db;
    }

    /**
     * Initialize test database tables
     */
    protected function initTestTables(PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS modules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                subtitle VARCHAR(255),
                description TEXT,
                week_number INTEGER,
                month_number INTEGER DEFAULT 1,
                duration VARCHAR(50) DEFAULT '60 min',
                difficulty VARCHAR(20) DEFAULT 'beginner',
                icon VARCHAR(50) DEFAULT '🔧',
                allegro_url TEXT,
                allegro_price VARCHAR(50),
                allegro_title VARCHAR(255),
                skills TEXT,
                components TEXT,
                is_active INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS starter_kits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price_range VARCHAR(50),
                allegro_url TEXT,
                allegro_search VARCHAR(255),
                features TEXT,
                recommended INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key VARCHAR(100) PRIMARY KEY,
                value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_name VARCHAR(255),
                child_name VARCHAR(255),
                child_age INTEGER,
                email VARCHAR(255),
                phone VARCHAR(50),
                preferred_mode VARCHAR(50),
                message TEXT,
                status VARCHAR(20) DEFAULT 'new',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Seed test data
     */
    protected function seedTestData(PDO $db): void
    {
        // Add test module
        $db->exec("
            INSERT INTO modules (title, subtitle, description, week_number, month_number, icon, allegro_url, allegro_price, is_active, sort_order)
            VALUES ('Test Module', 'Test Project', 'Test description', 1, 1, '🔧', 'https://allegro.pl/test', '100 zł', 1, 1)
        ");

        // Add test starter kit
        $db->exec("
            INSERT INTO starter_kits (name, description, price_range, allegro_url, features, recommended, is_active, sort_order)
            VALUES ('Test Kit', 'Test kit description', '100-150 zł', 'https://allegro.pl/test-kit', 'Feature1,Feature2', 1, 1, 1)
        ");

        // Add test settings
        $db->exec("INSERT INTO settings (key, value) VALUES ('hero_title', 'Test Title')");
        $db->exec("INSERT INTO settings (key, value) VALUES ('price_individual', '150 zł')");
        $db->exec("INSERT INTO settings (key, value) VALUES ('price_group', '80 zł')");
    }

    /**
     * Assert array has keys
     */
    protected function assertArrayHasKeys(array $keys, array $array): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, "Array is missing key: {$key}");
        }
    }

    /**
     * Create mock HTTP request
     */
    protected function mockRequest(string $method = 'GET', array $data = [], array $session = []): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        
        if ($method === 'POST') {
            $_POST = $data;
        } else {
            $_GET = $data;
        }
        
        $_SESSION = array_merge($_SESSION, $session);
    }

    /**
     * Generate valid CSRF token for testing
     */
    protected function generateTestCSRFToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}
