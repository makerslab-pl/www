# 🤖 MakersLab - Strona warsztatów robotyki dla dzieci

Kompletna strona PHP z prostym CMS do zarządzania programem edukacyjnym robotyki dla dzieci. Zintegrowana z linkami do Allegro dla każdego modułu.

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-PHPUnit-brightgreen.svg)](https://phpunit.de)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)

## ✨ Funkcje

- **Strona główna** z programem 8-tygodniowych zajęć
- **Prosty CMS** do zarządzania modułami, zestawami i ustawieniami
- **SQLite** - bez potrzeby zewnętrznej bazy danych
- **Docker** - łatwe uruchomienie w kontenerze
- **Testy PHPUnit** - pokrycie testami jednostkowymi i integracyjnymi
- **Responsywny design** - działa na telefonach i tabletach
- **Bezpieczeństwo** - CSRF protection, sanityzacja danych

## 🚀 Szybki start z Docker

```bash
# Klonuj repozytorium
git clone https://github.com/yourusername/makerslab.git
cd makerslab

# Skonfiguruj zmienne środowiskowe
cp .env.example .env
# Edytuj .env i zmień hasło administratora!

# Uruchom z Docker Compose
docker-compose up -d

# Otwórz w przeglądarce
# Strona: http://localhost:8080
# Admin:  http://localhost:8080/admin.php (hasło z .env)
```

### ⚙️ Konfiguracja środowiskowa

Projekt używa pliku `.env` do konfiguracji. Skopiuj `.env.example` do `.env` i dostosuj wartości:

```bash
# Uruchom skrypt konfiguracyjny
./scripts/setup-env.sh
```

Lub ręcznie skopiuj i edytuj plik:

```bash
cp .env.example .env
nano .env  # lub użyj swojego ulubionego edytora
```

**Ważne:** Zawsze zmieniaj domyślne hasło administratora w pliku `.env`!

## 📋 Zmienne środowiskowe

Plik `.env` zawiera wszystkie konfigurowalne zmienne:

### Konfiguracja strony
- `SITE_NAME` - Nazwa strony
- `SITE_TAGLINE` - Tagline/opis strony
- `SITE_URL` - URL strony (ważny dla produkcji)
- `ADMIN_PASSWORD` - Hasło do panelu admina

### Kontakt
- `CONTACT_EMAIL` - Email kontaktowy
- `CONTACT_PHONE` - Telefon kontaktowy
- `CONTACT_LOCATION` - Lokalizacja

### Social media
- `SOCIAL_FACEBOOK` - Link do Facebooka
- `SOCIAL_INSTAGRAM` - Link do Instagrama
- `SOCIAL_YOUTUBE` - Link do YouTube

### Ustawienia techniczne
- `TIMEZONE` - Strefa czasowa
- `DEBUG_MODE` - Tryb debugowania (true/false)
- `APP_ENV` - Środowisko (development/production/testing)
- `DB_PATH` - Ścieżka do bazy SQLite

### Konfiguracja PHP
- `PHP_MEMORY_LIMIT` - Limit pamięci PHP
- `PHP_UPLOAD_MAX_FILESIZE` - Maksymalny rozmiar uploadu
- `PHP_POST_MAX_SIZE` - Maksymalny rozmiar POST
- `PHP_MAX_EXECUTION_TIME` - Maksymalny czas wykonania

### Porty Docker
- `PORT` - Port aplikacji (domyślnie 8080)
- `ADMINER_PORT` - Port Adminera (domyślnie 8081)

## 📁 Struktura projektu

```
makerslab/
├── index.php              # Strona główna
├── admin.php              # Panel CMS
├── bootstrap.php          # Bootstrap - wczytuje .env i sesję
├── config.php             # Konfiguracja (wczytuje z .env)
├── .env                   # Zmienne środowiskowe (utwórz z .env.example)
├── .env.example           # Szablon zmiennych środowiskowych
├── Dockerfile             # Obraz Docker
├── Dockerfile.test        # Obraz dla testów
├── docker-compose.yml     # Konfiguracja Docker Compose
├── composer.json          # Zależności PHP
├── phpunit.xml            # Konfiguracja testów
├── Makefile               # Komendy make
├── scripts/
│   └── setup-env.sh       # Skrypt konfiguracyjny .env
├── includes/
│   └── database.php       # Klasa SQLite
├── tests/
│   ├── bootstrap.php      # Bootstrap testów
│   ├── TestCase.php       # Bazowa klasa testowa
│   ├── Unit/              # Testy jednostkowe
│   ├── Integration/       # Testy integracyjne
│   └── Feature/           # Testy funkcjonalne
├── assets/
│   ├── css/               # Style CSS
│   ├── js/                # JavaScript
│   └── images/            # Obrazy i ikony
└── data/                  # Baza SQLite (auto-generowana)
```

## 🐳 Docker

### Uruchomienie

```bash
# Produkcja
docker-compose up -d

# Development (z Adminer do bazy)
docker-compose --profile dev up -d

# Zatrzymanie
docker-compose down

# Logi
docker-compose logs -f

# Shell w kontenerze
docker-compose exec app bash
```

### Porty

| Usługa   | Port  | Opis                    |
|----------|-------|-------------------------|
| App      | 8080  | Strona główna           |
| Adminer  | 8081  | GUI do bazy (dev mode)  |

### Zmienne środowiskowe

```bash
# W docker-compose.yml lub .env
PHP_DISPLAY_ERRORS=1
PHP_ERROR_REPORTING=E_ALL
```

## 🧪 Testy

### Uruchomienie testów

```bash
# Wszystkie testy (w Docker)
docker-compose run --rm test

# Lub lokalnie (wymaga composer install)
composer install
./vendor/bin/phpunit

# Tylko testy jednostkowe
./vendor/bin/phpunit --testsuite=Unit

# Tylko testy integracyjne
./vendor/bin/phpunit --testsuite=Integration

# Z pokryciem kodu
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage
```

### Struktura testów

```
tests/
├── Unit/
│   ├── DatabaseTest.php    # Testy CRUD bazy danych
│   └── SecurityTest.php    # Testy CSRF, XSS, SQL Injection
├── Integration/
│   └── ContactFormTest.php # Testy formularza kontaktowego
└── Feature/
    └── AdminPanelTest.php  # Testy panelu administracyjnego
```

### Pokrycie testów

Po uruchomieniu `make test-cov`, raport dostępny w `coverage/index.html`.

## 🔧 Makefile

```bash
make help          # Pokaż dostępne komendy

# Docker
make build         # Buduj obrazy
make up            # Uruchom kontenery
make down          # Zatrzymaj kontenery
make logs          # Pokaż logi
make shell         # Shell w kontenerze

# Testy
make test          # Wszystkie testy
make test-unit     # Testy jednostkowe
make test-cov      # Testy z pokryciem

# Jakość kodu
make lint          # Sprawdź PSR-12
make lint-fix      # Napraw styl kodu
make analyse       # PHPStan analiza
make check         # Wszystkie sprawdzenia
```

## 🛠 Instalacja bez Dockera

### Wymagania
- PHP 8.1+ z PDO SQLite
- Composer
- Apache z mod_rewrite (lub nginx)

### Kroki

```bash
# 1. Zainstaluj zależności
composer install

# 2. Ustaw uprawnienia
chmod -R 777 data/

# 3. Skonfiguruj Apache lub uruchom serwer PHP
php -S localhost:8000

# 4. Otwórz http://localhost:8000
```

## 🔒 Bezpieczeństwo

### Zabezpieczenia

- **CSRF** - tokeny dla wszystkich formularzy
- **XSS** - htmlspecialchars dla wszystkich danych
- **SQL Injection** - prepared statements
- **Session** - httponly cookies, strict mode

### Konfiguracja produkcyjna

1. Zmień hasło w `.env`:
```bash
ADMIN_PASSWORD=twoje_bezpieczne_haslo
```

2. Ustaw zmienne środowiskowe dla produkcji:
```bash
DEBUG_MODE=false
APP_ENV=production
PHP_DISPLAY_ERRORS=0
```

3. Włącz HTTPS w `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

4. Ustaw odpowiednie uprawnienia:
```bash
chmod 755 makerslab/
chmod -R 777 makerslab/data/
chmod 644 makerslab/config.php
chmod 600 makerslab/.env
```

## 🛒 Linki do Allegro

Każdy moduł zawiera link do Allegro z zestawem potrzebnych komponentów:

| Tydzień | Projekt                  | Zestaw Allegro              |
|---------|--------------------------|----------------------------|
| 1       | Światelko powitalne      | Arduino UNO Starter Kit    |
| 2       | Alarm drzwiowy           | Czujnik HC-SR04            |
| 3       | Mini-wózek               | Robot Car Kit 2WD          |
| 4       | Programowanie            | Książka Arduino            |
| 5       | Robot unikający przeszkód| Robot obstacle avoidance   |
| 6       | Sterowanie joystickiem   | Moduł joysticka            |
| 7       | Labirynt Challenge       | Czujniki linii TCRT5000    |
| 8       | Automatyczny podlewacz   | Czujnik wilgotności gleby  |

## 📊 CI/CD (opcjonalnie)

### GitHub Actions

```yaml
# .github/workflows/test.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo_sqlite
      - run: composer install
      - run: ./vendor/bin/phpunit
```

## 📄 Licencja

MIT License - możesz używać i modyfikować dowolnie.

## 🤝 Współpraca

1. Fork repozytorium
2. Utwórz branch (`git checkout -b feature/nowa-funkcja`)
3. Commit (`git commit -m 'Dodaj nową funkcję'`)
4. Push (`git push origin feature/nowa-funkcja`)
5. Otwórz Pull Request

---

**Stworzono dla MakersLab** - Warsztaty robotyki i elektroniki dla dzieci 🤖
