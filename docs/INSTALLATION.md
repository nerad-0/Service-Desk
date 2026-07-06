# Instalace

## Požadavky

- PHP 8.0 nebo novější
- MySQL 8 nebo MariaDB 10.4+
- web server s podporou PHP, například Apache v XAMPP nebo Laragon
- Git pro verzování

## Lokální instalace přes XAMPP

1. Zkopírujte složku projektu do `htdocs`, například jako `C:\xampp\htdocs\servisdesk`.
2. Spusťte Apache a MySQL v XAMPP Control Panelu.
3. V MySQL vytvořte databázi:

```sql
CREATE DATABASE servisdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Importujte databázové schéma:

```bash
mysql -u root -p servisdesk < database/schema.sql
```

5. Importujte testovací data:

```bash
mysql -u root -p servisdesk < database/seed.sql
```

6. Vytvořte lokální konfiguraci:

```bash
copy config\config.example.php config\config.php
```

7. Upravte `config/config.php` podle vašeho MySQL nastavení.
8. Otevřete aplikaci:

```text
http://localhost/servisdesk/public
```

## Spuštění přes vestavěný PHP server

Tento způsob je vhodný pro vývoj:

```bash
php -S localhost:8000 -t public
```

Potom otevřete:

```text
http://localhost:8000
```

## Důležité

Soubor `config/config.php` obsahuje lokální přihlašovací údaje k databázi a není součástí Git repozitáře. Do repozitáře patří pouze `config/config.example.php`.

