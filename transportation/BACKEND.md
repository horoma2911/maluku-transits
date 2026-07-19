# Backend (PHP + PostgreSQL / SQLite)

A lightweight vanilla-PHP JSON API backing the Kasuku Transits frontend.
No framework, no Composer — just PDO and a small front-controller.

## Structure

```
config.php            DB connection (driver select) + JSON/CORS helpers
api/
  bootstrap.php       Generic CRUD handler used by every resource
  index.php           Front-controller: /api/<resource>[/id]
  router.php          Dev-server (php -S) rewrite to index.php
  vehicles.php        Resource endpoints (one file per entity)
  trips.php
  invoices.php
  expenses.php
  payments.php
  customers.php
  suppliers.php
  inventory.php
  purchase-orders.php
  drivers.php
  trailers.php
  users.php
  activities.php
db/
  schema.sql          PostgreSQL schema
  seed.sql            PostgreSQL seed data
  schema.sqlite.sql   SQLite schema
  seed.sqlite.sql     SQLite seed data
install.php           Creates DB + applies schema + seed
router.php            PHP built-in server router (project root)
```

## Database driver

Set `DB_DRIVER` in `config.php`:

- `'sqlite'` (default, zero setup) — uses `db/kasuku_tgs.sqlite`.
  Requires the `pdo_sqlite` PHP extension.
- `'pgsql'` (production) — uses `DB_HOST/PORT/NAME/USER/PASS`.

## Setup

```bash
# 1. (optional) enable extensions in php.ini
#    extension=pdo_sqlite   (for sqlite)
#    extension=pdo_pgsql    (for postgres)

# 2. create the database + seed it
php install.php

# 3. run the dev server
php -S 127.0.0.1:8000 -t . router.php
```

The API is then available at `http://127.0.0.1:8000/api/...`.

## API

All responses are JSON. CORS is open (`*`).

| Method | Endpoint              | Description            |
|--------|-----------------------|------------------------|
| GET    | `/api/<resource>`     | List all rows          |
| GET    | `/api/<resource>?id=N`| Get one row            |
| POST   | `/api/<resource>`     | Create (JSON body)     |
| PUT    | `/api/<resource>?id=N`| Update (JSON body)     |
| DELETE | `/api/<resource>?id=N`| Delete                 |

Resources: `vehicles`, `trailers`, `drivers`, `trips`, `customers`,
`suppliers`, `inventory`, `purchase-orders`, `expenses`, `invoices`,
`payments`, `users`, `activities`.

### Example

```bash
curl -X POST http://127.0.0.1:8000/api/invoices \
  -H "Content-Type: application/json" \
  -d '{"ref":"INV-2024-999","client":"Acme Ltd","date":"2024-07-18","amount":"TZS 10,000","due":"2024-08-18","status":"pending"}'
```

## Production

For Apache/Nginx, point requests at `api/index.php` (e.g. via a
`RewriteRule` so `/api/<resource>` maps to `api/index.php`). The
`router.php` is only needed for PHP's built-in development server.
