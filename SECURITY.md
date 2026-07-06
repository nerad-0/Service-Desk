# Bezpečnost projektu

Tento dokument popisuje bezpečnostní opatření implementovaná v aplikaci ServisDesk. Cílem není nahradit profesionální bezpečnostní audit, ale ukázat rozumnou úroveň ochrany pro maturitní projekt.

## Hlavní rizika

- SQL injection při práci s databází
- XSS při zobrazení uživatelských textů
- CSRF u měnících HTTP požadavků
- IDOR, tedy přístup k cizím požadavkům změnou ID v URL
- slabé ukládání hesel
- nechtěné zveřejnění lokální konfigurace
- zobrazení interních chyb uživateli

## Implementované ochrany

### SQL injection

Backend používá PDO a prepared statements. Hodnoty od uživatele se nevkládají přímo do SQL řetězce.

Příklady jsou v:

- `src/Controllers/AuthController.php`
- `src/Controllers/TicketController.php`
- `src/Controllers/AdminController.php`

### Hesla

Hesla se ukládají pomocí `password_hash` a ověřují přes `password_verify`. Databáze neukládá původní heslo.

### Autentizace

Aplikace používá PHP session. Po přihlášení se regeneruje session ID pomocí `session_regenerate_id(true)`.

### Autorizace

Oprávnění se kontroluje na backendu. Nestačí tedy skrýt tlačítko ve frontendu. Například běžný uživatel vidí pouze vlastní požadavky, zatímco technik a administrátor mohou pracovat se všemi požadavky.

### Ochrana proti IDOR

Před zobrazením nebo úpravou požadavku backend kontroluje, zda je uživatel vlastníkem požadavku, technikem nebo administrátorem. Změna ID v URL sama o sobě nestačí k získání přístupu.

### CSRF

Měnící požadavky používají CSRF token v hlavičce `X-CSRF-Token`. Token se generuje v session a kontroluje se na backendu.

### XSS

Frontend při vkládání dat do HTML používá escapování přes funkci `escapeHtml`. Uživatelský obsah se nevkládá do stránky jako neověřený HTML kód.

### Konfigurace

Soubor `config/config.php` je ignorovaný Gitem. V repozitáři je pouze `config/config.example.php`.

### Chybové zprávy

V produkčním režimu lze v konfiguraci vypnout `debug`. Aplikace pak nevrací detaily výjimek uživateli.

## Příklady útoků a reakce aplikace

- Uživatel změní URL z `/tickets/1` na `/tickets/2`: backend zkontroluje vlastnictví nebo roli.
- Uživatel odešle ručně `PATCH /admin/users/1`: backend vyžaduje roli `ADMIN`.
- Uživatel pošle SQL výraz v e-mailu nebo hledání: hodnota jde do prepared statementu.
- Útočník vloží `<script>` do komentáře: frontend text escapuje.
- Cizí web odešle POST požadavek: chybí platný CSRF token.

## Známá omezení

- Projekt zatím neobsahuje rate limiting proti opakovaným pokusům o přihlášení.
- Projekt zatím neposílá e-mailové ověřování účtu.
- Přílohy k požadavkům nejsou implementované, takže se neřeší antivirová kontrola uploadů.
- Na produkčním hostingu je potřeba zapnout HTTPS a nastavit `debug` na `false`.

