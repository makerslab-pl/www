# MakersLab - Makefile
# Komendy do zarządzania projektem

.PHONY: help build up down restart logs shell test test-coverage lint clean

# Domyślna komenda
help:
	@echo "MakersLab - Dostępne komendy:"
	@echo ""
	@echo "  Docker:"
	@echo "    make build       - Buduj obrazy Docker"
	@echo "    make up          - Uruchom kontenery"
	@echo "    make down        - Zatrzymaj kontenery"
	@echo "    make restart     - Restartuj kontenery"
	@echo "    make logs        - Pokaż logi"
	@echo "    make shell       - Shell w kontenerze app"
	@echo ""
	@echo "  Testy:"
	@echo "    make test        - Uruchom wszystkie testy"
	@echo "    make test-unit   - Uruchom testy jednostkowe"
	@echo "    make test-int    - Uruchom testy integracyjne"
	@echo "    make test-cov    - Testy z pokryciem kodu"
	@echo ""
	@echo "  Jakość kodu:"
	@echo "    make lint        - Sprawdź styl kodu (PSR-12)"
	@echo "    make lint-fix    - Napraw styl kodu"
	@echo "    make analyse     - Analiza statyczna (PHPStan)"
	@echo "    make check       - Wszystkie sprawdzenia"
	@echo ""
	@echo "  Inne:"
	@echo "    make install     - Zainstaluj zależności"
	@echo "    make clean       - Wyczyść pliki tymczasowe"
	@echo "    make backup      - Backup bazy danych"

# ==========================================
# DOCKER
# ==========================================

build:
	docker-compose build

up:
	docker-compose up -d
	@echo ""
	@echo "✅ MakersLab uruchomiony!"
	@echo "   Strona:     http://localhost:8080"
	@echo "   Admin:      http://localhost:8080/admin.php"
	@echo "   Adminer:    http://localhost:8081 (jeśli uruchomiony z --profile dev)"

up-dev:
	docker-compose --profile dev up -d
	@echo ""
	@echo "✅ MakersLab (tryb dev) uruchomiony!"
	@echo "   Strona:     http://localhost:8080"
	@echo "   Admin:      http://localhost:8080/admin.php"
	@echo "   Adminer:    http://localhost:8081"

down:
	docker-compose down

restart: down up

logs:
	docker-compose logs -f

logs-app:
	docker-compose logs -f app

shell:
	docker-compose exec app bash

# ==========================================
# TESTY
# ==========================================

# Uruchom testy w kontenerze
test:
	docker-compose run --rm test

# Lub lokalnie (wymaga composer install)
test-local:
	./vendor/bin/phpunit --colors=always

test-unit:
	./vendor/bin/phpunit --testsuite=Unit --colors=always

test-int:
	./vendor/bin/phpunit --testsuite=Integration --colors=always

test-feature:
	./vendor/bin/phpunit --testsuite=Feature --colors=always

test-cov:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage --coverage-text
	@echo ""
	@echo "📊 Raport pokrycia: coverage/index.html"

test-ci:
	./vendor/bin/phpunit --colors=always --log-junit test-results.xml

# ==========================================
# JAKOŚĆ KODU
# ==========================================

lint:
	./vendor/bin/phpcs --standard=PSR12 includes/ tests/

lint-fix:
	./vendor/bin/phpcbf --standard=PSR12 includes/ tests/

analyse:
	./vendor/bin/phpstan analyse includes/ tests/ --level=5

check: lint analyse test
	@echo ""
	@echo "✅ Wszystkie sprawdzenia przeszły pomyślnie!"

# ==========================================
# INSTALACJA
# ==========================================

install:
	composer install

install-prod:
	composer install --no-dev --optimize-autoloader

update:
	composer update

# ==========================================
# BACKUP & CLEAN
# ==========================================

backup:
	@mkdir -p backups
	@cp data/makerslab.db backups/makerslab_$(shell date +%Y%m%d_%H%M%S).db 2>/dev/null || echo "Brak bazy danych do backupu"
	@echo "✅ Backup utworzony w katalogu backups/"

clean:
	rm -rf coverage/
	rm -rf .phpunit.cache/
	rm -f coverage.txt coverage.xml test-results.xml
	rm -rf vendor/
	@echo "✅ Pliki tymczasowe usunięte"

clean-docker:
	docker-compose down -v --rmi local
	@echo "✅ Kontenery i obrazy Docker usunięte"

# ==========================================
# DEVELOPMENT
# ==========================================

# Uruchom serwer PHP (bez Dockera)
serve:
	php -S localhost:8000

# Watch dla testów (wymaga fswatch lub inotifywait)
watch:
	@echo "Obserwuję zmiany w plikach..."
	@while true; do \
		fswatch -1 includes/ tests/ && make test-local; \
	done

# Generuj dokumentację
docs:
	@echo "TODO: Dodaj generowanie dokumentacji"
