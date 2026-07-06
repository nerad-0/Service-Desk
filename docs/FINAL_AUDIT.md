# Finální audit projektu

Datum auditu: 2026-07-06

## 1. Název projektu

ServisDesk

## 2. Co projekt řeší

Projekt řeší evidenci a zpracování školních IT požadavků. Uživatel nahlásí problém, technik jej řeší a administrátor spravuje uživatele, kategorie a dohled nad aplikací.

## 3. Hlavní funkce

- registrace, přihlášení a odhlášení
- role `USER`, `TECHNICIAN`, `ADMIN`
- CRUD požadavků
- komentáře a interní poznámky
- vyhledávání, filtrování, řazení a stránkování
- administrace uživatelů a kategorií
- dashboard se statistikami a grafem
- auditní log
- REST API a JSON

## 4. Použité technologie

- HTML5
- CSS3
- JavaScript
- Fetch API
- PHP 8+
- MySQL/MariaDB
- PDO
- Git

## 5. Struktura databáze

Databáze obsahuje tabulky:

- `roles`
- `users`
- `categories`
- `tickets`
- `ticket_comments`
- `ticket_status_history`
- `audit_logs`

Podrobnosti jsou v `docs/DATABASE.md`.

## 6. Přehled API

Hlavní endpointy:

- `/auth/register`
- `/auth/login`
- `/auth/logout`
- `/auth/me`
- `/profile`
- `/categories`
- `/tickets`
- `/tickets/{id}`
- `/tickets/{id}/comments`
- `/admin/dashboard`
- `/admin/users`
- `/admin/categories`
- `/admin/audit-log`

Podrobnosti jsou v `docs/API.md`.

## 7. Bezpečnostní mechanismy

- PDO prepared statements
- hashování hesel přes `password_hash`
- ověření hesel přes `password_verify`
- session autentizace
- backendová autorizace podle rolí
- kontrola vlastnictví požadavků proti IDOR
- CSRF token pro měnící požadavky
- escapování výstupu ve frontendu
- lokální konfigurace mimo Git

Podrobnosti jsou v `SECURITY.md`.

## 8. Provedené testy

V tomto prostředí byly provedeny dostupné kontroly:

- `tests/static_check.ps1` prošel
- `node --check public/assets/js/api.js` prošel
- `node --check public/assets/js/app.js` prošel
- `git diff --check` prošel
- Git pracovní strom byl před vytvořením tohoto auditu čistý
- tracked soubory neobsahovaly `config/config.php` ani `.env`

PHP a MySQL/MariaDB nebyly v PATH dostupné, takže nebylo možné spustit PHP lint, import SQL ani funkční testy API proti reálné databázi.

## 9. Umístění dokumentace

- `README.md`
- `docs/ANALYSIS.md`
- `docs/API.md`
- `docs/DATABASE.md`
- `docs/INSTALLATION.md`
- `docs/TESTING.md`
- `docs/MATURITA_DOCUMENTATION.md`
- `docs/PRESENTATION.md`
- `docs/DEMO_SCRIPT.md`
- `docs/COMMISSION_QUESTIONS.md`
- `docs/DEFENSE_GUIDE.md`
- `SECURITY.md`

## 10. Stav Git repozitáře

Projekt je lokální Git repozitář. Historie obsahuje postupné commity:

- analýza a založení projektu
- databázové schéma a seed data
- PHP REST API
- responzivní frontend
- API, bezpečnost a testování
- obhajobové materiály

## 11. Stav GitHub repozitáře

GitHub CLI nebylo v prostředí dostupné, proto nebyl vzdálený repozitář vytvořen automaticky. Projekt je připravený k ručnímu připojení remote podle README.

## 12. Jak projekt spustit

1. Nainstalovat PHP a MySQL/MariaDB, například přes XAMPP nebo Laragon.
2. Vytvořit databázi `servisdesk`.
3. Importovat `database/schema.sql`.
4. Importovat `database/seed.sql`.
5. Zkopírovat `config/config.example.php` na `config/config.php`.
6. Upravit databázové připojení.
7. Otevřít `http://localhost/servisdesk/public`.

## 13. Testovací účty

Všechny testovací účty mají heslo `password`.

- `admin@servisdesk.local`
- `technik@servisdesk.local`
- `student@servisdesk.local`
- `ucitel@servisdesk.local`

## 14. Co ukázat komisi

- přihlášení běžného uživatele
- vytvoření požadavku
- komentář k požadavku
- filtr a vyhledávání
- přihlášení administrátora
- dashboard a graf
- správa uživatelů nebo kategorií
- ukázka API dokumentace
- ukázka databázového schématu

## 15. Části kódu, které musí student umět vysvětlit

- `public/api/index.php`
- `src/Core/Router.php`
- `src/Core/Database.php`
- `src/Core/Auth.php`
- `src/Core/Csrf.php`
- `src/Controllers/TicketController.php`
- `public/assets/js/api.js`
- `public/assets/js/app.js`
- `database/schema.sql`

Podrobnosti jsou v `docs/DEFENSE_GUIDE.md`.

## 16. Části vyžadující ruční ověření

- import `schema.sql` a `seed.sql`
- přihlášení testovacích účtů
- reálné volání REST API v PHP/MySQL prostředí
- funkčnost dashboardu proti skutečné databázi
- odeslání formulářů přes prohlížeč
- případné pořízení screenshotů pro prezentaci

