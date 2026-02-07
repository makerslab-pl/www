<?php
/**
 * MakersLab - PHPUnit Bootstrap
 * Inicjalizacja środowiska testowego
 */

declare(strict_types=1);

// Autoloader Composera
require_once __DIR__ . '/../vendor/autoload.php';

// Ustawienia dla testów
define('TESTING', true);
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', ':memory:'); // SQLite in-memory dla testów
define('DB_PATH', ':memory:');

// Konfiguracja testowa
define('SITE_NAME', 'MakersLab Test');
define('SITE_TAGLINE', 'Test Environment');
define('SITE_URL', 'http://localhost:8080');
define('ADMIN_PASSWORD', 'test_password');
define('CONTACT_EMAIL', 'test@makerslab.pl');
define('CONTACT_PHONE', '+48 000 000 000');
define('CONTACT_LOCATION', 'Test Location');
define('SOCIAL_FACEBOOK', 'https://facebook.com/test');
define('SOCIAL_INSTAGRAM', 'https://instagram.com/test');
define('SOCIAL_YOUTUBE', 'https://youtube.com/test');

// Funkcje pomocnicze
function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Włącz wyświetlanie błędów w testach
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Timezone
date_default_timezone_set('Europe/Warsaw');

// Wyczyść sesję przed testami
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
