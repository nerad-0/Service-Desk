# Průvodce obhajobou kódu

Tento dokument vysvětluje části projektu, kterým musí student rozumět při obhajobě.

## Tok HTTP požadavku aplikací

1. Prohlížeč odešle požadavek přes Fetch API.
2. Požadavek dorazí do `public/api/index.php`.
3. `Request::fromGlobals()` vytvoří objekt požadavku.
4. `Router` najde odpovídající endpoint.
5. Controller zkontroluje vstupy, přihlášení a oprávnění.
6. Controller použije PDO dotazy.
7. `Response::success` nebo `Response::error` vrátí JSON.

Hlavní soubory:

- `public/api/index.php`
- `src/Core/Request.php`
- `src/Core/Router.php`
- `src/Core/Response.php`

## Komunikace frontend a API

Frontend komunikuje s backendem v `public/assets/js/api.js`. Třída `ApiClient` používá `fetch`, posílá JSON a očekává JSON odpověď.

## Fetch API

Fetch API je moderní způsob posílání HTTP požadavků v JavaScriptu. V projektu je použité například při přihlášení, vytvoření požadavku nebo načtení dashboardu.

## JSON

JSON je formát pro přenos dat mezi frontendem a backendem. Backend čte JSON v `Request::json()` a odpovídá přes `Response`.

## PDO

PDO je databázová vrstva v PHP. Připojení je v `src/Core/Database.php`. Výhoda PDO je jednotný způsob práce s databází a prepared statements.

## Prepared statements

Prepared statements oddělují SQL dotaz od hodnot uživatele. Tím chrání proti SQL injection. Příklad je v `TicketController::create`.

## Session

Session uchovává stav přihlášeného uživatele. Po přihlášení se do `$_SESSION['user_id']` uloží ID uživatele. Logika je v `src/Core/Auth.php`.

## password_hash a password_verify

`password_hash` bezpečně uloží heslo jako hash. `password_verify` ověří zadané heslo proti hashi. Použité jsou v `src/Controllers/AuthController.php`.

## Role a oprávnění

Role jsou uložené v tabulce `roles`. Backend pracuje s rolemi `USER`, `TECHNICIAN` a `ADMIN`. Kontrola oprávnění probíhá v `Auth::requireAnyRole` a v controllerech.

## CRUD

CRUD znamená Create, Read, Update, Delete. V projektu:

- Create: `POST /tickets`
- Read: `GET /tickets`, `GET /tickets/{id}`
- Update: `PATCH /tickets/{id}`
- Delete: `DELETE /tickets/{id}`

## SQL JOIN

JOIN spojuje data z více tabulek. Například seznam požadavků spojuje `tickets`, `categories`, `users` a volitelně přiřazeného technika.

## Cizí klíče

Cizí klíče v `database/schema.sql` hlídají, aby požadavek nemohl odkazovat na neexistujícího uživatele nebo kategorii.

## Indexy

Indexy zrychlují časté dotazy. Projekt používá indexy například nad stavem požadavku, prioritou, autorem a datem vytvoření.

## Validace

Validace probíhá na backendu ve třídě `Validator` a v controllerech. Frontend má také HTML validaci, ale rozhodující je backend.

## XSS

XSS je vložení škodlivého JavaScriptu do stránky. Frontend používá `escapeHtml` v `public/assets/js/app.js`, aby uživatelský text nezpracoval jako HTML.

## CSRF

CSRF znamená zneužití přihlášené session z cizí stránky. Projekt používá CSRF token ve třídě `src/Core/Csrf.php`.

## SQL injection

SQL injection je vložení SQL kódu do vstupu. Projekt používá PDO prepared statements, takže vstupy nejsou spojované přímo s SQL dotazem.

## IDOR

IDOR je neoprávněný přístup změnou ID. V projektu kontroluje `TicketController::assertCanView`, zda uživatel smí požadavek zobrazit.

## Git commit

Commit je uložený bod historie projektu. Tento projekt má více commitů podle etap vývoje.

## Branch

Branch je samostatná vývojová větev. Projekt zatím používá hlavní větev, protože vývoj probíhal postupně v jednom lokálním repozitáři.

## Remote

Remote je vzdálený repozitář, typicky GitHub. V tomto prostředí není dostupný GitHub CLI, takže remote nebyl automaticky vytvořen.

## Push a pull

`git push` nahraje commity do vzdáleného repozitáře. `git pull` stáhne nové změny. Pro push je potřeba mít vytvořený GitHub repozitář a nastavený remote.

