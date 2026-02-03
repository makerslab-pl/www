<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class DatabaseTest extends TestCase
{
    private $testDbPath;
    
    protected function setUp(): void
    {
        $this->testDbPath = TEST_DB_PATH;
        
        // Clean up any existing test database
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test database
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
    }

    public function testDatabaseConnection()
    {
        // Test that we can create a new PDO connection
        $pdo = new PDO('sqlite:' . $this->testDbPath);
        $this->assertInstanceOf(PDO::class, $pdo);
        
        // Test that connection is working
        $result = $pdo->query('SELECT 1')->fetch();
        $this->assertEquals(1, $result[1]);
    }

    public function testDatabaseTablesCreation()
    {
        $pdo = new PDO('sqlite:' . $this->testDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create modules table
        $pdo->exec("
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
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create contacts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_name VARCHAR(255) NOT NULL,
                child_name VARCHAR(255) NOT NULL,
                child_age INTEGER,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                preferred_form VARCHAR(50),
                message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Verify tables exist
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
        $tableNames = array_column($tables, 'name');
        
        $this->assertContains('modules', $tableNames);
        $this->assertContains('contacts', $tableNames);
    }

    public function testModuleInsertion()
    {
        $pdo = new PDO('sqlite:' . $this->testDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create modules table
        $pdo->exec("
            CREATE TABLE modules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                week_number INTEGER,
                is_active INTEGER DEFAULT 1
            )
        ");

        // Insert test module
        $stmt = $pdo->prepare("INSERT INTO modules (title, description, week_number) VALUES (?, ?, ?)");
        $stmt->execute(['Test Module', 'Test Description', 1]);

        // Verify insertion
        $result = $pdo->query("SELECT * FROM modules WHERE title = 'Test Module'")->fetch();
        
        $this->assertEquals('Test Module', $result['title']);
        $this->assertEquals('Test Description', $result['description']);
        $this->assertEquals(1, $result['week_number']);
        $this->assertEquals(1, $result['is_active']);
    }

    public function testContactInsertion()
    {
        $pdo = new PDO('sqlite:' . $this->testDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create contacts table
        $pdo->exec("
            CREATE TABLE contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_name VARCHAR(255) NOT NULL,
                child_name VARCHAR(255) NOT NULL,
                child_age INTEGER,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                preferred_form VARCHAR(50),
                message TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert test contact
        $stmt = $pdo->prepare("
            INSERT INTO contacts (parent_name, child_name, child_age, email, phone, preferred_form, message) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Jan Kowalski',
            'Anna Kowalska',
            10,
            'jan@example.com',
            '+48 123 456 789',
            'stationary',
            'Proszę o informację o zajęciach'
        ]);

        // Verify insertion
        $result = $pdo->query("SELECT * FROM contacts WHERE email = 'jan@example.com'")->fetch();
        
        $this->assertEquals('Jan Kowalski', $result['parent_name']);
        $this->assertEquals('Anna Kowalska', $result['child_name']);
        $this->assertEquals(10, $result['child_age']);
        $this->assertEquals('jan@example.com', $result['email']);
        $this->assertEquals('+48 123 456 789', $result['phone']);
        $this->assertEquals('stationary', $result['preferred_form']);
        $this->assertStringContainsString('Proszę o informację', $result['message']);
    }
}
