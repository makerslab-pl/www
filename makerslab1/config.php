<?php
/**
 * MakersLab - Konfiguracja
 * Edukacja robotyczna dla dzieci
 */

// Ustawienia strony
define('SITE_NAME', getenv('SITE_NAME') ?: 'MakersLab');
define('SITE_TAGLINE', getenv('SITE_TAGLINE') ?: 'Warsztaty robotyki i elektroniki dla dzieci');
define('SITE_URL', getenv('SITE_URL') ?: 'https://makerslab.pl');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'makerslab2024');

// Ścieżki
define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');
define('DB_PATH', getenv('DB_PATH') ?: DATA_PATH . '/makerslab.db');

// Ustawienia kontaktowe
define('CONTACT_EMAIL', getenv('CONTACT_EMAIL') ?: 'kontakt@makerslab.pl');
define('CONTACT_PHONE', getenv('CONTACT_PHONE') ?: '+48 123 456 789');
define('CONTACT_LOCATION', getenv('CONTACT_LOCATION') ?: 'Trójmiasto / Online');

// Social media
define('SOCIAL_FACEBOOK', getenv('SOCIAL_FACEBOOK') ?: 'https://facebook.com/makerslab');
define('SOCIAL_INSTAGRAM', getenv('SOCIAL_INSTAGRAM') ?: 'https://instagram.com/makerslab');
define('SOCIAL_YOUTUBE', getenv('SOCIAL_YOUTUBE') ?: 'https://youtube.com/@makerslab');

// Ustawienia sesji (tylko definicje stałych, ustawienia ini_set muszą być przed session_start)
define('SESSION_COOKIE_HTTPONLY', getenv('SESSION_COOKIE_HTTPONLY') ?: 1);
define('SESSION_USE_STRICT_MODE', getenv('SESSION_USE_STRICT_MODE') ?: 1);
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);

// Timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'Europe/Warsaw');

// Error reporting
$debugMode = getenv('DEBUG_MODE') ?: false;
$appEnv = getenv('APP_ENV') ?: 'production';

if ($appEnv === 'development' || $debugMode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Email settings
define('EMAIL_NOTIFICATIONS', getenv('EMAIL_NOTIFICATIONS') ?: false);
define('EMAIL_SUBJECT', getenv('EMAIL_SUBJECT') ?: 'Nowe zgłoszenie MakersLab');

// Analytics
define('GA_ID', getenv('GA_ID') ?: '');

// CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
