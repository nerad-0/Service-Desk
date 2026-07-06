# Scénář živé ukázky

## Příprava před komisí

1. Spustit Apache a MySQL/MariaDB.
2. Ověřit, že existuje databáze `servisdesk`.
3. Ověřit import `database/schema.sql` a `database/seed.sql`.
4. Ověřit `config/config.php`.
5. Otevřít aplikaci v prohlížeči.

## Doporučený scénář

### 1. Otevření aplikace

Ukázat přihlašovací obrazovku a stručně říct, že aplikace používá frontend v HTML/CSS/JS a backend v PHP.

### 2. Přihlášení jako běžný uživatel

Použít:

- e-mail: `student@servisdesk.local`
- heslo: `password`

Ukázat, že běžný uživatel vidí vlastní požadavky.

### 3. Vytvoření požadavku

Vytvořit požadavek například:

- název: `Nefunguje Wi-Fi v učebně 312`
- kategorie: `Síť a internet`
- priorita: `Vysoká`
- popis: `Při připojení celé třídy často vypadává internet.`

Ukázat, že se požadavek objeví v seznamu.

### 4. Úprava a komentář

Otevřít detail, přidat komentář a upravit prioritu nebo popis.

### 5. Vyhledávání a filtrování

Použít vyhledávání podle slova `Wi-Fi` a filtr stavu.

### 6. Odhlášení a přihlášení jako administrátor

Použít:

- e-mail: `admin@servisdesk.local`
- heslo: `password`

Ukázat, že administrátor vidí administraci.

### 7. Dashboard

Otevřít administraci a ukázat:

- souhrnné statistiky
- graf podle stavů
- poslední požadavky

Zdůraznit, že graf používá data z endpointu `/admin/dashboard`, ne náhodná čísla.

### 8. Správa uživatelů

Ukázat změnu role nebo aktivace uživatele. Není nutné změnu ukládat, pokud nechcete ovlivnit připravená data.

### 9. Správa kategorií

Ukázat existující kategorie a případně přidat novou testovací kategorii.

### 10. API nebo databáze

Krátce ukázat:

- `docs/API.md`
- `database/schema.sql`
- případně REST požadavek z `tests/api_requests.http`

## Záložní plán

### Nefunguje internet

Projekt nepotřebuje internet. Ukázku lze provést lokálně přes XAMPP nebo Laragon.

### Není dostupný GitHub

Ukázat lokální Git historii:

```bash
git log --oneline
```

### Nejde spustit hosting

Použít lokální spuštění:

```bash
php -S localhost:8000 -t public
```

### Nejde spustit PHP/MySQL

Ukázat připravené soubory:

- `README.md`
- `database/schema.sql`
- `docs/API.md`
- `docs/DEFENSE_GUIDE.md`
- screenshoty, pokud byly předem pořízeny

## Časový plán ukázky

- přihlášení a vytvoření požadavku: 3 minuty
- práce s požadavkem: 3 minuty
- administrace a dashboard: 4 minuty
- API/databáze/kód: 3 minuty
- závěr: 1 minuta

