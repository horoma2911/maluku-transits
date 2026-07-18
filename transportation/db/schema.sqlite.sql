-- Kasuku Transits and General Supply
-- SQLite schema (zero-setup local/dev database)

DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS inventory;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS trips;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS trailers;
DROP TABLE IF EXISTS vehicles;

CREATE TABLE vehicles (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    plate       TEXT NOT NULL,
    make        TEXT NOT NULL,
    model       TEXT NOT NULL,
    year        INTEGER,
    capacity    TEXT,
    status      TEXT DEFAULT 'active',
    driver      TEXT
);

CREATE TABLE trailers (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    type        TEXT NOT NULL,
    capacity    TEXT,
    plate       TEXT,
    status      TEXT DEFAULT 'active'
);

CREATE TABLE drivers (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    phone       TEXT,
    license     TEXT,
    status      TEXT DEFAULT 'active',
    trips       INTEGER DEFAULT 0
);

CREATE TABLE trips (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    ref         TEXT NOT NULL,
    origin      TEXT NOT NULL,
    destination TEXT NOT NULL,
    vehicle     TEXT,
    driver      TEXT,
    date        TEXT,
    status      TEXT DEFAULT 'pending',
    amount      TEXT
);

CREATE TABLE customers (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    email       TEXT,
    phone       TEXT,
    status      TEXT DEFAULT 'active',
    total_trips INTEGER DEFAULT 0
);

CREATE TABLE suppliers (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    category    TEXT,
    email       TEXT,
    status      TEXT DEFAULT 'active'
);

CREATE TABLE inventory (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    sku         TEXT,
    category    TEXT,
    qty         INTEGER DEFAULT 0,
    min         INTEGER DEFAULT 0,
    price       REAL DEFAULT 0,
    status      TEXT DEFAULT 'in_stock'
);

CREATE TABLE purchase_orders (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    ref         TEXT NOT NULL,
    supplier    TEXT,
    date        TEXT,
    amount      TEXT,
    status      TEXT DEFAULT 'pending'
);

CREATE TABLE expenses (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    ref         TEXT NOT NULL,
    category    TEXT,
    amount      REAL DEFAULT 0,
    date        TEXT,
    trip        TEXT DEFAULT 'N/A',
    status      TEXT DEFAULT 'pending'
);

CREATE TABLE invoices (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    ref         TEXT NOT NULL,
    client      TEXT NOT NULL,
    date        TEXT,
    amount      TEXT,
    due         TEXT,
    status      TEXT DEFAULT 'pending'
);

CREATE TABLE payments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    ref         TEXT NOT NULL,
    method      TEXT,
    amount      TEXT,
    date        TEXT,
    status      TEXT DEFAULT 'pending'
);

CREATE TABLE users (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    email       TEXT UNIQUE,
    role        TEXT,
    status      TEXT DEFAULT 'active',
    password    TEXT,
    last_login  TEXT
);

CREATE TABLE activities (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user        TEXT,
    action      TEXT,
    time        TEXT,
    icon        TEXT
);

CREATE INDEX idx_trips_ref ON trips(ref);
CREATE INDEX idx_invoices_ref ON invoices(ref);
CREATE INDEX idx_expenses_ref ON expenses(ref);
CREATE INDEX idx_payments_ref ON payments(ref);
CREATE INDEX idx_po_ref ON purchase_orders(ref);
