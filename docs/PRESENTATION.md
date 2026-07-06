# Návrh prezentace na 10 až 15 minut

## Slide 1: ServisDesk

- Název projektu
- Krátká věta: evidence a řešení školních IT požadavků

Poznámky řečníka: Představit, že jde o praktický školní servisní systém.

## Slide 2: Motivace

- Školní IT problémy se často řeší neformálně
- Požadavky se ztrácí nebo nemají jasný stav
- Cílem je zavést přehlednou evidenci

Screenshot: přihlášení nebo seznam požadavků.

## Slide 3: Řešený problém

- kdo požadavek nahlásil
- kdo ho řeší
- jaká je priorita
- v jakém je stavu

Poznámky: Zdůraznit reálnost problému.

## Slide 4: Cílová skupina

- studenti
- učitelé
- IT technici
- administrátor systému

## Slide 5: Hlavní funkce

- přihlášení
- vytváření uživatelů administrátorem
- správa požadavků
- komentáře
- role a oprávnění
- dashboard a auditní log

## Slide 6: Technologie

- HTML, CSS, JavaScript
- PHP 8+
- MySQL/MariaDB
- PDO
- REST API a JSON
- Git

Poznámky: Vysvětlit, proč nebyl použit velký framework.

## Slide 7: Architektura

- frontend v `public`
- API vstup v `public/api/index.php`
- backend logika v `src`
- databáze v MySQL/MariaDB

Doporučený obrázek: jednoduchý diagram frontend -> API -> controller -> PDO -> databáze.

## Slide 8: Databázový návrh

- role
- uživatelé
- kategorie
- požadavky
- komentáře
- historie stavů
- audit log

Screenshot: ER diagram nebo výřez `docs/DATABASE.md`.

## Slide 9: REST API

- jednotný JSON formát
- HTTP metody podle operace
- ukázka endpointu `GET /tickets`
- ukázka endpointu `POST /tickets`

## Slide 10: Autentizace a autorizace

- session
- `password_hash`
- `password_verify`
- role `USER`, `TECHNICIAN`, `ADMIN`
- backendová kontrola oprávnění

## Slide 11: Bezpečnost

- prepared statements
- CSRF token
- XSS escapování
- IDOR ochrana
- necommitovaná lokální konfigurace

## Slide 12: Živá ukázka

- přihlášení
- vytvoření požadavku
- komentář
- změna stavu
- dashboard

## Slide 13: Testování

- statické kontroly
- JavaScript syntax check
- manuální scénáře
- omezení kvůli chybějícímu PHP/MySQL v aktuálním prostředí

## Slide 14: Problémy během vývoje

- návrh oprávnění
- oddělení běžného uživatele a technika
- ochrana proti přístupu k cizím požadavkům
- sjednocení JSON odpovědí

## Slide 15: Budoucí rozvoj a závěr

- přílohy
- e-mailové notifikace
- exporty
- SLA statistiky
- shrnutí přínosu projektu
