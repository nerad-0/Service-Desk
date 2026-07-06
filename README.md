# ServisDesk

ServisDesk je maturitní webová aplikace pro evidenci a řešení školních IT požadavků. Studenti nebo zaměstnanci školy mohou založit požadavek, technik jej řeší a administrátor spravuje uživatele, kategorie a přehledy.

Projekt používá jednoduchý a obhajitelný stack:

- HTML5, CSS3 a JavaScript na frontendu
- PHP 8+ na backendu
- MySQL nebo MariaDB jako relační databázi
- PDO a prepared statements pro práci s databází
- REST API a JSON pro komunikaci mezi frontendem a backendem
- Session autentizaci, role a backendovou autorizaci

## Aktuální stav

Projekt je vytvářen postupně podle maturitního zadání. Tato verze obsahuje základní analýzu, plán, strukturu projektu a konfigurační vzor. Další commity doplní databázi, API, frontend, testy a finální dokumentaci.

## Rychlé spuštění

1. Nainstalujte PHP 8+ a MySQL/MariaDB, například přes XAMPP nebo Laragon.
2. Zkopírujte `config/config.example.php` jako `config/config.php`.
3. Upravte přihlašovací údaje k databázi v `config/config.php`.
4. Vytvořte databázi `servisdesk`.
5. Importujte `database/schema.sql`.
6. Importujte `database/seed.sql`.
7. Otevřete aplikaci v prohlížeči přes web server, například `http://localhost/servisdesk/public`.

Podrobný postup je v [docs/INSTALLATION.md](docs/INSTALLATION.md).

## Testovací účty

Testovací účty budou doplněny v `database/seed.sql`.

## Dokumentace

- [Analýza projektu](docs/ANALYSIS.md)
- [Implementační plán](docs/IMPLEMENTATION_PLAN.md)
- [Instalace](docs/INSTALLATION.md)

