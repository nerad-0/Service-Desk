# Maturitní dokumentace projektu ServisDesk

## 1. Úvod

Tato práce popisuje návrh a implementaci webové aplikace ServisDesk. Aplikace slouží k evidenci školních IT požadavků a ukazuje kompletní vývoj od analýzy přes databázi, backend, REST API, frontend až po testování a bezpečnost.

## 2. Cíl práce

Cílem je vytvořit funkční webovou aplikaci, kterou lze spustit lokálně v prostředí PHP a MySQL/MariaDB. Projekt má obsahovat uživatelské účty, role, CRUD operace, administraci, dashboard, auditní log a dokumentované REST API.

## 3. Motivace

Ve školním prostředí často vznikají technické požadavky, které se řeší neformálně přes zprávy, e-maily nebo osobně. Takový postup komplikuje sledování stavu, priorit a odpovědnosti. ServisDesk tyto požadavky centralizuje.

## 4. Analýza problému

Hlavním problémem je chybějící evidence technických závad. Bez systému není jasné, kdo problém nahlásil, kdo ho řeší, jaká je priorita a zda už byl vyřešen. Aplikace proto používá stavový model požadavku, komentáře a historii změn.

## 5. Analýza požadavků

Požadavky jsou rozděleny na funkční a nefunkční. Funkční požadavky popisují, co aplikace dělá. Nefunkční požadavky popisují kvalitu řešení, například bezpečnost, responzivitu a jednoduchou instalaci.

## 6. Funkční požadavky

- registrace a přihlášení
- role `USER`, `TECHNICIAN`, `ADMIN`
- vytváření a správa požadavků
- komentáře a interní poznámky
- vyhledávání, filtrování, řazení a stránkování
- administrace uživatelů a kategorií
- dashboard se statistikami a grafem
- auditní log důležitých akcí

## 7. Nefunkční požadavky

- lokální běh v XAMPP nebo Laragonu
- PHP 8+ a MySQL/MariaDB
- PDO prepared statements
- responzivní rozhraní
- čitelná struktura projektu
- bezpečná práce s hesly a konfigurací

## 8. Použité technologie

- HTML5 pro strukturu stránek
- CSS3 pro responzivní vzhled
- JavaScript a Fetch API pro komunikaci s backendem
- PHP 8+ pro backend
- MySQL/MariaDB pro relační databázi
- PDO pro databázovou komunikaci
- Git pro verzování

## 9. Zdůvodnění výběru technologií

Technologie byly zvoleny tak, aby byly dostupné, srozumitelné a vhodné pro maturitní projekt. PHP a MySQL lze snadno spustit přes XAMPP. Nepoužití velkého frameworku pomáhá lépe vysvětlit tok požadavku, routing, session a práci s databází.

## 10. Návrh aplikace

Aplikace je rozdělena na veřejnou část v `public`, backendovou logiku v `src`, konfiguraci v `config`, SQL soubory v `database` a dokumentaci v `docs`.

## 11. Architektura

Architektura je jednoduchá vrstvená:

- frontend volá REST API
- `public/api/index.php` je vstupní bod API
- router předává požadavek controlleru
- controllery validují vstupy a kontrolují oprávnění
- databáze se používá přes PDO

## 12. Návrh databáze

Databáze obsahuje tabulky `roles`, `users`, `categories`, `tickets`, `ticket_comments`, `ticket_status_history` a `audit_logs`.

## 13. Popis databázových vztahů

Role má více uživatelů, uživatel má více požadavků, kategorie má více požadavků a požadavek má více komentářů i záznamů historie. Vztahy jsou zajištěné cizími klíči.

## 14. Návrh REST API

API používá HTTP metody podle významu operace. `GET` čte data, `POST` vytváří, `PATCH` upravuje a `DELETE` odstraňuje. Odpovědi jsou ve formátu JSON.

## 15. Implementace backendu

Backend je v PHP. Hlavní části jsou router, request/response helpery, databázová vrstva, autentizace, CSRF ochrana a controllery pro autentizaci, požadavky a administraci.

## 16. Implementace frontendu

Frontend je vytvořen v HTML, CSS a JavaScriptu. JavaScript komunikuje s API přes Fetch API, pracuje se stavem přihlášeného uživatele, zobrazuje formuláře, seznamy, detail požadavku a dashboard.

Vizuální návrh je popsán v `docs/DESIGN_BRIEF.md`. Rozhraní je záměrně kompaktní a pracovní, protože ServisDesk je servisní evidence, ne marketingová stránka.

## 17. Autentizace

Autentizace používá PHP session. Po přihlášení se uloží ID uživatele do session a regeneruje se session ID.

## 18. Autorizace

Autorizace je kontrolovaná na backendu. Role určují, jaká data smí uživatel zobrazit nebo upravit. Běžný uživatel nesmí upravovat cizí požadavky.

## 19. Bezpečnost

Projekt používá prepared statements, `password_hash`, `password_verify`, CSRF token, escapování výstupu a kontrolu vlastnictví záznamů. Podrobnosti jsou v `SECURITY.md`.

## 20. Testování

Testování je popsáno v `docs/TESTING.md`. V aktuálním prostředí byly provedeny statické kontroly a JS syntax check. Funkční testy vyžadují PHP/MySQL prostředí.

## 21. Instalace

Instalace je popsaná v `docs/INSTALLATION.md`. Zahrnuje vytvoření databáze, import `schema.sql`, import `seed.sql` a vytvoření lokální konfigurace.

## 22. Uživatelská příručka

Uživatel se přihlásí, založí nový požadavek, sleduje jeho stav a přidává komentáře. Vlastní požadavky vidí v seznamu a může je filtrovat.

## 23. Administrátorská příručka

Administrátor spravuje uživatele, role, kategorie a sleduje auditní log. Technik má přístup k dashboardu a může řešit požadavky.

## 24. Známá omezení

- chybí e-mailové notifikace
- chybí přílohy
- chybí rate limiting přihlášení
- automatické PHP testy nebyly spuštěny kvůli chybějícímu PHP runtime

## 25. Možnosti budoucího rozšíření

- upload příloh
- e-mailové notifikace
- export CSV
- SLA metriky
- pokročilé vyhledávání
- nasazení na hosting s HTTPS

## 26. Závěr

ServisDesk splňuje hlavní cíle maturitního projektu. Obsahuje frontend, backend, relační databázi, REST API, role, CRUD operace, administraci, bezpečnostní prvky, testovací podklady a dokumentaci pro obhajobu.
