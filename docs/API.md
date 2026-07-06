# Dokumentace REST API

API je dostupné přes `public/api/index.php`. Při použití Apache rewrite lze volat také kratší tvar `/api/...`.

Příklad základní adresy pro XAMPP:

```text
http://localhost/servisdesk/public/api/index.php
```

## Formát odpovědi

Všechny endpointy vrací JSON ve stejném tvaru:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "errors": []
}
```

Při chybě:

```json
{
  "success": false,
  "message": "Popis chyby.",
  "data": null,
  "errors": {
    "field": "Popis validační chyby."
  }
}
```

## Autentizace a CSRF

API používá session cookie. Po přihlášení vrací endpoint `POST /auth/login` také `csrf_token`. Ten musí frontend posílat u měnících požadavků v hlavičce:

```text
X-CSRF-Token: hodnota_tokenu
```

CSRF token vyžadují hlavně metody `POST`, `PATCH`, `PUT` a `DELETE` u přihlášených operací.

## Endpointy

### GET /health

- Účel: kontrola dostupnosti API
- Oprávnění: veřejné
- Statusy: `200`

### POST /auth/register

- Účel: historický endpoint veřejné registrace
- Oprávnění: veřejné
- Stav: veřejná registrace je vypnutá
- Výsledek: `403`
- Poznámka: účty vytváří pouze administrátor přes `POST /admin/users`

### POST /auth/login

- Účel: přihlášení uživatele
- Oprávnění: veřejné
- Tělo:

```json
{
  "email": "admin@servisdesk.local",
  "password": "password"
}
```

- Úspěch: `200`, vrací uživatele a CSRF token
- Chyby: `401`, `422`

### POST /auth/logout

- Účel: odhlášení
- Oprávnění: přihlášený uživatel
- CSRF: ano
- Úspěch: `200`

### GET /auth/me

- Účel: načtení aktuální session
- Oprávnění: veřejné
- Vrací: `user` nebo `null`, `csrf_token`

### PATCH /profile

- Účel: úprava profilu a volitelná změna hesla
- Oprávnění: přihlášený uživatel
- CSRF: ano
- Tělo:

```json
{
  "name": "Nové jméno",
  "phone": "+420 123 456 789",
  "department": "4.IT",
  "current_password": "password",
  "new_password": "noveheslo"
}
```

### GET /categories

- Účel: seznam aktivních kategorií pro formuláře
- Oprávnění: přihlášený uživatel

### GET /tickets

- Účel: seznam požadavků
- Oprávnění: přihlášený uživatel
- Chování:
  - `USER` vidí jen vlastní požadavky
  - `TECHNICIAN` a `ADMIN` vidí všechny požadavky
- Query parametry:
  - `page`
  - `per_page`
  - `search`
  - `status`
  - `priority`
  - `category_id`
  - `sort`
  - `direction`

Příklad:

```text
GET /tickets?status=new&priority=urgent&page=1
```

### POST /tickets

- Účel: vytvoření požadavku
- Oprávnění: přihlášený uživatel
- CSRF: ano
- Tělo:

```json
{
  "title": "Projektor nefunguje",
  "description": "Po zapnutí se nezobrazí obraz.",
  "category_id": 1,
  "priority": "high"
}
```

### GET /tickets/{id}

- Účel: detail požadavku
- Oprávnění:
  - vlastník požadavku
  - `TECHNICIAN`
  - `ADMIN`
- Poznámka: interní komentáře vidí jen `TECHNICIAN` a `ADMIN`

### PATCH nebo PUT /tickets/{id}

- Účel: úprava požadavku
- Oprávnění:
  - vlastník může upravit otevřený vlastní požadavek
  - `TECHNICIAN` a `ADMIN` mohou měnit stav, prioritu a řešení
- CSRF: ano
- Tělo:

```json
{
  "title": "Upravený název",
  "description": "Upravený popis",
  "category_id": 1,
  "priority": "urgent",
  "status": "in_progress",
  "status_note": "Technik začal požadavek řešit."
}
```

### DELETE /tickets/{id}

- Účel: odstranění požadavku
- Oprávnění:
  - `ADMIN` může odstranit libovolný požadavek
  - `USER` může odstranit vlastní nový požadavek
- CSRF: ano

### POST /tickets/{id}/comments

- Účel: přidání komentáře
- Oprávnění: uživatel s přístupem k požadavku
- CSRF: ano
- Tělo:

```json
{
  "body": "Komentář k řešení.",
  "is_internal": false
}
```

### GET /admin/dashboard

- Účel: statistiky a data pro dashboard
- Oprávnění: `TECHNICIAN`, `ADMIN`
- Vrací:
  - souhrnné statistiky
  - počty podle stavů
  - počty podle priorit
  - počty podle kategorií
  - poslední požadavky
  - poslední auditní záznamy

### GET /admin/users

- Účel: seznam uživatelů
- Oprávnění: `ADMIN`

### POST /admin/users

- Účel: vytvoření uživatele administrátorem
- Oprávnění: `ADMIN`
- CSRF: ano
- Tělo:

```json
{
  "name": "Nový uživatel",
  "email": "novy@servisdesk.local",
  "password": "docasneheslo",
  "role_id": 1
}
```

- Úspěch: `201`
- Chyby: `409` duplicitní e-mail, `422` validační chyba

### PATCH /admin/users/{id}

- Účel: změna jména, role a aktivity uživatele
- Oprávnění: `ADMIN`
- CSRF: ano

### GET /admin/roles

- Účel: seznam rolí
- Oprávnění: `ADMIN`

### GET /admin/categories

- Účel: seznam všech kategorií
- Oprávnění: `ADMIN`

### POST /admin/categories

- Účel: vytvoření kategorie
- Oprávnění: `ADMIN`
- CSRF: ano

### PATCH /admin/categories/{id}

- Účel: úprava kategorie
- Oprávnění: `ADMIN`
- CSRF: ano

### GET /admin/audit-log

- Účel: poslední auditní události
- Oprávnění: `ADMIN`

## Používané HTTP status kódy

- `200` úspěch
- `201` vytvořeno
- `400` neplatný JSON
- `401` není přihlášen
- `403` nedostatečné oprávnění nebo neplatný CSRF token
- `404` záznam nebyl nalezen
- `409` konflikt, například duplicitní e-mail
- `422` validační chyba
- `500` neočekávaná chyba serveru
