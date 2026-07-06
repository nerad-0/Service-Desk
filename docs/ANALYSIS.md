# Analýza projektu ServisDesk

## Název projektu

ServisDesk

## Stručný popis

ServisDesk je webová aplikace pro správu školních IT požadavků. Uživatel vytvoří požadavek, popíše problém, nastaví kategorii a prioritu. Technik požadavek zpracuje, komunikuje v komentářích a mění jeho stav. Administrátor spravuje uživatele, kategorie, role a sleduje statistiky.

## Hlavní cíl

Cílem je vytvořit funkční webovou aplikaci, která ukáže kompletní cestu od návrhu relační databáze přes REST API až po responzivní frontend. Projekt má být srozumitelný pro studenta střední IT školy a zároveň dostatečně kvalitní pro maturitní obhajobu.

## Řešený problém

Ve škole často vznikají technické požadavky: nefunkční projektor, problém s přihlášením, chybějící software nebo porucha tiskárny. Bez evidence se požadavky ztrácí, nejsou jasné priority a vedení nemá přehled, co se řešilo. ServisDesk dává požadavkům jednotné místo, stav, historii a odpovědnou osobu.

## Cílová skupina

- studenti a zaměstnanci školy, kteří hlásí IT problémy
- školní IT technici, kteří požadavky řeší
- administrátor nebo správce systému, který sleduje provoz aplikace

## Funkční požadavky

- registrace, přihlášení a odhlášení uživatele
- role `USER`, `TECHNICIAN` a `ADMIN`
- vytvoření, zobrazení, úprava a uzavření požadavku
- komentáře k požadavkům
- změna stavu požadavku
- vyhledávání, filtrování, řazení a stránkování požadavků
- správa profilu
- administrace uživatelů a kategorií
- dashboard se statistikami a grafem z databázových dat
- auditní log důležitých akcí
- dokumentované REST API ve formátu JSON

## Nefunkční požadavky

- aplikace musí běžet lokálně v prostředí XAMPP, Laragon nebo podobném PHP/MySQL prostředí
- backend musí používat PDO a prepared statements
- výstup API musí být konzistentní JSON
- aplikace musí být responzivní pro desktop i mobil
- citlivá konfigurace nesmí být commitnutá do Git repozitáře
- chyby v produkčním režimu nesmí zobrazovat hesla, SQL dotazy ani interní cesty serveru

## Uživatelské role a oprávnění

### USER

- registrace a přihlášení
- vytvoření vlastního požadavku
- zobrazení vlastních požadavků
- úprava vlastního otevřeného požadavku
- přidání komentáře k vlastnímu požadavku
- úprava vlastního profilu

### TECHNICIAN

- vše jako `USER`
- zobrazení všech požadavků
- změna stavu a priority požadavku
- přiřazení požadavku sobě
- komentování všech požadavků
- zobrazení dashboardu

### ADMIN

- vše jako `TECHNICIAN`
- správa uživatelů
- změna rolí uživatelů
- deaktivace uživatelů
- správa kategorií
- přístup k auditnímu logu

## Hlavní uživatelské scénáře

1. Uživatel se zaregistruje a přihlásí.
2. Uživatel vytvoří požadavek s kategorií, prioritou a popisem.
3. Technik vidí nový požadavek v seznamu, přiřadí ho sobě a odpoví komentářem.
4. Technik změní stav na `in_progress` a později na `resolved`.
5. Uživatel zkontroluje řešení a požadavek uzavře.
6. Administrátor sleduje dashboard, spravuje kategorie a uživatele.

## Hlavní stránky aplikace

- přihlášení
- registrace
- seznam požadavků
- detail požadavku
- vytvoření požadavku
- profil uživatele
- administrace uživatelů
- administrace kategorií
- dashboard

## Hlavní API operace

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`
- `GET /tickets`
- `POST /tickets`
- `GET /tickets/{id}`
- `PUT /tickets/{id}`
- `DELETE /tickets/{id}`
- `POST /tickets/{id}/comments`
- `GET /admin/users`
- `PATCH /admin/users/{id}`
- `GET /admin/categories`
- `POST /admin/categories`
- `PATCH /admin/categories/{id}`
- `GET /admin/dashboard`

## Základní bezpečnostní požadavky

- hesla ukládat pouze pomocí `password_hash`
- hesla ověřovat pomocí `password_verify`
- SQL dotazy provádět přes PDO prepared statements
- kontrolovat roli a vlastnictví záznamu na backendu
- u JSON požadavků kontrolovat CSRF token pro měnící operace
- escapovat uživatelský obsah při zobrazení ve frontendu
- vracet bezpečné chybové zprávy bez interních detailů

## Rozdělení funkcí

### MVP

- databázové schéma a testovací data
- registrace, přihlášení, odhlášení
- role `USER` a `ADMIN`
- vytvoření a zobrazení požadavků
- komentáře k požadavkům
- základní administrace

### STANDARD

- role `TECHNICIAN`
- vyhledávání, filtrování, řazení a stránkování
- dashboard se statistikami a grafem
- auditní log
- správa kategorií
- CSRF ochrana
- kompletní API dokumentace
- testovací plán a obhajobové podklady

### BONUS

- přílohy k požadavkům
- e-mailové notifikace
- export požadavků do CSV
- tmavý režim
- pokročilé SLA metriky

