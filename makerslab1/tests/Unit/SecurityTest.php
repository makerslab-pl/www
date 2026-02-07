<?php
/**
 * MakersLab - Security Unit Tests
 */

declare(strict_types=1);

namespace MakersLab\Tests\Unit;

use MakersLab\Tests\TestCase;

class SecurityTest extends TestCase
{
    // ==========================================
    // CSRF TOKEN TESTS
    // ==========================================

    public function testGenerateCSRFTokenReturnsString(): void
    {
        $token = generateCSRFToken();
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testGenerateCSRFTokenHasCorrectLength(): void
    {
        $token = generateCSRFToken();
        
        // 32 bytes = 64 hex characters
        $this->assertEquals(64, strlen($token));
    }

    public function testGenerateCSRFTokenIsPersistent(): void
    {
        $token1 = generateCSRFToken();
        $token2 = generateCSRFToken();
        
        // Same token should be returned if session exists
        $this->assertEquals($token1, $token2);
    }

    public function testVerifyCSRFTokenWithValidToken(): void
    {
        $token = generateCSRFToken();
        
        $this->assertTrue(verifyCSRFToken($token));
    }

    public function testVerifyCSRFTokenWithInvalidToken(): void
    {
        generateCSRFToken();
        
        $this->assertFalse(verifyCSRFToken('invalid_token'));
    }

    public function testVerifyCSRFTokenWithEmptyToken(): void
    {
        generateCSRFToken();
        
        $this->assertFalse(verifyCSRFToken(''));
    }

    public function testVerifyCSRFTokenWithoutSession(): void
    {
        // Clear session
        $_SESSION = [];
        
        $this->assertFalse(verifyCSRFToken('any_token'));
    }

    // ==========================================
    // XSS PREVENTION TESTS
    // ==========================================

    public function testHtmlSpecialCharsEscapesScript(): void
    {
        $malicious = '<script>alert("xss")</script>';
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testHtmlSpecialCharsEscapesQuotes(): void
    {
        $malicious = '" onmouseover="alert(1)"';
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('"', $escaped);
        $this->assertStringContainsString('&quot;', $escaped);
    }

    public function testHtmlSpecialCharsEscapesSingleQuotes(): void
    {
        $malicious = "' onclick='alert(1)'";
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString("'", $escaped);
        $this->assertStringContainsString('&#039;', $escaped);
    }

    public function testHtmlSpecialCharsPreservesPolishCharacters(): void
    {
        $polish = 'Zażółć gęślą jaźń ĄĘŚĆŻŹŃÓŁ';
        $escaped = htmlspecialchars($polish, ENT_QUOTES, 'UTF-8');
        
        $this->assertEquals($polish, $escaped);
    }

    // ==========================================
    // INPUT VALIDATION TESTS
    // ==========================================

    public function testFilterEmailValidatesCorrectEmail(): void
    {
        $valid = 'test@example.com';
        $filtered = filter_var($valid, FILTER_VALIDATE_EMAIL);
        
        $this->assertEquals($valid, $filtered);
    }

    public function testFilterEmailRejectsInvalidEmail(): void
    {
        $invalid = 'not-an-email';
        $filtered = filter_var($invalid, FILTER_VALIDATE_EMAIL);
        
        $this->assertFalse($filtered);
    }

    public function testFilterEmailRejectsMaliciousEmail(): void
    {
        $malicious = 'test@example.com<script>';
        $filtered = filter_var($malicious, FILTER_VALIDATE_EMAIL);
        
        $this->assertFalse($filtered);
    }

    public function testFilterSanitizeEmailRemovesInvalidChars(): void
    {
        $dirty = 'test<>@example.com';
        $sanitized = filter_var($dirty, FILTER_SANITIZE_EMAIL);
        
        $this->assertEquals('test@example.com', $sanitized);
    }

    public function testIntvalParsesInteger(): void
    {
        $this->assertEquals(12, intval('12'));
        $this->assertEquals(12, intval('12abc'));
        $this->assertEquals(0, intval('abc'));
        $this->assertEquals(0, intval(''));
    }

    public function testIntvalRejectsNegativeAge(): void
    {
        $age = intval('-5');
        $isValid = $age >= 6 && $age <= 18;
        
        $this->assertFalse($isValid);
    }

    // ==========================================
    // URL VALIDATION TESTS
    // ==========================================

    public function testFilterUrlValidatesCorrectUrl(): void
    {
        $valid = 'https://allegro.pl/listing?string=arduino';
        $filtered = filter_var($valid, FILTER_VALIDATE_URL);
        
        $this->assertEquals($valid, $filtered);
    }

    public function testFilterUrlRejectsJavascriptUrl(): void
    {
        $malicious = 'javascript:alert(1)';
        $filtered = filter_var($malicious, FILTER_VALIDATE_URL);
        
        $this->assertFalse($filtered);
    }

    public function testFilterUrlRejectsDataUrl(): void
    {
        $malicious = 'data:text/html,<script>alert(1)</script>';
        $filtered = filter_var($malicious, FILTER_VALIDATE_URL);
        
        $this->assertFalse($filtered);
    }

    // ==========================================
    // SQL INJECTION PREVENTION TESTS
    // ==========================================

    public function testPreparedStatementPreventsInjection(): void
    {
        $db = $this->createTestDatabase();
        
        // Insert legitimate data
        $db->exec("INSERT INTO modules (title) VALUES ('Legitimate')");
        
        // Attempt SQL injection
        $malicious = "'; DROP TABLE modules; --";
        
        $stmt = $db->prepare("SELECT * FROM modules WHERE title = ?");
        $stmt->execute([$malicious]);
        
        // Table should still exist
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='modules'");
        $this->assertNotFalse($result->fetch());
        
        // Original data should still exist
        $module = $db->query("SELECT * FROM modules WHERE title = 'Legitimate'")->fetch();
        $this->assertNotFalse($module);
    }

    public function testPreparedStatementWithUnionInjection(): void
    {
        $db = $this->createTestDatabase();
        
        $db->exec("INSERT INTO modules (title) VALUES ('Module 1')");
        $db->exec("INSERT INTO settings (key, value) VALUES ('secret', 'password123')");
        
        // Attempt UNION injection
        $malicious = "' UNION SELECT key, value, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL FROM settings --";
        
        $stmt = $db->prepare("SELECT * FROM modules WHERE title = ?");
        $stmt->execute([$malicious]);
        $results = $stmt->fetchAll();
        
        // Should return empty (no match for that title)
        $this->assertEmpty($results);
    }

    // ==========================================
    // PASSWORD SECURITY TESTS
    // ==========================================

    public function testPasswordHashIsSecure(): void
    {
        $password = 'test_password';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Hash should not equal plaintext
        $this->assertNotEquals($password, $hash);
        
        // Hash should be verifiable
        $this->assertTrue(password_verify($password, $hash));
    }

    public function testPasswordHashIsDifferentEachTime(): void
    {
        $password = 'same_password';
        $hash1 = password_hash($password, PASSWORD_DEFAULT);
        $hash2 = password_hash($password, PASSWORD_DEFAULT);
        
        // Hashes should be different due to salt
        $this->assertNotEquals($hash1, $hash2);
        
        // But both should verify correctly
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }

    public function testWrongPasswordDoesNotVerify(): void
    {
        $hash = password_hash('correct_password', PASSWORD_DEFAULT);
        
        $this->assertFalse(password_verify('wrong_password', $hash));
    }

    // ==========================================
    // SESSION SECURITY TESTS
    // ==========================================

    public function testSessionRegenerateId(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $oldId = session_id();
        session_regenerate_id(true);
        $newId = session_id();
        
        $this->assertNotEquals($oldId, $newId);
    }

    // ==========================================
    // HEADER INJECTION TESTS
    // ==========================================

    public function testRemoveNewlinesFromInput(): void
    {
        $malicious = "value\r\nSet-Cookie: hacked=true";
        $sanitized = str_replace(["\r", "\n"], '', $malicious);
        
        $this->assertStringNotContainsString("\r", $sanitized);
        $this->assertStringNotContainsString("\n", $sanitized);
    }

    // ==========================================
    // FILE UPLOAD SECURITY TESTS
    // ==========================================

    public function testAllowedFileExtensions(): void
    {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        $this->assertTrue(in_array('jpg', $allowed));
        $this->assertTrue(in_array('png', $allowed));
        $this->assertFalse(in_array('php', $allowed));
        $this->assertFalse(in_array('exe', $allowed));
    }

    public function testMimeTypeValidation(): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        
        $this->assertTrue(in_array('image/jpeg', $allowedMimes));
        $this->assertFalse(in_array('application/x-php', $allowedMimes));
        $this->assertFalse(in_array('text/html', $allowedMimes));
    }
}
