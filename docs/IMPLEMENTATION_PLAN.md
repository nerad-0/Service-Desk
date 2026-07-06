# Implementační plán

## Etapa 1: Výběr tématu

Vybrané téma: ServisDesk, webová aplikace pro správu školních IT požadavků.

## Etapa 2: Analýza

Dokument `docs/ANALYSIS.md` popisuje cíle, role, požadavky, scénáře, stránky a API.

## Etapa 3: Založení projektu

Vytvořit adresářovou strukturu:

- `public/` pro frontend a veřejný vstup do API
- `src/` pro PHP backend
- `config/` pro konfiguraci
- `database/` pro SQL soubory
- `docs/` pro dokumentaci
- `tests/` pro testovací podklady
- `storage/` pro lokální runtime soubory

## Etapa 4: Git a GitHub

Inicializovat lokální Git repozitář a vytvářet průběžné commity. Pokud nebude dostupné GitHub CLI, zůstane projekt připravený lokálně a dokumentace popíše ruční vytvoření vzdáleného repozitáře.

## Etapa 5: Databáze

Navrhnout tabulky pro uživatele, role, požadavky, kategorie, komentáře, historii stavů a auditní log. Vytvořit `schema.sql` a `seed.sql`.

## Etapa 6: Backend

Vytvořit jednoduchý PHP backend bez frameworku:

- router
- JSON response helper
- PDO databázovou vrstvu
- autentizaci
- autorizaci
- validaci
- controllery

## Etapa 7: REST API

Implementovat endpointy pro autentizaci, požadavky, komentáře, administraci a dashboard.

## Etapa 8: Autentizace a role

Použít session autentizaci, `password_hash`, `password_verify`, CSRF token a backendovou kontrolu rolí.

## Etapa 9: Hlavní funkce

Implementovat CRUD požadavků, komentáře, vyhledávání, filtrování, stránkování, dashboard, graf a auditní log.

## Etapa 10: Frontend

Vytvořit responzivní rozhraní v HTML, CSS a JavaScriptu. Frontend bude používat Fetch API a JSON.

## Etapa 11: Administrace

Vytvořit správu uživatelů, kategorií, dashboard a auditní log.

## Etapa 12: Bezpečnost

Provést bezpečnostní kontrolu a vytvořit `SECURITY.md`.

## Etapa 13: Testování

Vytvořit manuální testovací plán a dostupné automatizované statické kontroly.

## Etapa 14 až 19: Dokumentace a obhajoba

Doplnit dokumentaci maturitní práce, prezentaci, scénář živé ukázky, otázky komise a vysvětlení kódu studentovi.

## Etapa 20: Finální audit

Zkontrolovat funkčnost souborů, dokumentace, Git historii, absenci tajných údajů a připravenost projektu k prezentaci.

