<?php
/**
 * MakersLab - Panel Administracyjny CMS
 * Zarządzanie modułami, zestawami i ustawieniami
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

// Obsługa logowania
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $loginError = 'Nieprawidłowe hasło';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Aktywna zakładka
$tab = $_GET['tab'] ?? 'modules';

// Obsługa akcji CRUD
$message = '';
$messageType = '';

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = 'Błąd bezpieczeństwa. Odśwież stronę.';
        $messageType = 'error';
    } else {
        // Dodawanie/edycja modułu
        if (isset($_POST['save_module'])) {
            $moduleData = [
                'title' => $_POST['title'] ?? '',
                'subtitle' => $_POST['subtitle'] ?? '',
                'description' => $_POST['description'] ?? '',
                'week_number' => intval($_POST['week_number'] ?? 1),
                'month_number' => intval($_POST['month_number'] ?? 1),
                'duration' => $_POST['duration'] ?? '90 min',
                'difficulty' => $_POST['difficulty'] ?? 'beginner',
                'icon' => $_POST['icon'] ?? '🔧',
                'allegro_url' => $_POST['allegro_url'] ?? '',
                'allegro_price' => $_POST['allegro_price'] ?? '',
                'allegro_title' => $_POST['allegro_title'] ?? '',
                'skills' => $_POST['skills'] ?? '',
                'components' => $_POST['components'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => intval($_POST['sort_order'] ?? 0)
            ];

            if (isset($_POST['module_id']) && $_POST['module_id'] > 0) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE modules SET 
                        title = :title, subtitle = :subtitle, description = :description,
                        week_number = :week_number, month_number = :month_number,
                        duration = :duration, difficulty = :difficulty, icon = :icon,
                        allegro_url = :allegro_url, allegro_price = :allegro_price,
                        allegro_title = :allegro_title, skills = :skills, components = :components,
                        is_active = :is_active, sort_order = :sort_order,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                $moduleData['id'] = intval($_POST['module_id']);
                $stmt->execute($moduleData);
                $message = 'Moduł został zaktualizowany';
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO modules (title, subtitle, description, week_number, month_number,
                        duration, difficulty, icon, allegro_url, allegro_price, allegro_title,
                        skills, components, is_active, sort_order)
                    VALUES (:title, :subtitle, :description, :week_number, :month_number,
                        :duration, :difficulty, :icon, :allegro_url, :allegro_price, :allegro_title,
                        :skills, :components, :is_active, :sort_order)
                ");
                $stmt->execute($moduleData);
                $message = 'Moduł został dodany';
            }
            $messageType = 'success';
        }

        // Usuwanie modułu
        if (isset($_POST['delete_module'])) {
            $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
            $stmt->execute([intval($_POST['module_id'])]);
            $message = 'Moduł został usunięty';
            $messageType = 'success';
        }

        // Dodawanie/edycja zestawu
        if (isset($_POST['save_kit'])) {
            $kitData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price_range' => $_POST['price_range'] ?? '',
                'allegro_url' => $_POST['allegro_url'] ?? '',
                'allegro_search' => $_POST['allegro_search'] ?? '',
                'features' => $_POST['features'] ?? '',
                'recommended' => isset($_POST['recommended']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => intval($_POST['sort_order'] ?? 0)
            ];

            if (isset($_POST['kit_id']) && $_POST['kit_id'] > 0) {
                $stmt = $pdo->prepare("
                    UPDATE starter_kits SET 
                        name = :name, description = :description, price_range = :price_range,
                        allegro_url = :allegro_url, allegro_search = :allegro_search,
                        features = :features, recommended = :recommended,
                        is_active = :is_active, sort_order = :sort_order
                    WHERE id = :id
                ");
                $kitData['id'] = intval($_POST['kit_id']);
                $stmt->execute($kitData);
                $message = 'Zestaw został zaktualizowany';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO starter_kits (name, description, price_range, allegro_url,
                        allegro_search, features, recommended, is_active, sort_order)
                    VALUES (:name, :description, :price_range, :allegro_url,
                        :allegro_search, :features, :recommended, :is_active, :sort_order)
                ");
                $stmt->execute($kitData);
                $message = 'Zestaw został dodany';
            }
            $messageType = 'success';
        }

        // Usuwanie zestawu
        if (isset($_POST['delete_kit'])) {
            $stmt = $pdo->prepare("DELETE FROM starter_kits WHERE id = ?");
            $stmt->execute([intval($_POST['kit_id'])]);
            $message = 'Zestaw został usunięty';
            $messageType = 'success';
        }

        // Zapisywanie ustawień
        if (isset($_POST['save_settings'])) {
            $settings = [
                'hero_title' => $_POST['hero_title'] ?? '',
                'hero_subtitle' => $_POST['hero_subtitle'] ?? '',
                'about_text' => $_POST['about_text'] ?? '',
                'cta_text' => $_POST['cta_text'] ?? '',
                'price_individual' => $_POST['price_individual'] ?? '',
                'price_group' => $_POST['price_group'] ?? '',
                'location' => $_POST['location'] ?? ''
            ];

            foreach ($settings as $key => $value) {
                $db->updateSetting($key, $value);
            }
            $message = 'Ustawienia zostały zapisane';
            $messageType = 'success';
        }
    }
}

// Pobieranie danych
$modules = $db->getModules(false);
$starterKits = $pdo->query("SELECT * FROM starter_kits ORDER BY sort_order ASC")->fetchAll();
$contacts = $db->getContacts();

// Edytowany moduł
$editModule = null;
if (isset($_GET['edit_module'])) {
    $editModule = $db->getModule(intval($_GET['edit_module']));
}

// Edytowany zestaw
$editKit = null;
if (isset($_GET['edit_kit'])) {
    $stmt = $pdo->prepare("SELECT * FROM starter_kits WHERE id = ?");
    $stmt->execute([intval($_GET['edit_kit'])]);
    $editKit = $stmt->fetch();
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel CMS - <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Space+Mono:wght@400;700&family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rubik', sans-serif;
            background: var(--dark);
            color: var(--white);
            min-height: 100vh;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background: var(--dark-card);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(0, 255, 136, 0.2);
            max-width: 400px;
            width: 100%;
        }

        .login-box h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(135deg, #00ff88, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--dark-lighter);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
        }

        .sidebar-logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #00ff88, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .sidebar-subtitle {
            font-size: 0.8rem;
            color: var(--gray);
            margin-bottom: 30px;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--gray-light);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary);
        }

        .sidebar-nav .icon {
            font-size: 1.2rem;
        }

        .sidebar-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-footer a {
            display: block;
            padding: 10px 16px;
            color: var(--gray);
            text-decoration: none;
            font-size: 0.9rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-footer a:hover {
            color: var(--secondary);
            background: rgba(255, 107, 53, 0.1);
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00ff88, #00d4ff);
            color: var(--dark);
        }

        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.4);
        }

        .btn-secondary {
            background: var(--dark-card);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .btn-danger:hover {
            background: var(--error);
            color: var(--white);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
        }

        .card {
            background: var(--dark-card);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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
            color: var(--gray-light);
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--white);
            font-family: 'Rubik', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-checkbox input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 5px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        th {
            font-weight: 500;
            color: var(--gray-light);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .status-inactive {
            background: rgba(107, 114, 128, 0.2);
            color: var(--gray);
        }

        .status-new {
            background: rgba(0, 212, 255, 0.2);
            color: var(--accent);
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: var(--gray-light);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .icon-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .icon-btn.danger:hover {
            border-color: var(--error);
            color: var(--error);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #00ff88, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .emoji-picker {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .emoji-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .emoji-btn:hover,
        .emoji-btn.selected {
            border-color: var(--primary);
            background: rgba(0, 255, 136, 0.1);
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }
            .sidebar {
                position: relative;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isLoggedIn): ?>
    <!-- Login Page -->
    <div class="login-page">
        <div class="login-box">
            <h1>🔐 Panel CMS</h1>
            
            <?php if ($loginError): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($loginError) ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Hasło administratora</label>
                    <input type="password" name="password" class="form-input" required autofocus>
                </div>
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">
                    Zaloguj się
                </button>
            </form>
            <p style="text-align: center; margin-top: 20px; color: var(--gray); font-size: 0.85rem;">
                Domyślne hasło: makerslab2024
            </p>
        </div>
    </div>

    <?php else: ?>
    <!-- Admin Layout -->
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-logo">MAKERSLAB</div>
            <div class="sidebar-subtitle">Panel administracyjny</div>
            
            <ul class="sidebar-nav">
                <li>
                    <a href="?tab=modules" class="<?= $tab === 'modules' ? 'active' : '' ?>">
                        <span class="icon">📚</span>
                        Moduły zajęć
                    </a>
                </li>
                <li>
                    <a href="?tab=kits" class="<?= $tab === 'kits' ? 'active' : '' ?>">
                        <span class="icon">🛒</span>
                        Zestawy startowe
                    </a>
                </li>
                <li>
                    <a href="?tab=contacts" class="<?= $tab === 'contacts' ? 'active' : '' ?>">
                        <span class="icon">📬</span>
                        Zgłoszenia
                        <?php 
                        $newContacts = count(array_filter($contacts, fn($c) => $c['status'] === 'new'));
                        if ($newContacts > 0): 
                        ?>
                        <span class="status-badge status-new"><?= $newContacts ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="?tab=settings" class="<?= $tab === 'settings' ? 'active' : '' ?>">
                        <span class="icon">⚙️</span>
                        Ustawienia
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="index.php" target="_blank">🌐 Zobacz stronę</a>
                <a href="?logout=1">🚪 Wyloguj się</a>
            </div>
        </aside>

        <main class="main-content">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $messageType === 'success' ? '✓' : '✗' ?>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if ($tab === 'modules'): ?>
            <!-- Moduły zajęć -->
            <div class="page-header">
                <h1 class="page-title">Moduły zajęć</h1>
                <a href="?tab=modules&new=1" class="btn btn-primary">+ Dodaj moduł</a>
            </div>

            <?php if (isset($_GET['new']) || $editModule): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= $editModule ? 'Edytuj moduł' : 'Nowy moduł' ?></h2>
                    <a href="?tab=modules" class="btn btn-secondary btn-sm">Anuluj</a>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="module_id" value="<?= $editModule['id'] ?? '' ?>">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Tytuł *</label>
                                <input type="text" name="title" class="form-input" required 
                                       value="<?= htmlspecialchars($editModule['title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Podtytuł (projekt)</label>
                                <input type="text" name="subtitle" class="form-input"
                                       value="<?= htmlspecialchars($editModule['subtitle'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Opis</label>
                            <textarea name="description" class="form-textarea"><?= htmlspecialchars($editModule['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Numer tygodnia</label>
                                <input type="number" name="week_number" class="form-input" min="1" max="12"
                                       value="<?= $editModule['week_number'] ?? 1 ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Miesiąc</label>
                                <select name="month_number" class="form-select">
                                    <option value="1" <?= ($editModule['month_number'] ?? 1) == 1 ? 'selected' : '' ?>>Miesiąc 1 - Fundamenty</option>
                                    <option value="2" <?= ($editModule['month_number'] ?? 1) == 2 ? 'selected' : '' ?>>Miesiąc 2 - Integracja</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Czas trwania</label>
                                <input type="text" name="duration" class="form-input"
                                       value="<?= htmlspecialchars($editModule['duration'] ?? '90 min') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Poziom trudności</label>
                                <select name="difficulty" class="form-select">
                                    <option value="beginner" <?= ($editModule['difficulty'] ?? '') == 'beginner' ? 'selected' : '' ?>>Początkujący</option>
                                    <option value="intermediate" <?= ($editModule['difficulty'] ?? '') == 'intermediate' ? 'selected' : '' ?>>Średnio zaawansowany</option>
                                    <option value="advanced" <?= ($editModule['difficulty'] ?? '') == 'advanced' ? 'selected' : '' ?>>Zaawansowany</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ikona</label>
                            <input type="text" name="icon" id="iconInput" class="form-input" 
                                   value="<?= htmlspecialchars($editModule['icon'] ?? '🔧') ?>" style="width: 100px;">
                            <div class="emoji-picker">
                                <?php foreach (['💡', '🚨', '🚗', '💻', '🤖', '🎮', '🧩', '🌱', '⚡', '🔧', '📡', '🎯'] as $emoji): ?>
                                <button type="button" class="emoji-btn" onclick="document.getElementById('iconInput').value='<?= $emoji ?>'"><?= $emoji ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Umiejętności (oddzielone przecinkami)</label>
                            <input type="text" name="skills" class="form-input"
                                   value="<?= htmlspecialchars($editModule['skills'] ?? '') ?>"
                                   placeholder="Podstawy elektroniki, Montaż na breadboard, Pierwszy kod Arduino">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Komponenty (oddzielone przecinkami)</label>
                            <textarea name="components" class="form-textarea" style="min-height: 60px;"
                                      placeholder="Arduino UNO, Breadboard, Diody LED"><?= htmlspecialchars($editModule['components'] ?? '') ?></textarea>
                        </div>

                        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 30px 0;">
                        <h3 style="margin-bottom: 20px; color: var(--secondary);">🛒 Link do Allegro</h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nazwa zestawu na Allegro</label>
                                <input type="text" name="allegro_title" class="form-input"
                                       value="<?= htmlspecialchars($editModule['allegro_title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cena orientacyjna</label>
                                <input type="text" name="allegro_price" class="form-input"
                                       value="<?= htmlspecialchars($editModule['allegro_price'] ?? '') ?>"
                                       placeholder="89-149 zł">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">URL do Allegro</label>
                            <input type="url" name="allegro_url" class="form-input"
                                   value="<?= htmlspecialchars($editModule['allegro_url'] ?? '') ?>"
                                   placeholder="https://allegro.pl/listing?string=...">
                            <p class="form-hint">Użyj wyszukiwania Allegro i skopiuj URL z wynikami</p>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Kolejność sortowania</label>
                                <input type="number" name="sort_order" class="form-input" min="0"
                                       value="<?= $editModule['sort_order'] ?? 0 ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-checkbox">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                           <?= ($editModule['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label for="is_active">Aktywny (widoczny na stronie)</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="save_module" class="btn btn-primary">
                            💾 Zapisz moduł
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tydzień</th>
                                    <th>Ikona</th>
                                    <th>Tytuł</th>
                                    <th>Allegro</th>
                                    <th>Status</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td>
                                        <strong>M<?= $module['month_number'] ?>/T<?= $module['week_number'] ?></strong>
                                    </td>
                                    <td style="font-size: 1.5rem;"><?= $module['icon'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($module['title']) ?></strong><br>
                                        <small style="color: var(--gray);"><?= htmlspecialchars($module['subtitle']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($module['allegro_url']): ?>
                                        <a href="<?= htmlspecialchars($module['allegro_url']) ?>" target="_blank" style="color: var(--secondary);">
                                            <?= htmlspecialchars($module['allegro_price']) ?>
                                        </a>
                                        <?php else: ?>
                                        <span style="color: var(--gray);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $module['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                            <?= $module['is_active'] ? 'Aktywny' : 'Nieaktywny' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="?tab=modules&edit_module=<?= $module['id'] ?>" class="icon-btn" title="Edytuj">✏️</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Czy na pewno usunąć ten moduł?');">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                                <button type="submit" name="delete_module" class="icon-btn danger" title="Usuń">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($tab === 'kits'): ?>
            <!-- Zestawy startowe -->
            <div class="page-header">
                <h1 class="page-title">Zestawy startowe</h1>
                <a href="?tab=kits&new=1" class="btn btn-primary">+ Dodaj zestaw</a>
            </div>

            <?php if (isset($_GET['new']) || $editKit): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= $editKit ? 'Edytuj zestaw' : 'Nowy zestaw' ?></h2>
                    <a href="?tab=kits" class="btn btn-secondary btn-sm">Anuluj</a>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="kit_id" value="<?= $editKit['id'] ?? '' ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Nazwa zestawu *</label>
                            <input type="text" name="name" class="form-input" required
                                   value="<?= htmlspecialchars($editKit['name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Opis</label>
                            <textarea name="description" class="form-textarea"><?= htmlspecialchars($editKit['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Przedział cenowy</label>
                                <input type="text" name="price_range" class="form-input"
                                       value="<?= htmlspecialchars($editKit['price_range'] ?? '') ?>"
                                       placeholder="100-150 zł">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Fraza wyszukiwania Allegro</label>
                                <input type="text" name="allegro_search" class="form-input"
                                       value="<?= htmlspecialchars($editKit['allegro_search'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">URL do Allegro</label>
                            <input type="url" name="allegro_url" class="form-input"
                                   value="<?= htmlspecialchars($editKit['allegro_url'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cechy (oddzielone przecinkami)</label>
                            <input type="text" name="features" class="form-input"
                                   value="<?= htmlspecialchars($editKit['features'] ?? '') ?>"
                                   placeholder="Instrukcje po polsku, 28 projektów, Od 10 lat">
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Kolejność</label>
                                <input type="number" name="sort_order" class="form-input" min="0"
                                       value="<?= $editKit['sort_order'] ?? 0 ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-checkbox">
                                    <input type="checkbox" name="recommended" id="recommended" value="1"
                                           <?= ($editKit['recommended'] ?? 0) ? 'checked' : '' ?>>
                                    <label for="recommended">Polecany (wyróżniony)</label>
                                </div>
                                <div class="form-checkbox" style="margin-top: 10px;">
                                    <input type="checkbox" name="is_active" id="kit_active" value="1"
                                           <?= ($editKit['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    <label for="kit_active">Aktywny</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="save_kit" class="btn btn-primary">
                            💾 Zapisz zestaw
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>Cena</th>
                                    <th>Polecany</th>
                                    <th>Status</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($starterKits as $kit): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($kit['name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($kit['price_range']) ?></td>
                                    <td>
                                        <?= $kit['recommended'] ? '⭐' : '-' ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $kit['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                            <?= $kit['is_active'] ? 'Aktywny' : 'Nieaktywny' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="?tab=kits&edit_kit=<?= $kit['id'] ?>" class="icon-btn" title="Edytuj">✏️</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Czy na pewno usunąć?');">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="kit_id" value="<?= $kit['id'] ?>">
                                                <button type="submit" name="delete_kit" class="icon-btn danger" title="Usuń">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($tab === 'contacts'): ?>
            <!-- Zgłoszenia -->
            <div class="page-header">
                <h1 class="page-title">Zgłoszenia kontaktowe</h1>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= count($contacts) ?></div>
                    <div class="stat-label">Wszystkie zgłoszenia</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($contacts, fn($c) => $c['status'] === 'new')) ?></div>
                    <div class="stat-label">Nowe</div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Rodzic</th>
                                    <th>Dziecko</th>
                                    <th>Kontakt</th>
                                    <th>Forma</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><?= date('d.m.Y H:i', strtotime($contact['created_at'])) ?></td>
                                    <td><strong><?= htmlspecialchars($contact['parent_name']) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($contact['child_name']) ?>
                                        <?php if ($contact['child_age']): ?>
                                        <br><small style="color: var(--gray);"><?= $contact['child_age'] ?> lat</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" style="color: var(--accent);">
                                            <?= htmlspecialchars($contact['email']) ?>
                                        </a>
                                        <?php if ($contact['phone']): ?>
                                        <br><small><?= htmlspecialchars($contact['phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($contact['preferred_mode']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $contact['status'] ?>">
                                            <?= $contact['status'] === 'new' ? 'Nowe' : ucfirst($contact['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php if ($contact['message']): ?>
                                <tr>
                                    <td colspan="6" style="padding: 10px 15px; background: var(--dark); color: var(--gray-light); font-style: italic;">
                                        <?= nl2br(htmlspecialchars($contact['message'])) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if (empty($contacts)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--gray); padding: 40px;">
                                        Brak zgłoszeń
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($tab === 'settings'): ?>
            <!-- Ustawienia -->
            <div class="page-header">
                <h1 class="page-title">Ustawienia strony</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Treści strony głównej</h2>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                        <div class="form-group">
                            <label class="form-label">Tytuł Hero</label>
                            <input type="text" name="hero_title" class="form-input"
                                   value="<?= htmlspecialchars($db->getSetting('hero_title')) ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Podtytuł Hero</label>
                            <input type="text" name="hero_subtitle" class="form-input"
                                   value="<?= htmlspecialchars($db->getSetting('hero_subtitle')) ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tekst "O nas"</label>
                            <textarea name="about_text" class="form-textarea"><?= htmlspecialchars($db->getSetting('about_text')) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tekst CTA (przycisk)</label>
                            <input type="text" name="cta_text" class="form-input"
                                   value="<?= htmlspecialchars($db->getSetting('cta_text')) ?>">
                        </div>

                        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 30px 0;">
                        <h3 style="margin-bottom: 20px;">💰 Cennik</h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Cena zajęć indywidualnych</label>
                                <input type="text" name="price_individual" class="form-input"
                                       value="<?= htmlspecialchars($db->getSetting('price_individual')) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cena zajęć grupowych</label>
                                <input type="text" name="price_group" class="form-input"
                                       value="<?= htmlspecialchars($db->getSetting('price_group')) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Lokalizacja</label>
                            <input type="text" name="location" class="form-input"
                                   value="<?= htmlspecialchars($db->getSetting('location')) ?>">
                        </div>

                        <button type="submit" name="save_settings" class="btn btn-primary">
                            💾 Zapisz ustawienia
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">ℹ️ Informacje o systemie</h2>
                </div>
                <div class="card-body">
                    <p><strong>Baza danych:</strong> SQLite (<?= DB_PATH ?>)</p>
                    <p><strong>Moduły:</strong> <?= count($modules) ?></p>
                    <p><strong>Zestawy:</strong> <?= count($starterKits) ?></p>
                    <p><strong>Zgłoszenia:</strong> <?= count($contacts) ?></p>
                    <p style="margin-top: 15px; color: var(--gray); font-size: 0.9rem;">
                        Aby zmienić dane kontaktowe (email, telefon, social media), edytuj plik <code>config.php</code>
                    </p>
                </div>
            </div>

            <?php endif; ?>
        </main>
    </div>
    <?php endif; ?>
</body>
</html>
