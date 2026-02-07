<?php
/**
 * MakersLab - Konfiguracja
 * Edukacja robotyczna dla dzieci
 */

// Ustawienia strony
define('SITE_NAME', 'MakersLab');
define('SITE_TAGLINE', 'Warsztaty robotyki i elektroniki dla dzieci');
define('SITE_URL', 'https://makerslab.pl');
define('ADMIN_PASSWORD', 'makerslab2024'); // Zmień na własne hasło!

// Ścieżki
define('BASE_PATH', __DIR__);
define('DATA_PATH', BASE_PATH . '/data');
define('DB_PATH', DATA_PATH . '/makerslab.db');

// Ustawienia kontaktowe
define('CONTACT_EMAIL', 'kontakt@makerslab.pl');
define('CONTACT_PHONE', '+48 123 456 789');
define('CONTACT_LOCATION', 'Trójmiasto / Online');

// Social media
define('SOCIAL_FACEBOOK', 'https://facebook.com/makerslab');
define('SOCIAL_INSTAGRAM', 'https://instagram.com/makerslab');
define('SOCIAL_YOUTUBE', 'https://youtube.com/@makerslab');

// Ustawienia sesji
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Europe/Warsaw');

// Error reporting (wyłącz na produkcji)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
