# Testování

## Stav testovacího prostředí

V aktuálním prostředí byly nalezeny tyto nástroje:

- Git: dostupný
- Node.js: dostupný
- PHP: nedostupné v PATH
- MySQL/MariaDB klient: nedostupný v PATH
- GitHub CLI: nedostupné v PATH

Kvůli chybějícímu PHP a MySQL nebylo možné lokálně spustit backend ani reálně importovat databázi. Byly provedeny dostupné statické kontroly a připraveny manuální testovací scénáře pro spuštění v XAMPP/Laragon prostředí.

## Automaticky provedené kontroly

| Test | Účel | Výsledek |
|---|---|---|
| `node --check public/assets/js/api.js` | Kontrola syntaxe API klienta | Prošlo |
| `node --check public/assets/js/app.js` | Kontrola syntaxe hlavního JS | Prošlo |
| `git diff --check` | Kontrola whitespace problémů | Prošlo |
| `rg` kontrola endpointů a helperů | Ověření přítomnosti klíčových souborů | Prošlo |

## Připravený automatizovaný test

Soubor `tests/static_check.ps1` kontroluje existenci důležitých souborů, nepřítomnost lokální konfigurace v repozitáři, výskyt prepared statements a syntaxi JavaScriptu.

Spuštění:

```powershell
powershell -ExecutionPolicy Bypass -File tests/static_check.ps1
```

## Manuální testovací scénáře

### 1. Registrace

- Účel: ověřit vytvoření nového účtu
- Vstupní podmínky: běžící aplikace a prázdný nebo seedovaný systém
- Postup: otevřít aplikaci, vyplnit registrační formulář, odeslat
- Očekávaný výsledek: uživatel je přihlášen a vidí seznam požadavků
- Skutečný výsledek: vyžaduje ověření po instalaci PHP/MySQL

### 2. Přihlášení

- Účel: ověřit session autentizaci
- Vstupní podmínky: importovaný `seed.sql`
- Postup: přihlásit se jako `admin@servisdesk.local` s heslem `password`
- Očekávaný výsledek: zobrazí se navigace, role administrátora a administrace
- Skutečný výsledek: vyžaduje ověření po instalaci PHP/MySQL

### 3. Nesprávné heslo

- Postup: použít existující e-mail a špatné heslo
- Očekávaný výsledek: API vrátí `401` a frontend zobrazí chybu

### 4. Duplicitní registrace

- Postup: registrovat e-mail, který už existuje
- Očekávaný výsledek: API vrátí `409`

### 5. Odhlášení

- Postup: přihlásit se, kliknout na odhlášení
- Očekávaný výsledek: session je zrušena a zobrazí se přihlášení

### 6. Přístup nepřihlášeného uživatele

- Postup: zavolat `GET /tickets` bez session
- Očekávaný výsledek: API vrátí `401`

### 7. Oprávnění běžného uživatele

- Postup: přihlásit se jako `student@servisdesk.local`, otevřít seznam požadavků
- Očekávaný výsledek: uživatel vidí jen vlastní požadavky

### 8. Oprávnění administrátora

- Postup: přihlásit se jako admin a otevřít administraci
- Očekávaný výsledek: admin vidí dashboard, uživatele, kategorie a audit log

### 9. Vytvoření požadavku

- Postup: vyplnit formulář nového požadavku
- Očekávaný výsledek: požadavek vznikne se stavem `new`

### 10. Načtení detailu požadavku

- Postup: kliknout na řádek požadavku
- Očekávaný výsledek: zobrazí se detail, komentáře a podle role také historie

### 11. Úprava požadavku

- Postup: upravit název nebo prioritu
- Očekávaný výsledek: změna se uloží a projeví v seznamu

### 12. Odstranění požadavku

- Postup: jako admin odstranit požadavek
- Očekávaný výsledek: požadavek zmizí ze seznamu

### 13. Neplatné ID

- Postup: zavolat `GET /tickets/999999`
- Očekávaný výsledek: API vrátí `404`

### 14. Prázdné vstupy

- Postup: odeslat formulář bez povinných polí
- Očekávaný výsledek: frontend nebo API odmítne požadavek

### 15. Příliš dlouhé vstupy

- Postup: odeslat název delší než 160 znaků
- Očekávaný výsledek: API vrátí `422`

### 16. Neplatné datové typy

- Postup: poslat text místo `category_id`
- Očekávaný výsledek: API vrátí validační chybu

### 17. Vyhledávání

- Postup: zadat hledaný výraz do filtru
- Očekávaný výsledek: seznam se zúží podle názvu nebo popisu

### 18. Filtrování

- Postup: vybrat stav nebo prioritu
- Očekávaný výsledek: seznam zobrazí pouze odpovídající požadavky

### 19. Řazení

- Postup: změnit řazení v seznamu
- Očekávaný výsledek: API vrátí data v požadovaném pořadí

### 20. Stránkování

- Postup: vytvořit více než 10 požadavků a přejít na další stránku
- Očekávaný výsledek: zobrazí se další sada záznamů

### 21. Neoprávněný přístup k cizímu požadavku

- Postup: jako běžný uživatel zavolat detail cizího požadavku
- Očekávaný výsledek: API vrátí `403`

### 22. CSRF ochrana

- Postup: poslat `POST /tickets` bez hlavičky `X-CSRF-Token`
- Očekávaný výsledek: API vrátí `403`

### 23. Dashboard a graf

- Postup: přihlásit se jako technik nebo admin a otevřít administraci
- Očekávaný výsledek: graf vychází z dat endpointu `/admin/dashboard`

### 24. Auditní log

- Postup: provést změnu požadavku a otevřít audit log jako admin
- Očekávaný výsledek: změna je dohledatelná v auditním logu

