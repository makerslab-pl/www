<?php
/**
 * MakersLab - Database Handler
 * SQLite database management
 */

class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        try {
            $this->db = new PDO('sqlite:' . DB_PATH);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initTables();
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }

    private function initTables() {
        // Tabela modułów edukacyjnych
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS modules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                subtitle VARCHAR(255),
                description TEXT,
                week_number INTEGER,
                month_number INTEGER DEFAULT 1,
                duration VARCHAR(50) DEFAULT '60 min',
                difficulty VARCHAR(20) DEFAULT 'beginner',
                icon VARCHAR(50) DEFAULT '🔧',
                allegro_url TEXT,
                allegro_price VARCHAR(50),
                allegro_title VARCHAR(255),
                skills TEXT,
                components TEXT,
                is_active INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabela zestawów startowych
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS starter_kits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price_range VARCHAR(50),
                allegro_url TEXT,
                allegro_search VARCHAR(255),
                features TEXT,
                recommended INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabela ustawień strony
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key VARCHAR(100) PRIMARY KEY,
                value TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Tabela zgłoszeń kontaktowych
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_name VARCHAR(255),
                child_name VARCHAR(255),
                child_age INTEGER,
                email VARCHAR(255),
                phone VARCHAR(50),
                preferred_mode VARCHAR(50),
                message TEXT,
                status VARCHAR(20) DEFAULT 'new',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Sprawdź czy są dane, jeśli nie - dodaj domyślne
        $count = $this->db->query("SELECT COUNT(*) FROM modules")->fetchColumn();
        if ($count == 0) {
            $this->seedDefaultData();
        }
    }

    private function seedDefaultData() {
        // Moduły - Miesiąc 1
        $modules = [
            [
                'title' => 'Poznajemy Arduino',
                'subtitle' => 'Światelko powitalne',
                'description' => 'Pierwsze spotkanie z elektroniką! Poznajemy płytkę Arduino, breadboard i podstawowe elementy. Budujemy pierwszy układ - migającą diodę LED sterowaną przyciskiem.',
                'week_number' => 1,
                'month_number' => 1,
                'duration' => '90 min',
                'difficulty' => 'beginner',
                'icon' => '💡',
                'allegro_url' => 'https://allegro.pl/listing?string=arduino%20uno%20starter%20kit%20dzieci',
                'allegro_price' => '89-149 zł',
                'allegro_title' => 'Arduino UNO Starter Kit dla dzieci',
                'skills' => 'Podstawy elektroniki,Montaż na breadboard,Pierwszy kod Arduino,Bezpieczeństwo z prądem',
                'components' => 'Arduino UNO,Breadboard,Diody LED (czerwona, zielona, żółta),Rezystory 220Ω,Przycisk tact switch,Przewody połączeniowe',
                'sort_order' => 1
            ],
            [
                'title' => 'Czujniki i dźwięk',
                'subtitle' => 'Alarm drzwiowy',
                'description' => 'Uczymy się jak działają czujniki! Budujemy prosty alarm, który reaguje na otwarcie drzwi i wydaje dźwięk przez buzzer.',
                'week_number' => 2,
                'month_number' => 1,
                'duration' => '90 min',
                'difficulty' => 'beginner',
                'icon' => '🚨',
                'allegro_url' => 'https://allegro.pl/listing?string=czujnik%20odleg%C5%82o%C5%9Bci%20hc-sr04%20arduino',
                'allegro_price' => '8-15 zł',
                'allegro_title' => 'Czujnik odległości HC-SR04',
                'skills' => 'Obsługa czujników,Programowanie warunków IF,Sterowanie buzzerem,Debugowanie kodu',
                'components' => 'Czujnik odległości HC-SR04,Buzzer piezoelektryczny,Dioda LED,Rezystory',
                'sort_order' => 2
            ],
            [
                'title' => 'Silniki i ruch',
                'subtitle' => 'Mini-wózek',
                'description' => 'Czas na ruch! Poznajemy silniki DC i serwomechanizmy. Budujemy prosty pojazd na kółkach.',
                'week_number' => 3,
                'month_number' => 1,
                'duration' => '90 min',
                'difficulty' => 'beginner',
                'icon' => '🚗',
                'allegro_url' => 'https://allegro.pl/listing?string=robot%20car%20kit%20arduino%202wd',
                'allegro_price' => '45-89 zł',
                'allegro_title' => 'Zestaw podwozia robota 2WD',
                'skills' => 'Sterowanie silnikami DC,Mostek H L298N,Zasilanie zewnętrzne,Podstawy mechaniki',
                'components' => 'Podwozie z silnikami 2WD,Mostek H L298N,Koszyk na baterie,Koła',
                'sort_order' => 3
            ],
            [
                'title' => 'Programowanie Arduino',
                'subtitle' => 'Modyfikacje i eksperymenty',
                'description' => 'Zagłębiamy się w kod! Uczymy się modyfikować programy, dodawać nowe funkcje i łączyć poprzednie projekty.',
                'week_number' => 4,
                'month_number' => 1,
                'duration' => '90 min',
                'difficulty' => 'beginner',
                'icon' => '💻',
                'allegro_url' => 'https://allegro.pl/listing?string=arduino%20kurs%20programowania%20ksi%C4%85%C5%BCka',
                'allegro_price' => '39-69 zł',
                'allegro_title' => 'Książka Arduino dla początkujących',
                'skills' => 'Zmienne i funkcje,Pętle FOR i WHILE,Łączenie projektów,Optymalizacja kodu',
                'components' => 'Wszystkie poprzednie elementy,Kabel USB,Komputer z Arduino IDE',
                'sort_order' => 4
            ],
            // Miesiąc 2
            [
                'title' => 'Robot unikający przeszkód',
                'subtitle' => 'Inteligentny pojazd',
                'description' => 'Łączymy czujniki z ruchem! Robot sam omija przeszkody dzięki czujnikowi odległości.',
                'week_number' => 5,
                'month_number' => 2,
                'duration' => '120 min',
                'difficulty' => 'intermediate',
                'icon' => '🤖',
                'allegro_url' => 'https://allegro.pl/listing?string=robot%20arduino%20obstacle%20avoidance',
                'allegro_price' => '89-159 zł',
                'allegro_title' => 'Robot omijający przeszkody - zestaw',
                'skills' => 'Integracja czujników,Algorytmy decyzyjne,Kalibracja sensorów,Testowanie',
                'components' => 'Zestaw robota 2WD,Czujnik HC-SR04,Serwomechanizm SG90,Uchwyt na czujnik',
                'sort_order' => 5
            ],
            [
                'title' => 'Sterowanie joystickiem',
                'subtitle' => 'Robot sterowany ręcznie',
                'description' => 'Budujemy pilota do robota! Joystick analogowy pozwala na precyzyjne sterowanie ruchem.',
                'week_number' => 6,
                'month_number' => 2,
                'duration' => '120 min',
                'difficulty' => 'intermediate',
                'icon' => '🎮',
                'allegro_url' => 'https://allegro.pl/listing?string=joystick%20analogowy%20arduino%20modul',
                'allegro_price' => '5-12 zł',
                'allegro_title' => 'Moduł joysticka analogowego',
                'skills' => 'Odczyt analogowy,Mapowanie wartości,Sterowanie proporcjonalne,Komunikacja przewodowa',
                'components' => 'Joystick analogowy,Przewody długie,Opcjonalnie: moduł Bluetooth HC-05',
                'sort_order' => 6
            ],
            [
                'title' => 'Labirynt Challenge',
                'subtitle' => 'Robot rozwiązujący labirynt',
                'description' => 'Zaawansowany projekt! Robot z wieloma czujnikami sam znajduje drogę przez labirynt.',
                'week_number' => 7,
                'month_number' => 2,
                'duration' => '120 min',
                'difficulty' => 'intermediate',
                'icon' => '🧩',
                'allegro_url' => 'https://allegro.pl/listing?string=czujnik%20linii%20tcrt5000%20arduino',
                'allegro_price' => '3-8 zł',
                'allegro_title' => 'Czujniki linii TCRT5000 (5 szt)',
                'skills' => 'Wieloczujnikowe systemy,Algorytm śledzenia,Logika decyzyjna,Optymalizacja trasy',
                'components' => 'Czujniki linii TCRT5000 x3-5,Czujniki odległości x2,Materiały na labirynt',
                'sort_order' => 7
            ],
            [
                'title' => 'Projekt własny',
                'subtitle' => 'Automatyczny podlewacz roślin',
                'description' => 'Finałowy projekt! Dziecko projektuje i buduje własne urządzenie od zera - np. system automatycznego podlewania.',
                'week_number' => 8,
                'month_number' => 2,
                'duration' => '120 min',
                'difficulty' => 'intermediate',
                'icon' => '🌱',
                'allegro_url' => 'https://allegro.pl/listing?string=czujnik%20wilgotno%C5%9Bci%20gleby%20arduino',
                'allegro_price' => '5-15 zł',
                'allegro_title' => 'Czujnik wilgotności gleby',
                'skills' => 'Projektowanie od zera,Dobór komponentów,Prezentacja projektu,Dokumentacja',
                'components' => 'Czujnik wilgotności gleby,Mini pompka wodna 5V,Wężyk silikonowy,Przekaźnik 5V',
                'sort_order' => 8
            ]
        ];

        $stmt = $this->db->prepare("
            INSERT INTO modules (title, subtitle, description, week_number, month_number, duration, difficulty, icon, allegro_url, allegro_price, allegro_title, skills, components, sort_order)
            VALUES (:title, :subtitle, :description, :week_number, :month_number, :duration, :difficulty, :icon, :allegro_url, :allegro_price, :allegro_title, :skills, :components, :sort_order)
        ");

        foreach ($modules as $module) {
            $stmt->execute($module);
        }

        // Zestawy startowe
        $kits = [
            [
                'name' => 'Arduino UNO R3 Starter Kit dla dzieci',
                'description' => 'Kompletny zestaw na 2 miesiące nauki. 28 lekcji po polsku, breadboard, LED RGB, czujniki, buzzer.',
                'price_range' => '100-150 zł',
                'allegro_url' => 'https://allegro.pl/listing?string=arduino%20uno%20r3%20starter%20kit%20dzieci',
                'allegro_search' => 'Zestaw Edukacyjny Arduino UNO R3 dzieci',
                'features' => 'Instrukcje po polsku,28 projektów,Bez lutowania,Od 10 lat',
                'recommended' => 1,
                'sort_order' => 1
            ],
            [
                'name' => 'Elegoo UNO Project Super Starter Kit',
                'description' => '200+ elementów, tutoriale PDF po polsku, projekty jak alarm czy robot - wizualne schematy.',
                'price_range' => '120-180 zł',
                'allegro_url' => 'https://allegro.pl/listing?string=elegoo%20arduino%20starter%20kit',
                'allegro_search' => 'Elegoo Arduino Starter Kit',
                'features' => '200+ komponentów,Tutoriale PDF,Schematy Fritzing,Pudełko organizacyjne',
                'recommended' => 0,
                'sort_order' => 2
            ],
            [
                'name' => 'Zestaw Robot Car 2WD Arduino',
                'description' => 'Kompletne podwozie robota z silnikami, idealny do projektów mobilnych od tygodnia 3.',
                'price_range' => '45-89 zł',
                'allegro_url' => 'https://allegro.pl/listing?string=robot%20car%20kit%20arduino%202wd',
                'allegro_search' => 'Robot Car Kit Arduino 2WD',
                'features' => 'Gotowe podwozie,Silniki DC,Koła,Instrukcja montażu',
                'recommended' => 0,
                'sort_order' => 3
            ]
        ];

        $stmt = $this->db->prepare("
            INSERT INTO starter_kits (name, description, price_range, allegro_url, allegro_search, features, recommended, sort_order)
            VALUES (:name, :description, :price_range, :allegro_url, :allegro_search, :features, :recommended, :sort_order)
        ");

        foreach ($kits as $kit) {
            $stmt->execute($kit);
        }

        // Ustawienia domyślne
        $settings = [
            ['hero_title', 'Warsztaty robotyki dla dzieci'],
            ['hero_subtitle', 'Nauka przez budowanie - Arduino, elektronika, prototypy'],
            ['about_text', 'MakersLab to warsztaty projektowe dla dzieci od 10 lat. Uczymy przez praktykę - każde zajęcia to nowy projekt, który dziecko zabiera do domu.'],
            ['cta_text', 'Zapisz się na zajęcia próbne'],
            ['price_individual', '150 zł / 90 min'],
            ['price_group', '80 zł / 90 min'],
            ['location', 'Trójmiasto (Gdańsk, Sopot, Gdynia) lub Online']
        ];

        $stmt = $this->db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
    }

    // Helper methods
    public function getModules($activeOnly = true) {
        $sql = "SELECT * FROM modules";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getModule($id) {
        $stmt = $this->db->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getStarterKits($activeOnly = true) {
        $sql = "SELECT * FROM starter_kits";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getSetting($key, $default = '') {
        $stmt = $this->db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    }

    public function updateSetting($key, $value) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
        return $stmt->execute([$key, $value]);
    }

    public function saveContact($data) {
        $stmt = $this->db->prepare("
            INSERT INTO contacts (parent_name, child_name, child_age, email, phone, preferred_mode, message)
            VALUES (:parent_name, :child_name, :child_age, :email, :phone, :preferred_mode, :message)
        ");
        return $stmt->execute($data);
    }

    public function getContacts() {
        return $this->db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();
    }
}
