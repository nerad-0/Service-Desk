# ServisDesk

ServisDesk je maturitní webová aplikace pro evidenci a řešení školních IT požadavků. Uživatelé mohou hlásit problémy, technici je řeší a administrátor spravuje uživatele, kategorie, dashboard a auditní log.

## Hlavní funkce

- registrace, přihlášení a odhlášení
- role `USER`, `TECHNICIAN`, `ADMIN`
- CRUD požadavků
- komentáře k požadavkům
- interní poznámky pro techniky a administrátory
- vyhledávání, filtrování, řazení a stránkování
- správa profilu
- administrace uživatelů a kategorií
- dashboard se statistikami a grafem z databázových dat
- auditní log důležitých akcí
- REST API ve formátu JSON
- CSRF ochrana pro měnící požadavky

## Použité technologie

- HTML5
- CSS3
- JavaScript
- Fetch API
- PHP 8+
- MySQL nebo MariaDB
- PDO
- Git

## Systémové požadavky

- PHP 8.0 nebo novější
- MySQL 8 nebo MariaDB 10.4+
- web server s podporou PHP, například Apache v XAMPP nebo Laragonu
- Git

## Instalace

1. Zkopírujte projekt do webového adresáře, například:

```text
C:\xampp\htdocs\servisdesk
```

2. Vytvořte databázi:

```sql
CREATE DATABASE servisdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Importujte databázové schéma:

```bash
mysql -u root -p servisdesk < database/schema.sql
```

4. Importujte testovací data:

```bash
mysql -u root -p servisdesk < database/seed.sql
```

5. Vytvořte lokální konfiguraci:

```powershell
copy config\config.example.php config\config.php
```

6. Upravte `config/config.php` podle lokální databáze.

7. Otevřete aplikaci:

```text
http://localhost/servisdesk/public
```

Podrobnosti jsou v [docs/INSTALLATION.md](docs/INSTALLATION.md).

## Spuštění přes PHP server

Pokud máte PHP v PATH:

```bash
php -S localhost:8000 -t public
```

Potom otevřete:

```text
http://localhost:8000
```

## Testovací účty

Všechny testovací účty ze `seed.sql` mají heslo `password`.

| Role | E-mail |
|---|---|
| ADMIN | `admin@servisdesk.local` |
| TECHNICIAN | `technik@servisdesk.local` |
| USER | `student@servisdesk.local` |
| USER | `ucitel@servisdesk.local` |

## Struktura projektu

```text
servisdesk/
├── config/
│   └── config.example.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   ├── ANALYSIS.md
│   ├── API.md
│   ├── COMMISSION_QUESTIONS.md
│   ├── DATABASE.md
│   ├── DEFENSE_GUIDE.md
│   ├── DEMO_SCRIPT.md
│   ├── INSTALLATION.md
│   ├── MATURITA_DOCUMENTATION.md
│   ├── PRESENTATION.md
│   └── TESTING.md
├── public/
│   ├── api/index.php
│   ├── assets/css/styles.css
│   ├── assets/js/api.js
│   ├── assets/js/app.js
│   └── index.html
├── src/
│   ├── Controllers/
│   ├── Core/
│   └── bootstrap.php
├── storage/
├── tests/
│   ├── api_requests.http
│   └── static_check.ps1
├── README.md
└── SECURITY.md
```

## API

API je dostupné přes:

```text
public/api/index.php
```

Příklad:

```text
GET /auth/me
GET /tickets
POST /tickets
PATCH /tickets/{id}
GET /admin/dashboard
```

Kompletní dokumentace je v [docs/API.md](docs/API.md).

## Bezpečnost

Projekt implementuje:

- PDO prepared statements
- `password_hash` a `password_verify`
- session autentizaci
- backendovou autorizaci podle rolí
- kontrolu vlastnictví požadavků
- CSRF token pro měnící požadavky
- escapování výstupu ve frontendu
- ignorování lokální konfigurace v Gitu

Podrobnosti jsou v [SECURITY.md](SECURITY.md).

## Testování

V aktuálním vývojovém prostředí nebylo dostupné PHP ani MySQL/MariaDB, takže nebylo možné spustit plný backendový test. Provedené a připravené testy jsou popsané v [docs/TESTING.md](docs/TESTING.md).

Statickou kontrolu lze spustit:

```powershell
powershell -ExecutionPolicy Bypass -File tests/static_check.ps1
```

## Git a GitHub

Projekt je lokální Git repozitář s průběžnou historií commitů. GitHub CLI nebylo v aktuálním prostředí dostupné, proto nebyl vzdálený GitHub repozitář vytvořen automaticky.

Ruční vytvoření remote po založení GitHub repozitáře:

```bash
git remote add origin https://github.com/UZIVATEL/servisdesk.git
git branch -M main
git push -u origin main
```

## Dokumentace k obhajobě

- [Maturitní dokumentace](docs/MATURITA_DOCUMENTATION.md)
- [Prezentace](docs/PRESENTATION.md)
- [Scénář živé ukázky](docs/DEMO_SCRIPT.md)
- [Otázky komise](docs/COMMISSION_QUESTIONS.md)
- [Průvodce obhajobou kódu](docs/DEFENSE_GUIDE.md)
- [Finální audit](docs/FINAL_AUDIT.md)

## Screenshoty

Screenshoty je vhodné doplnit po prvním lokálním spuštění aplikace v prostředí s PHP a MySQL/MariaDB.
