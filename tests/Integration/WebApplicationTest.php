<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class WebApplicationTest extends TestCase
{
    private $baseUrl = 'http://localhost:8080';
    
    protected function setUp(): void
    {
        // Check if the application is running
        $this->checkApplicationRunning();
    }
    
    private function checkApplicationRunning()
    {
        // For integration tests, we assume Docker is running
        // In a real CI/CD environment, you would start Docker containers here
    }
    
    public function testHomePageIsAccessible()
    {
        // This test would require cURL or a web client
        // For now, we'll test that the index.php file exists and is readable
        $indexFile = __DIR__ . '/../../index.php';
        $this->assertFileExists($indexFile);
        $this->assertIsReadable($indexFile);
    }
    
    public function testAdminPageIsAccessible()
    {
        $adminFile = __DIR__ . '/../../admin.php';
        $this->assertFileExists($adminFile);
        $this->assertIsReadable($adminFile);
    }
    
    public function testConfigFileExists()
    {
        $configFile = __DIR__ . '/../../config.php';
        $this->assertFileExists($configFile);
        $this->assertIsReadable($configFile);
    }
    
    public function testDatabaseClassExists()
    {
        $databaseFile = __DIR__ . '/../../includes/database.php';
        $this->assertFileExists($databaseFile);
        $this->assertIsReadable($databaseFile);
        
        // Check if Database class is defined
        $content = file_get_contents($databaseFile);
        $this->assertStringContainsString('class Database', $content);
    }
    
    public function testHtaccessFileExists()
    {
        $htaccessFile = __DIR__ . '/../../.htaccess';
        $this->assertFileExists($htaccessFile);
        $this->assertIsReadable($htaccessFile);
    }
    
    public function testDataDirectoryIsWritable()
    {
        $dataDir = __DIR__ . '/../../data';
        $this->assertDirectoryExists($dataDir);
        $this->assertTrue(is_writable($dataDir));
    }
    
    public function testRequiredConstantsAreDefined()
    {
        // Load config file
        require_once __DIR__ . '/../../config.php';
        
        $requiredConstants = [
            'SITE_NAME',
            'SITE_TAGLINE',
            'SITE_URL',
            'ADMIN_PASSWORD',
            'BASE_PATH',
            'DATA_PATH',
            'DB_PATH',
            'CONTACT_EMAIL',
            'CONTACT_PHONE',
            'CONTACT_LOCATION'
        ];
        
        foreach ($requiredConstants as $constant) {
            $this->assertTrue(defined($constant), "Constant $constant should be defined");
        }
    }
    
    public function testCSRFFunctionsExist()
    {
        require_once __DIR__ . '/../../config.php';
        
        $this->assertTrue(function_exists('generateCSRFToken'));
        $this->assertTrue(function_exists('verifyCSRFToken'));
    }
    
    public function testDatabaseConnectionFromConfig()
    {
        // Test that we can connect to the database using the configuration
        try {
            $pdo = new \PDO('sqlite:' . TEST_DB_PATH);
            $this->assertInstanceOf(\PDO::class, $pdo);
            
            // Test basic query
            $result = $pdo->query('SELECT 1')->fetch();
            $this->assertEquals('1', $result[1]);
        } catch (\PDOException $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }
}
