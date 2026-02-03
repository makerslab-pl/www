<?php
/**
 * MakersLab - Konfiguracja
 * Edukacja robotyczna dla dzieci
 */

// Ustawienia strony
define('SITE_NAME', getenv('SITE_NAME') ?: 'MakersLab');
define('SITE_TAGLINE', getenv('SITE_TAGLINE') ?: 'Warsztaty robotyki i elektroniki dla dzieci');
define('SITE_DESCRIPTION', getenv('SITE_DESCRIPTION') ?: 'Warsztaty robotyki i elektroniki dla dzieci w Trójmieście i online. Arduino, programowanie, prototypy - nauka przez projekty.');
define('SITE_KEYWORDS', getenv('SITE_KEYWORDS') ?: 'robotyka, elektronika, dzieci, warsztaty, arduino, programowanie, trójmiasto');
define('SITE_URL', getenv('SITE_URL') ?: 'https://makerslab.pl');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'makerslab2024'); // Zmień na własne hasło!

// Ścieżki
define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');

// Upewnij się, że katalog data istnieje
if (!file_exists(DATA_PATH)) {
    mkdir(DATA_PATH, 0777, true);
}

define('DB_PATH', getenv('DB_PATH') ?: DATA_PATH . '/makerslab.db');

// Ustawienia kontaktowe
define('CONTACT_EMAIL', getenv('CONTACT_EMAIL') ?: 'kontakt@makerslab.pl');
define('CONTACT_PHONE', getenv('CONTACT_PHONE') ?: '+48 123 456 789');
define('CONTACT_LOCATION', getenv('CONTACT_LOCATION') ?: 'Trójmiasto / Online');
define('EMAIL_NOTIFICATIONS', getenv('EMAIL_NOTIFICATIONS') === 'true');
define('EMAIL_SUBJECT', getenv('EMAIL_SUBJECT') ?: 'Nowe zgłoszenie MakersLab');

// Social media
define('SOCIAL_FACEBOOK', getenv('SOCIAL_FACEBOOK') ?: 'https://facebook.com/makerslab');
define('SOCIAL_INSTAGRAM', getenv('SOCIAL_INSTAGRAM') ?: 'https://instagram.com/makerslab');
define('SOCIAL_YOUTUBE', getenv('SOCIAL_YOUTUBE') ?: 'https://youtube.com/@makerslab');

// Analityka
define('GA_ID', getenv('GA_ID') ?: '');

// Timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'Europe/Warsaw');

// Error reporting (wyłącz na produkcji)
$debugMode = getenv('DEBUG_MODE') ?: 'false';
if ($debugMode === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Ustawienia sesji (tylko jeśli sesja nie jest aktywna)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    $sessionLifetime = getenv('SESSION_LIFETIME') ?: 3600;
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    session_set_cookie_params($sessionLifetime);
}

// CSRF Token
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
