# Otázky komise a doporučené odpovědi

## Obecný návrh aplikace

### 1. Jaký problém aplikace řeší?

Krátce: Eviduje a pomáhá řešit školní IT požadavky.

Podrobněji: Uživatelé mohou založit požadavek, technici ho řeší a administrátor sleduje stav systému. Díky tomu je jasné, kdo problém nahlásil, kdo ho řeší a v jakém je stavu.

V projektu: `docs/ANALYSIS.md`, tabulka `tickets`.

### 2. Proč jste zvolil jednoduchou architekturu bez frameworku?

Krátce: Je snazší ji vysvětlit a stačí rozsahu projektu.

Podrobněji: Projekt má ukázat principy HTTP požadavku, routingu, session, PDO a validace. Velký framework by část těchto principů schoval.

V projektu: `public/api/index.php`, `src/Core/Router.php`.

### 3. Jaké jsou hlavní role v aplikaci?

Krátce: `USER`, `TECHNICIAN`, `ADMIN`.

Podrobněji: Běžný uživatel vytváří vlastní požadavky, technik řeší požadavky a administrátor spravuje uživatele, kategorie a auditní log.

V projektu: tabulka `roles`, `src/Core/Auth.php`.

## HTML

### 4. K čemu slouží HTML v projektu?

Krátce: Určuje strukturu uživatelského rozhraní.

Podrobněji: HTML definuje formuláře, navigaci, tabulky, dashboard, canvas graf a sekce aplikace.

V projektu: `public/index.html`.

### 5. Jak HTML formuláře pomáhají validaci?

Krátce: Používají atributy jako `required`, `minlength` a `maxlength`.

Podrobněji: Frontendová validace zlepšuje pohodlí uživatele, ale rozhodující validace probíhá na backendu.

V projektu: formuláře v `public/index.html`, validace v controllerech.

## CSS

### 6. Jak je řešena responzivita?

Krátce: Pomocí CSS gridu, flexibilních rozměrů a media queries.

Podrobněji: Layout se na menších obrazovkách přepne z více sloupců do jednoho sloupce, aby se tabulky a formuláře vešly na mobil.

V projektu: `public/assets/css/styles.css`.

### 7. Proč používáte CSS proměnné?

Krátce: Udržují jednotný vzhled.

Podrobněji: Barvy, radiusy a stíny jsou definované v `:root`, takže se dají snadno měnit a zůstávají konzistentní.

V projektu: začátek `styles.css`.

## JavaScript

### 8. K čemu slouží JavaScript?

Krátce: Komunikuje s API a aktualizuje rozhraní bez reloadu.

Podrobněji: JavaScript načítá session, požadavky, dashboard, odesílá formuláře a vykresluje data z API.

V projektu: `public/assets/js/app.js`.

### 9. Jak funguje Fetch API?

Krátce: Posílá HTTP požadavky z prohlížeče.

Podrobněji: V projektu třída `ApiClient` posílá JSON, nastavuje hlavičky a zpracovává JSON odpovědi.

V projektu: `public/assets/js/api.js`.

### 10. Jak bráníte XSS ve frontendu?

Krátce: Escapováním textu před vložením do HTML.

Podrobněji: Funkce `escapeHtml` převádí znaky jako `<` a `>` na bezpečné HTML entity.

V projektu: `escapeHtml` v `app.js`.

## PHP

### 11. Jaký je vstupní bod backendu?

Krátce: `public/api/index.php`.

Podrobněji: Tento soubor načte konfiguraci, vytvoří databázové připojení, controllery, zaregistruje routy a předá požadavek routeru.

V projektu: `public/api/index.php`.

### 12. Jak je řešen autoloading tříd?

Krátce: Vlastním jednoduchým autoloaderem.

Podrobněji: `src/bootstrap.php` převádí namespace `App\...` na cesty ve složce `src`.

V projektu: `src/bootstrap.php`.

### 13. Jak backend vrací odpovědi?

Krátce: Přes helper `Response`.

Podrobněji: `Response::success` a `Response::error` sjednocují JSON formát a nastavují HTTP status.

V projektu: `src/Core/Response.php`.

## REST API, HTTP a JSON

### 14. Co znamená REST API?

Krátce: API postavené kolem zdrojů a HTTP metod.

Podrobněji: Požadavky jsou zdroj, `GET` čte, `POST` vytváří, `PATCH` upravuje a `DELETE` odstraňuje.

V projektu: `docs/API.md`.

### 15. Proč API vrací JSON?

Krátce: JSON je jednoduchý formát pro komunikaci frontend-backend.

Podrobněji: JavaScript ho umí snadno číst i vytvářet a PHP ho umí dekódovat přes `json_decode`.

V projektu: `Request::json`, `Response`.

### 16. Jaké HTTP status kódy používáte?

Krátce: Například `200`, `201`, `401`, `403`, `404`, `422`.

Podrobněji: Status kód vyjadřuje výsledek požadavku. Například `401` znamená nepřihlášený uživatel a `403` nedostatečné oprávnění.

V projektu: `HttpException`, `docs/API.md`.

### 17. Proč používáte PATCH pro úpravy?

Krátce: Protože se často upravuje jen část záznamu.

Podrobněji: U požadavku může technik změnit jen stav nebo prioritu, aniž by posílal celý objekt.

V projektu: `PATCH /tickets/{id}`.

## Databáze a SQL

### 18. Proč je databáze relační?

Krátce: Data mají jasné vztahy.

Podrobněji: Uživatelé, požadavky, kategorie a komentáře spolu souvisí přes primární a cizí klíče.

V projektu: `database/schema.sql`.

### 19. Co je primární klíč?

Krátce: Jednoznačný identifikátor záznamu.

Podrobněji: V projektu má každá tabulka sloupec `id`, který jednoznačně identifikuje řádek.

V projektu: všechny tabulky v `schema.sql`.

### 20. Co je cizí klíč?

Krátce: Odkaz na záznam v jiné tabulce.

Podrobněji: Například `tickets.author_id` odkazuje na autora v tabulce `users`.

V projektu: `fk_tickets_author`.

### 21. Kde používáte vztah 1:N?

Krátce: Jeden uživatel může mít více požadavků.

Podrobněji: Také jedna kategorie může patřit více požadavkům a jeden požadavek může mít více komentářů.

V projektu: `users` -> `tickets`, `tickets` -> `ticket_comments`.

### 22. Proč používáte indexy?

Krátce: Zrychlují časté dotazy.

Podrobněji: Indexy jsou nad stavem, prioritou, autorem nebo datem, protože podle nich aplikace často filtruje a řadí.

V projektu: indexy v `database/schema.sql`.

### 23. Co je SQL JOIN?

Krátce: Spojení dat z více tabulek.

Podrobněji: Seznam požadavků spojuje tabulku požadavků s kategorií, autorem a přiřazeným technikem.

V projektu: `TicketController::index`.

## PDO

### 24. Proč používáte PDO?

Krátce: Pro bezpečnou a jednotnou práci s databází.

Podrobněji: PDO podporuje prepared statements a umí pracovat s různými databázemi přes podobné API.

V projektu: `src/Core/Database.php`.

### 25. Co jsou prepared statements?

Krátce: Předpřipravené SQL dotazy s parametry.

Podrobněji: Dotaz a data uživatele se neposílají jako jeden slepený řetězec, což chrání proti SQL injection.

V projektu: `prepare` a `execute` v controllerech.

## Autentizace a autorizace

### 26. Jak funguje přihlášení?

Krátce: Ověří se e-mail a heslo, potom se uloží ID do session.

Podrobněji: Po úspěšném `password_verify` se zavolá `Auth::login`, která regeneruje session ID.

V projektu: `AuthController::login`, `Auth::login`.

### 27. Proč se hesla neukládají jako text?

Krátce: Kvůli bezpečnosti.

Podrobněji: Pokud by unikla databáze, útočník by neměl původní hesla, ale jen hashe.

V projektu: `password_hash`.

### 28. Jaký je rozdíl mezi autentizací a autorizací?

Krátce: Autentizace říká kdo jsem, autorizace co smím.

Podrobněji: Přihlášení ověří uživatele. Role a vlastnictví záznamů rozhodují, zda smí provést akci.

V projektu: `Auth.php`, `TicketController::assertCanView`.

### 29. Proč nestačí skrýt tlačítko ve frontendu?

Krátce: Uživatel může API zavolat ručně.

Podrobněji: Bez backendové kontroly by šlo obejít UI změnou URL nebo vlastním HTTP požadavkem.

V projektu: `requireAnyRole`, `assertCanView`.

## Bezpečnost

### 30. Co je SQL injection?

Krátce: Vložení škodlivého SQL do vstupu.

Podrobněji: Prepared statements chrání tím, že vstup bere jako hodnotu, ne jako část SQL příkazu.

V projektu: PDO dotazy v controllerech.

### 31. Co je XSS?

Krátce: Vložení skriptu do stránky.

Podrobněji: Útočník by mohl vložit `<script>` do komentáře. Escapování zabrání tomu, aby se text spustil jako kód.

V projektu: `escapeHtml`.

### 32. Co je CSRF?

Krátce: Zneužití přihlášené session z cizí stránky.

Podrobněji: Projekt vyžaduje CSRF token v hlavičce u měnících požadavků.

V projektu: `src/Core/Csrf.php`.

### 33. Co je IDOR?

Krátce: Přístup k cizím datům změnou ID.

Podrobněji: Pokud uživatel změní ID požadavku v URL, backend musí ověřit, zda daný požadavek patří jemu nebo má vyšší roli.

V projektu: `TicketController::assertCanView`.

### 34. Jak chráníte konfiguraci?

Krátce: Skutečná konfigurace je ignorovaná Gitem.

Podrobněji: V repozitáři je jen `config.example.php`, zatímco `config.php` je v `.gitignore`.

V projektu: `.gitignore`, `config/config.example.php`.

## Git a GitHub

### 35. K čemu slouží Git?

Krátce: K verzování zdrojového kódu.

Podrobněji: Git umožňuje sledovat historii změn, vracet se k předchozím verzím a připravit projekt pro GitHub.

V projektu: lokální Git repozitář a průběžné commity.

### 36. Co je commit?

Krátce: Uložený bod historie.

Podrobněji: Commit obsahuje konkrétní změny a zprávu, která popisuje, co bylo uděláno.

V projektu: `git log --oneline`.

### 37. Proč není automaticky vytvořen GitHub repozitář?

Krátce: GitHub CLI není v prostředí dostupné.

Podrobněji: Projekt je připraven jako lokální Git repozitář. Remote lze doplnit ručně po vytvoření GitHub repozitáře.

V projektu: finální audit a README.

## Testování a nasazení

### 38. Jaké testy byly provedeny?

Krátce: Statické kontroly a JavaScript syntax check.

Podrobněji: Funkční PHP/MySQL testy vyžadují prostředí s PHP a databází, které v aktuálním workspace nebylo dostupné.

V projektu: `docs/TESTING.md`, `tests/static_check.ps1`.

### 39. Jak se aplikace nasadí lokálně?

Krátce: Přes XAMPP nebo Laragon.

Podrobněji: Je potřeba zkopírovat projekt, vytvořit databázi, importovat SQL soubory, vytvořit `config.php` a otevřít `public`.

V projektu: `docs/INSTALLATION.md`.

### 40. Co byste doplnil v další verzi?

Krátce: Přílohy, e-mailové notifikace a rate limiting.

Podrobněji: Tyto funkce dávají smysl, ale nebyly nutné pro první obhajitelnou verzi. Přílohy by vyžadovaly další bezpečnostní kontroly uploadů.

V projektu: `SECURITY.md`, `MATURITA_DOCUMENTATION.md`.

