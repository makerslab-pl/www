<?php
/**
 * MakersLab - Strona główna
 * Warsztaty robotyki i elektroniki dla dzieci
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';

$db = Database::getInstance();
$modules = $db->getModules();
$starterKits = $db->getStarterKits();

// Grupowanie modułów według miesięcy
$modulesByMonth = [];
foreach ($modules as $module) {
    $month = $module['month_number'];
    if (!isset($modulesByMonth[$month])) {
        $modulesByMonth[$month] = [];
    }
    $modulesByMonth[$month][] = $module;
}

// Obsługa formularza kontaktowego
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $formError = 'Błąd bezpieczeństwa. Odśwież stronę i spróbuj ponownie.';
    } else {
        $contactData = [
            'parent_name' => htmlspecialchars($_POST['parent_name'] ?? ''),
            'child_name' => htmlspecialchars($_POST['child_name'] ?? ''),
            'child_age' => intval($_POST['child_age'] ?? 0),
            'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
            'phone' => htmlspecialchars($_POST['phone'] ?? ''),
            'preferred_mode' => htmlspecialchars($_POST['preferred_mode'] ?? ''),
            'message' => htmlspecialchars($_POST['message'] ?? '')
        ];

        if (empty($contactData['parent_name']) || empty($contactData['email'])) {
            $formError = 'Proszę wypełnić wymagane pola.';
        } else {
            if ($db->saveContact($contactData)) {
                $formSuccess = true;
            } else {
                $formError = 'Wystąpił błąd. Spróbuj ponownie.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - <?= SITE_TAGLINE ?></title>
    <meta name="description" content="Warsztaty robotyki i elektroniki dla dzieci w Trójmieście i online. Arduino, programowanie, prototypy - nauka przez projekty.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Space+Mono:wght@400;700&family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #00ff88;
            --primary-dark: #00cc6a;
            --secondary: #ff6b35;
            --accent: #00d4ff;
            --dark: #0a0a0f;
            --dark-lighter: #12121a;
            --dark-card: #1a1a25;
            --gray: #6b7280;
            --gray-light: #9ca3af;
            --white: #ffffff;
            --gradient-primary: linear-gradient(135deg, #00ff88 0%, #00d4ff 100%);
            --gradient-secondary: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%);
            --gradient-dark: linear-gradient(180deg, #0a0a0f 0%, #12121a 100%);
            --shadow-glow: 0 0 40px rgba(0, 255, 136, 0.3);
            --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.5);
            --font-display: 'Orbitron', sans-serif;
            --font-mono: 'Space Mono', monospace;
            --font-body: 'Rubik', sans-serif;
            --radius: 12px;
            --radius-lg: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            background: var(--dark);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Grid pattern background */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(0, 255, 136, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 136, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 15px 0;
            background: rgba(10, 10, 15, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 255, 136, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 10px 0;
            background: rgba(10, 10, 15, 0.98);
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            letter-spacing: 2px;
        }

        .logo span {
            color: var(--secondary);
            -webkit-text-fill-color: var(--secondary);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: var(--gray-light);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-primary);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-cta {
            background: var(--gradient-primary);
            color: var(--dark) !important;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
        }

        .nav-cta:hover {
            box-shadow: var(--shadow-glow);
        }

        .nav-cta::after {
            display: none;
        }

        /* Mobile menu */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
        }

        .menu-toggle span {
            display: block;
            width: 25px;
            height: 2px;
            background: var(--primary);
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(0, 255, 136, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-text h1 {
            font-family: var(--font-display);
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .hero-text h1 .highlight {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--gray-light);
            margin-bottom: 30px;
            max-width: 500px;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
        }

        .hero-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--dark-card);
            padding: 10px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            border: 1px solid rgba(0, 255, 136, 0.2);
        }

        .hero-feature-icon {
            font-size: 1.1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: var(--dark);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .hero-visual {
            position: relative;
        }

        .hero-card {
            background: var(--dark-card);
            border-radius: var(--radius-lg);
            padding: 40px;
            border: 1px solid rgba(0, 255, 136, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .circuit-icon {
            font-size: 80px;
            text-align: center;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .hero-card-text {
            text-align: center;
        }

        .hero-card-text h3 {
            font-family: var(--font-display);
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .hero-card-text p {
            color: var(--gray-light);
            font-size: 0.95rem;
        }

        .floating-badge {
            position: absolute;
            background: var(--gradient-secondary);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: var(--shadow-card);
        }

        .floating-badge.top-right {
            top: -15px;
            right: -15px;
        }

        .floating-badge.bottom-left {
            bottom: -15px;
            left: -15px;
            background: var(--dark);
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        /* Section styling */
        section {
            padding: 100px 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px;
        }

        .section-badge {
            display: inline-block;
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 50px;
            font-family: var(--font-mono);
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        .section-title {
            font-family: var(--font-display);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .section-subtitle {
            color: var(--gray-light);
            font-size: 1.1rem;
        }

        /* Program section */
        .program-section {
            background: var(--dark-lighter);
        }

        .month-group {
            margin-bottom: 60px;
        }

        .month-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 255, 136, 0.2);
        }

        .month-number {
            font-family: var(--font-display);
            font-size: 3rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .month-info h3 {
            font-family: var(--font-display);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .month-info p {
            color: var(--gray-light);
            font-size: 0.95rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .module-card {
            background: var(--dark-card);
            border-radius: var(--radius-lg);
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .module-card:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 255, 136, 0.3);
            box-shadow: var(--shadow-card);
        }

        .module-card:hover::before {
            transform: scaleX(1);
        }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .module-week {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--primary);
            background: rgba(0, 255, 136, 0.1);
            padding: 5px 12px;
            border-radius: 50px;
        }

        .module-icon {
            font-size: 2.5rem;
        }

        .module-title {
            font-family: var(--font-display);
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .module-subtitle {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .module-description {
            color: var(--gray-light);
            font-size: 0.9rem;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .module-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .module-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--gray);
        }

        .module-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .skill-tag {
            background: rgba(0, 212, 255, 0.1);
            color: var(--accent);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .module-allegro {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.3);
            border-radius: var(--radius);
            padding: 15px;
        }

        .allegro-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 0.8rem;
            color: var(--secondary);
            font-weight: 600;
        }

        .allegro-title {
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .allegro-price {
            font-family: var(--font-mono);
            font-size: 1.1rem;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 12px;
        }

        .allegro-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--secondary);
            color: var(--white);
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .allegro-link:hover {
            background: #ff8c5a;
            transform: translateX(3px);
        }

        /* Starter Kits section */
        .kits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .kit-card {
            background: var(--dark-card);
            border-radius: var(--radius-lg);
            padding: 35px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            transition: all 0.3s ease;
        }

        .kit-card.recommended {
            border-color: var(--primary);
        }

        .kit-card.recommended::before {
            content: '★ POLECANY';
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--gradient-primary);
            color: var(--dark);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            font-family: var(--font-mono);
        }

        .kit-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-card);
        }

        .kit-name {
            font-family: var(--font-display);
            font-size: 1.3rem;
            margin-bottom: 10px;
            padding-right: 100px;
        }

        .kit-description {
            color: var(--gray-light);
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .kit-price {
            font-family: var(--font-mono);
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .kit-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        .kit-feature {
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
        }

        .kit-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--gradient-secondary);
            color: var(--white);
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .kit-link:hover {
            box-shadow: 0 0 30px rgba(255, 107, 53, 0.4);
            transform: translateY(-2px);
        }

        /* Pricing section */
        .pricing-section {
            background: var(--dark-lighter);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .pricing-card {
            background: var(--dark-card);
            border-radius: var(--radius-lg);
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .pricing-card.featured {
            border-color: var(--primary);
            transform: scale(1.05);
        }

        .pricing-card:hover {
            border-color: var(--primary);
        }

        .pricing-type {
            font-family: var(--font-mono);
            color: var(--primary);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .pricing-price {
            font-family: var(--font-display);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .pricing-duration {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 30px;
            text-align: left;
        }

        .pricing-features li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pricing-features li::before {
            content: '✓';
            color: var(--primary);
            font-weight: bold;
        }

        /* Contact section */
        .contact-section {
            background: var(--dark);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        .contact-info h3 {
            font-family: var(--font-display);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .contact-info p {
            color: var(--gray-light);
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .contact-method {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--dark-card);
            border-radius: var(--radius);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .contact-method-icon {
            font-size: 1.5rem;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 255, 136, 0.1);
            border-radius: 50%;
        }

        .contact-method-text span {
            display: block;
            color: var(--gray);
            font-size: 0.85rem;
        }

        .contact-method-text strong {
            color: var(--white);
            font-size: 1.1rem;
        }

        .contact-form {
            background: var(--dark-card);
            border-radius: var(--radius-lg);
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 14px 18px;
            background: var(--dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            color: var(--white);
            font-family: var(--font-body);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-submit {
            width: 100%;
            padding: 16px;
            background: var(--gradient-primary);
            border: none;
            border-radius: 50px;
            color: var(--dark);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-submit:hover {
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }

        .form-message {
            padding: 15px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            text-align: center;
        }

        .form-message.success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .form-message.error {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid var(--secondary);
            color: var(--secondary);
        }

        /* Footer */
        .footer {
            background: var(--dark-lighter);
            padding: 60px 0 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer-brand .logo {
            display: inline-block;
            margin-bottom: 15px;
        }

        .footer-brand p {
            color: var(--gray);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .footer-links h4 {
            font-family: var(--font-display);
            font-size: 1rem;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--gray-light);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-copyright {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .footer-social {
            display: flex;
            gap: 15px;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark-card);
            border-radius: 50%;
            color: var(--gray-light);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--primary);
            color: var(--dark);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                margin: 0 auto 30px;
            }

            .hero-features {
                justify-content: center;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-visual {
                max-width: 400px;
                margin: 0 auto;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--dark);
                flex-direction: column;
                padding: 20px;
                gap: 15px;
                border-bottom: 1px solid rgba(0, 255, 136, 0.1);
            }

            .nav-links.active {
                display: flex;
            }

            .menu-toggle {
                display: block;
            }

            .hero-text h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .pricing-card.featured {
                transform: none;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="#" class="logo">MAKERS<span>LAB</span></a>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#program">Program</a></li>
                    <li><a href="#zestawy">Zestawy</a></li>
                    <li><a href="#cennik">Cennik</a></li>
                    <li><a href="#kontakt" class="nav-cta">Zapisz się</a></li>
                </ul>
                <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>
                        Warsztaty <span class="highlight">robotyki</span><br>
                        dla dzieci
                    </h1>
                    <p class="hero-subtitle">
                        <?= htmlspecialchars($db->getSetting('about_text', 'Nauka przez budowanie - Arduino, elektronika, prototypy. Każde zajęcia to nowy projekt, który dziecko zabiera do domu.')) ?>
                    </p>
                    <div class="hero-features">
                        <div class="hero-feature">
                            <span class="hero-feature-icon">🎯</span>
                            <span>Od 10 lat</span>
                        </div>
                        <div class="hero-feature">
                            <span class="hero-feature-icon">🏠</span>
                            <span>Trójmiasto / Online</span>
                        </div>
                        <div class="hero-feature">
                            <span class="hero-feature-icon">🔧</span>
                            <span>Nauka przez projekty</span>
                        </div>
                    </div>
                    <div class="hero-buttons">
                        <a href="#kontakt" class="btn btn-primary">
                            Zapisz na zajęcia próbne
                            <span>→</span>
                        </a>
                        <a href="#program" class="btn btn-secondary">
                            Zobacz program
                        </a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-card">
                        <div class="floating-badge top-right">8 tygodni</div>
                        <div class="floating-badge bottom-left">Arduino</div>
                        <div class="circuit-icon">🤖</div>
                        <div class="hero-card-text">
                            <h3>Program 2-miesięczny</h3>
                            <p>Od migającej diody do robota omijającego przeszkody</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Program Section -->
    <section class="program-section" id="program">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">// PROGRAM ZAJĘĆ</span>
                <h2 class="section-title">8 tygodni praktycznej nauki</h2>
                <p class="section-subtitle">Każdy tydzień to nowy projekt - od podstaw do zaawansowanej robotyki</p>
            </div>

            <?php foreach ($modulesByMonth as $month => $monthModules): ?>
            <div class="month-group animate-on-scroll">
                <div class="month-header">
                    <div class="month-number"><?= $month ?></div>
                    <div class="month-info">
                        <h3>Miesiąc <?= $month ?>: <?= $month == 1 ? 'Fundamenty' : 'Integracja' ?></h3>
                        <p><?= $month == 1 ? 'Podstawy elektroniki i programowania Arduino' : 'Zaawansowane projekty i własne prototypy' ?></p>
                    </div>
                </div>
                <div class="modules-grid">
                    <?php foreach ($monthModules as $module): ?>
                    <div class="module-card">
                        <div class="module-header">
                            <span class="module-week">Tydzień <?= $module['week_number'] ?></span>
                            <span class="module-icon"><?= $module['icon'] ?></span>
                        </div>
                        <h3 class="module-title"><?= htmlspecialchars($module['title']) ?></h3>
                        <p class="module-subtitle"><?= htmlspecialchars($module['subtitle']) ?></p>
                        <p class="module-description"><?= htmlspecialchars($module['description']) ?></p>
                        
                        <div class="module-meta">
                            <span class="module-meta-item">
                                <span>⏱</span>
                                <?= htmlspecialchars($module['duration']) ?>
                            </span>
                            <span class="module-meta-item">
                                <span>📊</span>
                                <?= $module['difficulty'] == 'beginner' ? 'Początkujący' : 'Średnio zaawansowany' ?>
                            </span>
                        </div>

                        <?php if (!empty($module['skills'])): ?>
                        <div class="module-skills">
                            <?php foreach (explode(',', $module['skills']) as $skill): ?>
                            <span class="skill-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($module['allegro_url'])): ?>
                        <div class="module-allegro">
                            <div class="allegro-header">
                                <span>🛒</span>
                                Zestaw na Allegro
                            </div>
                            <p class="allegro-title"><?= htmlspecialchars($module['allegro_title']) ?></p>
                            <p class="allegro-price"><?= htmlspecialchars($module['allegro_price']) ?></p>
                            <a href="<?= htmlspecialchars($module['allegro_url']) ?>" target="_blank" rel="noopener" class="allegro-link">
                                Zobacz na Allegro
                                <span>→</span>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Starter Kits Section -->
    <section class="kits-section" id="zestawy">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">// ZESTAWY STARTOWE</span>
                <h2 class="section-title">Wszystko czego potrzebujesz na start</h2>
                <p class="section-subtitle">Polecane zestawy Arduino dostępne na Allegro - sprawdzone przez nas</p>
            </div>

            <div class="kits-grid">
                <?php foreach ($starterKits as $kit): ?>
                <div class="kit-card <?= $kit['recommended'] ? 'recommended' : '' ?> animate-on-scroll">
                    <h3 class="kit-name"><?= htmlspecialchars($kit['name']) ?></h3>
                    <p class="kit-description"><?= htmlspecialchars($kit['description']) ?></p>
                    <p class="kit-price"><?= htmlspecialchars($kit['price_range']) ?></p>
                    
                    <?php if (!empty($kit['features'])): ?>
                    <div class="kit-features">
                        <?php foreach (explode(',', $kit['features']) as $feature): ?>
                        <span class="kit-feature"><?= htmlspecialchars(trim($feature)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <a href="<?= htmlspecialchars($kit['allegro_url']) ?>" target="_blank" rel="noopener" class="kit-link">
                        🛒 Kup na Allegro
                        <span>→</span>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section" id="cennik">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">// CENNIK</span>
                <h2 class="section-title">Przejrzyste ceny</h2>
                <p class="section-subtitle">Wybierz formę zajęć dopasowaną do potrzeb</p>
            </div>

            <div class="pricing-grid">
                <div class="pricing-card animate-on-scroll">
                    <p class="pricing-type">ZAJĘCIA GRUPOWE</p>
                    <p class="pricing-price"><?= htmlspecialchars($db->getSetting('price_group', '80 zł')) ?></p>
                    <p class="pricing-duration">za 90 minut</p>
                    <ul class="pricing-features">
                        <li>Grupy 4-6 osób</li>
                        <li>Wspólne projekty</li>
                        <li>Nauka współpracy</li>
                        <li>Wymiana doświadczeń</li>
                        <li>Materiały w cenie</li>
                    </ul>
                    <a href="#kontakt" class="btn btn-primary">Zapisz się</a>
                </div>

                <div class="pricing-card featured animate-on-scroll">
                    <p class="pricing-type">ZAJĘCIA INDYWIDUALNE</p>
                    <p class="pricing-price"><?= htmlspecialchars($db->getSetting('price_individual', '150 zł')) ?></p>
                    <p class="pricing-duration">za 90 minut</p>
                    <ul class="pricing-features">
                        <li>Tempo dopasowane do dziecka</li>
                        <li>Elastyczne terminy</li>
                        <li>100% uwagi mentora</li>
                        <li>Własne projekty</li>
                        <li>Materiały w cenie</li>
                        <li>Online lub stacjonarnie</li>
                    </ul>
                    <a href="#kontakt" class="btn btn-primary">Zapisz się</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="kontakt">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">// KONTAKT</span>
                <h2 class="section-title">Zapisz dziecko na zajęcia</h2>
                <p class="section-subtitle">Pierwsze zajęcia próbne gratis!</p>
            </div>

            <div class="contact-grid">
                <div class="contact-info animate-on-scroll">
                    <h3>Masz pytania?</h3>
                    <p>
                        Chętnie odpowiem na wszystkie pytania dotyczące programu, 
                        materiałów i możliwości dostosowania zajęć do zainteresowań Twojego dziecka.
                    </p>
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-method-icon">📧</div>
                            <div class="contact-method-text">
                                <span>Email</span>
                                <strong><?= CONTACT_EMAIL ?></strong>
                            </div>
                        </div>
                        <div class="contact-method">
                            <div class="contact-method-icon">📱</div>
                            <div class="contact-method-text">
                                <span>Telefon</span>
                                <strong><?= CONTACT_PHONE ?></strong>
                            </div>
                        </div>
                        <div class="contact-method">
                            <div class="contact-method-icon">📍</div>
                            <div class="contact-method-text">
                                <span>Lokalizacja</span>
                                <strong><?= CONTACT_LOCATION ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form animate-on-scroll">
                    <?php if ($formSuccess): ?>
                    <div class="form-message success">
                        ✓ Dziękujemy za zgłoszenie! Skontaktujemy się wkrótce.
                    </div>
                    <?php elseif ($formError): ?>
                    <div class="form-message error">
                        <?= htmlspecialchars($formError) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="#kontakt">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="contact_form" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Imię rodzica *</label>
                                <input type="text" name="parent_name" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Imię dziecka</label>
                                <input type="text" name="child_name" class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Wiek dziecka</label>
                                <input type="number" name="child_age" class="form-input" min="6" max="18">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Preferowana forma</label>
                                <select name="preferred_mode" class="form-select">
                                    <option value="">Wybierz...</option>
                                    <option value="stacjonarnie">Stacjonarnie (Trójmiasto)</option>
                                    <option value="online">Online (Zoom)</option>
                                    <option value="both">Bez preferencji</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="phone" class="form-input">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Wiadomość</label>
                            <textarea name="message" class="form-textarea" placeholder="Napisz o zainteresowaniach dziecka, doświadczeniu z elektroniką, preferowanych terminach..."></textarea>
                        </div>

                        <button type="submit" class="form-submit">
                            Wyślij zgłoszenie →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <a href="#" class="logo">MAKERS<span>LAB</span></a>
                    <p>
                        Warsztaty robotyki i elektroniki dla dzieci w Trójmieście i online. 
                        Uczymy przez projekty - Arduino, programowanie, prototypowanie.
                    </p>
                </div>
                <div class="footer-links">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="#program">Program zajęć</a></li>
                        <li><a href="#zestawy">Zestawy startowe</a></li>
                        <li><a href="#cennik">Cennik</a></li>
                        <li><a href="#kontakt">Kontakt</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Informacje</h4>
                    <ul>
                        <li><a href="#">Polityka prywatności</a></li>
                        <li><a href="#">Regulamin</a></li>
                        <li><a href="admin.php">Panel CMS</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copyright">
                    © <?= date('Y') ?> <?= SITE_NAME ?>. Wszystkie prawa zastrzeżone.
                </p>
                <div class="footer-social">
                    <a href="<?= SOCIAL_FACEBOOK ?>" target="_blank" rel="noopener" aria-label="Facebook">f</a>
                    <a href="<?= SOCIAL_INSTAGRAM ?>" target="_blank" rel="noopener" aria-label="Instagram">ig</a>
                    <a href="<?= SOCIAL_YOUTUBE ?>" target="_blank" rel="noopener" aria-label="YouTube">yt</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');

        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Animate on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu
                    navLinks.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
