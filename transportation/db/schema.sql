-- Kasuku Transits and General Supply
-- PostgreSQL schema

DROP TABLE IF EXISTS activities CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS payments CASCADE;
DROP TABLE IF EXISTS invoices CASCADE;
DROP TABLE IF EXISTS expenses CASCADE;
DROP TABLE IF EXISTS purchase_orders CASCADE;
DROP TABLE IF EXISTS inventory CASCADE;
DROP TABLE IF EXISTS suppliers CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS trips CASCADE;
DROP TABLE IF EXISTS drivers CASCADE;
DROP TABLE IF EXISTS trailers CASCADE;
DROP TABLE IF EXISTS vehicles CASCADE;

CREATE TABLE vehicles (
    id          SERIAL PRIMARY KEY,
    plate       VARCHAR(50) NOT NULL,
    make        VARCHAR(100) NOT NULL,
    model       VARCHAR(100) NOT NULL,
    year        INT,
    capacity    VARCHAR(50),
    status      VARCHAR(30) DEFAULT 'active',
    driver      VARCHAR(100)
);

CREATE TABLE trailers (
    id          SERIAL PRIMARY KEY,
    type        VARCHAR(50) NOT NULL,
    capacity    VARCHAR(50),
    plate       VARCHAR(50),
    status      VARCHAR(30) DEFAULT 'active'
);

CREATE TABLE drivers (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    phone       VARCHAR(50),
    license     VARCHAR(50),
    status      VARCHAR(30) DEFAULT 'active',
    trips       INT DEFAULT 0
);

CREATE TABLE trips (
    id          SERIAL PRIMARY KEY,
    ref         VARCHAR(50) NOT NULL,
    origin      VARCHAR(150) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    vehicle     VARCHAR(100),
    driver      VARCHAR(150),
    date        DATE,
    status      VARCHAR(30) DEFAULT 'pending',
    amount      VARCHAR(50)
);

CREATE TABLE customers (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(150),
    phone       VARCHAR(50),
    status      VARCHAR(30) DEFAULT 'active',
    total_trips INT DEFAULT 0
);

CREATE TABLE suppliers (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    category    VARCHAR(100),
    email       VARCHAR(150),
    status      VARCHAR(30) DEFAULT 'active'
);

CREATE TABLE inventory (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    sku         VARCHAR(50),
    category    VARCHAR(100),
    qty         INT DEFAULT 0,
    min         INT DEFAULT 0,
    price       NUMERIC(12,2) DEFAULT 0,
    status      VARCHAR(30) DEFAULT 'in_stock'
);

CREATE TABLE purchase_orders (
    id          SERIAL PRIMARY KEY,
    ref         VARCHAR(50) NOT NULL,
    supplier    VARCHAR(150),
    date        DATE,
    amount      VARCHAR(50),
    status      VARCHAR(30) DEFAULT 'pending'
);

CREATE TABLE expenses (
    id          SERIAL PRIMARY KEY,
    ref         VARCHAR(50) NOT NULL,
    category    VARCHAR(100),
    amount      NUMERIC(12,2) DEFAULT 0,
    date        DATE,
    trip        VARCHAR(50) DEFAULT 'N/A',
    status      VARCHAR(30) DEFAULT 'pending'
);

CREATE TABLE invoices (
    id          SERIAL PRIMARY KEY,
    ref         VARCHAR(50) NOT NULL,
    client      VARCHAR(150) NOT NULL,
    date        DATE,
    amount      VARCHAR(50),
    due         DATE,
    status      VARCHAR(30) DEFAULT 'pending'
);

CREATE TABLE payments (
    id          SERIAL PRIMARY KEY,
    ref         VARCHAR(50) NOT NULL,
    method      VARCHAR(100),
    amount      VARCHAR(50),
    date        DATE,
    status      VARCHAR(30) DEFAULT 'pending'
);

CREATE TABLE users (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(150) UNIQUE,
    role        VARCHAR(50),
    status      VARCHAR(30) DEFAULT 'active',
    password    VARCHAR(255),
    last_login  VARCHAR(50)
);

CREATE TABLE activities (
    id          SERIAL PRIMARY KEY,
    user        VARCHAR(150),
    action      VARCHAR(255),
    time        VARCHAR(100),
    icon        VARCHAR(50)
);

CREATE INDEX idx_trips_ref ON trips(ref);
CREATE INDEX idx_invoices_ref ON invoices(ref);
CREATE INDEX idx_expenses_ref ON expenses(ref);
CREATE INDEX idx_payments_ref ON payments(ref);
CREATE INDEX idx_po_ref ON purchase_orders(ref);
