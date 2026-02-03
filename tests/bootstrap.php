<?php

// Test bootstrap file for PHPUnit

// Define test environment constants
define('TEST_ENV', true);

// Set test database path
define('TEST_DB_PATH', __DIR__ . '/test.db');

// Load configuration if not already loaded
if (!defined('SITE_NAME')) {
    // Override DB_PATH for testing
    $_ENV['DB_PATH'] = TEST_DB_PATH;
    
    // Load the original config
    require_once __DIR__ . '/../config.php';
}

// Clean up test database before each test run
if (file_exists(TEST_DB_PATH)) {
    unlink(TEST_DB_PATH);
}

// Ensure test directory is writable
if (!is_writable(__DIR__)) {
    throw new Exception('Tests directory must be writable');
}
