# Návrh databáze

## Přehled entit

Databáze je navržena jako relační model pro jednoduchý servisní systém.

- `roles` uchovává dostupné role aplikace.
- `users` uchovává uživatelské účty a odkazuje na roli.
- `categories` rozděluje požadavky podle oblasti problému.
- `tickets` je hlavní tabulka servisních požadavků.
- `ticket_comments` uchovává komentáře k požadavkům.
- `ticket_status_history` ukládá historii změn stavu.
- `audit_logs` ukládá důležité akce pro dohledatelnost.

## Vztahy a kardinality

- `roles 1:N users`  
  Jedna role může patřit více uživatelům, ale jeden uživatel má právě jednu roli.

- `users 1:N tickets` jako autor  
  Jeden uživatel může založit více požadavků.

- `users 1:N tickets` jako přiřazený technik  
  Jeden technik může řešit více požadavků. Požadavek může být zatím bez technika.

- `categories 1:N tickets`  
  Jedna kategorie může být použita u více požadavků.

- `tickets 1:N ticket_comments`  
  Jeden požadavek má více komentářů.

- `tickets 1:N ticket_status_history`  
  Jeden požadavek má více záznamů historie stavů.

- `users 1:N audit_logs`  
  Jeden uživatel může provést více auditovaných akcí.

V této verzi není potřeba samostatný vztah M:N. Pokud by aplikace v budoucnu umožnila více techniků na jednom požadavku, vznikla by spojovací tabulka například `ticket_assignees`.

## Primární a cizí klíče

Každá tabulka má číselný primární klíč `id`. Cizí klíče hlídají referenční integritu, například požadavek nemůže odkazovat na neexistující kategorii nebo neexistujícího autora.

## Indexy

Použité indexy zrychlují běžné dotazy:

- vyhledání uživatele podle e-mailu
- filtrování požadavků podle stavu, priority, kategorie a autora
- řazení požadavků podle data vytvoření
- dohledání komentářů podle požadavku
- dohledání auditních záznamů podle entity nebo data

Tabulka `tickets` má také fulltext index nad `title` a `description`, který lze využít pro rychlé textové hledání v MySQL/MariaDB.

## Testovací účty

Soubor `seed.sql` vytváří tyto účty. Všechny mají testovací heslo `password`.

| Role | E-mail |
|---|---|
| ADMIN | `admin@servisdesk.local` |
| TECHNICIAN | `technik@servisdesk.local` |
| USER | `student@servisdesk.local` |
| USER | `ucitel@servisdesk.local` |

Tyto účty jsou určeny pouze pro lokální vývoj a prezentaci.

