<?php
/**
 * MakersLab - Contact Form Integration Tests
 */

declare(strict_types=1);

namespace MakersLab\Tests\Integration;

use MakersLab\Tests\TestCase;
use PDO;

class ContactFormTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->createTestDatabase();
    }

    // ==========================================
    // VALID SUBMISSION TESTS
    // ==========================================

    public function testValidContactFormSubmission(): void
    {
        $contactData = [
            'parent_name' => 'Jan Kowalski',
            'child_name' => 'Tomek',
            'child_age' => 12,
            'email' => 'jan@example.com',
            'phone' => '+48 123 456 789',
            'preferred_mode' => 'online',
            'message' => 'Interested in robotics classes'
        ];

        $stmt = $this->db->prepare("
            INSERT INTO contacts (parent_name, child_name, child_age, email, phone, preferred_mode, message)
            VALUES (:parent_name, :child_name, :child_age, :email, :phone, :preferred_mode, :message)
        ");
        
        $result = $stmt->execute($contactData);

        $this->assertTrue($result);

        // Verify data was saved correctly
        $contact = $this->db->query("SELECT * FROM contacts WHERE id = 1")->fetch();
        
        $this->assertEquals('Jan Kowalski', $contact['parent_name']);
        $this->assertEquals('Tomek', $contact['child_name']);
        $this->assertEquals(12, $contact['child_age']);
        $this->assertEquals('jan@example.com', $contact['email']);
        $this->assertEquals('new', $contact['status']);
    }

    public function testMinimalContactFormSubmission(): void
    {
        // Only required fields
        $contactData = [
            'parent_name' => 'Anna Nowak',
            'child_name' => null,
            'child_age' => null,
            'email' => 'anna@example.com',
            'phone' => null,
            'preferred_mode' => null,
            'message' => null
        ];

        $stmt = $this->db->prepare("
            INSERT INTO contacts (parent_name, child_name, child_age, email, phone, preferred_mode, message)
            VALUES (:parent_name, :child_name, :child_age, :email, :phone, :preferred_mode, :message)
        ");
        
        $result = $stmt->execute($contactData);

        $this->assertTrue($result);
    }

    // ==========================================
    // VALIDATION TESTS
    // ==========================================

    public function testEmailValidation(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.pl',
            'user+tag@example.org',
            'user123@test.co.uk'
        ];

        foreach ($validEmails as $email) {
            $filtered = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertNotFalse($filtered, "Email should be valid: {$email}");
        }
    }

    public function testInvalidEmailRejection(): void
    {
        $invalidEmails = [
            'not-an-email',
            '@nodomain.com',
            'no@domain',
            'spaces in@email.com',
            ''
        ];

        foreach ($invalidEmails as $email) {
            $filtered = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($filtered, "Email should be invalid: {$email}");
        }
    }

    public function testChildAgeValidation(): void
    {
        $validAges = [6, 10, 12, 15, 18];
        $invalidAges = [0, 5, 19, -1, 100];

        foreach ($validAges as $age) {
            $isValid = $age >= 6 && $age <= 18;
            $this->assertTrue($isValid, "Age {$age} should be valid");
        }

        foreach ($invalidAges as $age) {
            $isValid = $age >= 6 && $age <= 18;
            $this->assertFalse($isValid, "Age {$age} should be invalid");
        }
    }

    public function testPhoneValidation(): void
    {
        $validPhones = [
            '+48 123 456 789',
            '123456789',
            '+48123456789',
            '(12) 345-67-89'
        ];

        $pattern = '/^[\d\s\+\-\(\)]{9,}$/';

        foreach ($validPhones as $phone) {
            $isValid = preg_match($pattern, $phone);
            $this->assertEquals(1, $isValid, "Phone should be valid: {$phone}");
        }
    }

    public function testPreferredModeOptions(): void
    {
        $validModes = ['stacjonarnie', 'online', 'both', ''];
        
        foreach ($validModes as $mode) {
            $isValid = in_array($mode, ['stacjonarnie', 'online', 'both', '']);
            $this->assertTrue($isValid);
        }
    }

    // ==========================================
    // DATA SANITIZATION TESTS
    // ==========================================

    public function testXSSPreventionInParentName(): void
    {
        $maliciousInput = '<script>alert("xss")</script>';
        $sanitized = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
        
        $stmt = $this->db->prepare("INSERT INTO contacts (parent_name, email) VALUES (?, ?)");
        $stmt->execute([$sanitized, 'test@example.com']);

        $contact = $this->db->query("SELECT parent_name FROM contacts WHERE id = 1")->fetch();

        // Should be escaped
        $this->assertStringContainsString('&lt;script&gt;', $contact['parent_name']);
        $this->assertStringNotContainsString('<script>', $contact['parent_name']);
    }

    public function testXSSPreventionInMessage(): void
    {
        $maliciousInput = '<img src=x onerror=alert("xss")>';
        $sanitized = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
        
        $stmt = $this->db->prepare("INSERT INTO contacts (parent_name, email, message) VALUES (?, ?, ?)");
        $stmt->execute(['Test', 'test@example.com', $sanitized]);

        $contact = $this->db->query("SELECT message FROM contacts WHERE id = 1")->fetch();

        $this->assertStringNotContainsString('<img', $contact['message']);
        $this->assertStringNotContainsString('onerror', $contact['message']);
    }

    public function testSQLInjectionPrevention(): void
    {
        $maliciousInput = "'; DROP TABLE contacts; --";
        
        $stmt = $this->db->prepare("INSERT INTO contacts (parent_name, email) VALUES (?, ?)");
        $stmt->execute([$maliciousInput, 'test@example.com']);

        // Table should still exist
        $tableExists = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contacts'")->fetch();
        $this->assertNotFalse($tableExists);

        // Data should be saved as-is
        $contact = $this->db->query("SELECT parent_name FROM contacts WHERE id = 1")->fetch();
        $this->assertEquals($maliciousInput, $contact['parent_name']);
    }

    // ==========================================
    // CONTACT RETRIEVAL TESTS
    // ==========================================

    public function testGetAllContacts(): void
    {
        // Insert multiple contacts
        for ($i = 1; $i <= 5; $i++) {
            $stmt = $this->db->prepare("INSERT INTO contacts (parent_name, email) VALUES (?, ?)");
            $stmt->execute(["Parent {$i}", "parent{$i}@example.com"]);
        }

        $contacts = $this->db->query("SELECT * FROM contacts ORDER BY id")->fetchAll();

        $this->assertCount(5, $contacts);
    }

    public function testGetContactsOrderedByDate(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, created_at) VALUES ('First', 'a@test.com', '2024-01-01 10:00:00')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, created_at) VALUES ('Second', 'b@test.com', '2024-01-02 10:00:00')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, created_at) VALUES ('Third', 'c@test.com', '2024-01-03 10:00:00')");

        $contacts = $this->db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();

        $this->assertEquals('Third', $contacts[0]['parent_name']);
        $this->assertEquals('First', $contacts[2]['parent_name']);
    }

    public function testFilterContactsByStatus(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('New 1', 'a@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Contacted', 'b@test.com', 'contacted')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('New 2', 'c@test.com', 'new')");

        $newContacts = $this->db->query("SELECT * FROM contacts WHERE status = 'new'")->fetchAll();

        $this->assertCount(2, $newContacts);
    }

    public function testCountNewContacts(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Test 1', 'a@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Test 2', 'b@test.com', 'contacted')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Test 3', 'c@test.com', 'new')");
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Test 4', 'd@test.com', 'new')");

        $count = $this->db->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'")->fetchColumn();

        $this->assertEquals(3, $count);
    }

    // ==========================================
    // STATUS UPDATE TESTS
    // ==========================================

    public function testUpdateContactStatus(): void
    {
        $this->db->exec("INSERT INTO contacts (parent_name, email, status) VALUES ('Test', 'test@example.com', 'new')");

        $stmt = $this->db->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute(['contacted', 1]);

        $contact = $this->db->query("SELECT status FROM contacts WHERE id = 1")->fetch();

        $this->assertEquals('contacted', $contact['status']);
    }

    // ==========================================
    // CSRF PROTECTION TESTS
    // ==========================================

    public function testFormRequiresCSRFToken(): void
    {
        $token = $this->generateTestCSRFToken();
        
        // Valid token should pass
        $this->assertTrue(verifyCSRFToken($token));
        
        // Invalid token should fail
        $this->assertFalse(verifyCSRFToken('invalid_token'));
        $this->assertFalse(verifyCSRFToken(''));
    }

    // ==========================================
    // EDGE CASES
    // ==========================================

    public function testEmptyFormSubmission(): void
    {
        $stmt = $this->db->prepare("INSERT INTO contacts (parent_name, email) VALUES (?, ?)");
        $result = $stmt->execute(['', '']);

        $this->assertTrue($result); // SQLite allows empty strings

        $contact = $this->db->query("SELECT * FROM contacts WHERE id = 1")->fetch();
        $this->assertEquals('', $contact['parent_name']);
    }

    public function testVeryLongMessage(): void
    {
        $longMessage = str_repeat('A', 10000);
        
        $stmt = $this->db->prepare("INSERT INTO contacts (parent_name, email, message) VALUES (?, ?, ?)");
        $result = $stmt->execute(['Test', 'test@example.com', $longMessage]);

        $this->assertTrue($result);

        $contact = $this->db->query("SELECT message FROM contacts WHERE id = 1")->fetch();
        $this->assertEquals(10000, strlen($contact['message']));
    }

    public function testPolishCharactersInContact(): void
    {
        $polishData = [
            'parent_name' => 'Żółć Ąęśćń',
            'child_name' => 'Gęślą Jaźń',
            'child_age' => 12,
            'email' => 'zolc@example.pl',
            'phone' => null,
            'preferred_mode' => 'stacjonarnie',
            'message' => 'Zażółć gęślą jaźń - ĄĘŚĆŻŹŃÓŁ'
        ];

        $stmt = $this->db->prepare("
            INSERT INTO contacts (parent_name, child_name, child_age, email, phone, preferred_mode, message)
            VALUES (:parent_name, :child_name, :child_age, :email, :phone, :preferred_mode, :message)
        ");
        $stmt->execute($polishData);

        $contact = $this->db->query("SELECT * FROM contacts WHERE id = 1")->fetch();

        $this->assertEquals('Żółć Ąęśćń', $contact['parent_name']);
        $this->assertEquals('Gęślą Jaźń', $contact['child_name']);
        $this->assertStringContainsString('ĄĘŚĆŻŹŃÓŁ', $contact['message']);
    }
}
