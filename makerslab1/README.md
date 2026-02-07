# 🤖 MakersLab - Strona warsztatów robotyki dla dzieci

Kompletna strona PHP z prostym CMS do zarządzania programem edukacyjnym robotyki dla dzieci. Zintegrowana z linkami do Allegro dla każdego modułu.

## ✨ Funkcje

- **Strona główna** z programem 8-tygodniowych zajęć
- **Prosty CMS** do zarządzania:
  - Modułami zajęć (tytuł, opis, komponenty, linki Allegro)
  - Zestawami startowymi z linkami do Allegro
  - Ustawieniami strony (treści, cennik)
  - Zgłoszeniami kontaktowymi
- **SQLite** - bez potrzeby zewnętrznej bazy danych
- **Responsywny design** - działa na telefonach i tabletach
- **Bezpieczeństwo** - CSRF protection, sanityzacja danych
- **Nowoczesny wygląd** - styl "maker lab", ciemny motyw, animacje

## 📁 Struktura projektu

```
makerslab/
├── index.php           # Strona główna
├── admin.php           # Panel CMS
├── config.php          # Konfiguracja
├── .htaccess           # Konfiguracja Apache
├── includes/
│   └── database.php    # Klasa obsługi SQLite
├── data/
│   └── makerslab.db    # Baza danych (tworzona automatycznie)
└── assets/             # Pliki statyczne (opcjonalnie)
```

## 🚀 Instalacja

### Wymagania
- PHP 7.4+ z rozszerzeniem SQLite (PDO)
- Serwer Apache z mod_rewrite (lub nginx)

### Kroki instalacji

1. **Skopiuj pliki na serwer:**
   ```bash
   # Przez FTP lub SCP
   scp -r makerslab/ user@server:/var/www/html/
   ```

2. **Ustaw uprawnienia:**
   ```bash
   chmod 755 makerslab/
   chmod -R 777 makerslab/data/
   ```

3. **Zmień hasło administratora:**
   Edytuj `config.php`:
   ```php
   define('ADMIN_PASSWORD', 'twoje_bezpieczne_haslo');
   ```

4. **Skonfiguruj dane kontaktowe:**
   Edytuj `config.php`:
   ```php
   define('CONTACT_EMAIL', 'twoj@email.pl');
   define('CONTACT_PHONE', '+48 123 456 789');
   ```

5. **Otwórz stronę:**
   - Strona główna: `https://makerslab.pl/`
   - Panel CMS: `https://makerslab.pl/admin.php`

## 🔧 Konfiguracja

### config.php

```php
// Hasło do panelu CMS
define('ADMIN_PASSWORD', 'zmien_na_bezpieczne_haslo');

// Dane kontaktowe
define('CONTACT_EMAIL', 'kontakt@makerslab.pl');
define('CONTACT_PHONE', '+48 123 456 789');
define('CONTACT_LOCATION', 'Trójmiasto / Online');

// Social media
define('SOCIAL_FACEBOOK', 'https://facebook.com/makerslab');
define('SOCIAL_INSTAGRAM', 'https://instagram.com/makerslab');
define('SOCIAL_YOUTUBE', 'https://youtube.com/@makerslab');
```

## 📱 Panel CMS

Logowanie: `admin.php` → domyślne hasło: `makerslab2024`

### Zarządzanie modułami
- Dodawanie/edycja modułów programu
- Ustawianie ikon (emoji), poziomów trudności
- Dodawanie linków do Allegro z ceną
- Aktywacja/deaktywacja modułów

### Zestawy startowe
- Polecane zestawy Arduino
- Linki do wyszukiwania na Allegro
- Oznaczanie polecanego zestawu

### Ustawienia
- Treści strony głównej
- Cennik zajęć
- Lokalizacja

### Zgłoszenia
- Lista zgłoszeń z formularza kontaktowego
- Dane rodzica i dziecka
- Preferowana forma zajęć

## 🛒 Linki do Allegro

Każdy moduł może mieć przypisany link do Allegro. Zalecane formaty:

```
# Link do wyszukiwania (najlepszy)
https://allegro.pl/listing?string=arduino%20uno%20starter%20kit

# Link do konkretnej oferty
https://allegro.pl/oferta/arduino-uno-r3-starter-kit-12345678
```

## 🔒 Bezpieczeństwo

- Hasło administratora przechowywane w config.php (zmień domyślne!)
- Ochrona CSRF dla formularzy
- Sanityzacja wszystkich danych wejściowych
- Blokada dostępu do plików konfiguracyjnych przez .htaccess
- SQLite z prepared statements

### Zalecenia produkcyjne

1. Zmień domyślne hasło w `config.php`
2. Włącz HTTPS (odkomentuj regułę w .htaccess)
3. Ustaw odpowiednie uprawnienia plików
4. Regularnie twórz kopie zapasowe `data/makerslab.db`

## 📧 Formularz kontaktowy

Zgłoszenia są zapisywane w bazie danych. Aby otrzymywać powiadomienia email, dodaj do `index.php` w sekcji obsługi formularza:

```php
// Po zapisaniu do bazy
mail(CONTACT_EMAIL, 'Nowe zgłoszenie MakersLab', 
     "Rodzic: {$contactData['parent_name']}\nEmail: {$contactData['email']}");
```

## 🎨 Personalizacja

### Zmiana kolorów
Edytuj zmienne CSS w `index.php`:

```css
:root {
    --primary: #00ff88;      /* Główny kolor (zielony) */
    --secondary: #ff6b35;    /* Akcent (pomarańczowy) */
    --accent: #00d4ff;       /* Dodatkowy (niebieski) */
    --dark: #0a0a0f;         /* Tło */
}
```

### Dodanie logo
Zamień tekst logo na obrazek w `index.php`:

```html
<a href="#" class="logo">
    <img src="assets/images/logo.png" alt="MakersLab" height="40">
</a>
```

## 📊 Backup bazy danych

```bash
# Kopia zapasowa
cp data/makerslab.db data/makerslab_backup_$(date +%Y%m%d).db

# Przywracanie
cp data/makerslab_backup_20240101.db data/makerslab.db
```

## 🐛 Rozwiązywanie problemów

### Baza danych się nie tworzy
```bash
chmod 777 data/
```

### Błąd 500
Sprawdź logi Apache i upewnij się, że PHP ma rozszerzenie SQLite:
```bash
php -m | grep -i sqlite
```

### Formularz nie działa
Sprawdź czy sesje PHP działają poprawnie i czy katalog `data/` ma uprawnienia zapisu.

## 📄 Licencja

MIT License - możesz używać i modyfikować dowolnie.

---

**Stworzono dla MakersLab** - Warsztaty robotyki i elektroniki dla dzieci
