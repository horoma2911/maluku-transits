-- Kasuku Transits and General Supply
-- SQLite seed data (mirrors js/data.js)

INSERT INTO vehicles (id, plate, make, model, year, capacity, status, driver) VALUES
(1, 'T 123 ABC', 'Toyota', 'Hilux', 2022, '2500kg', 'active', 'Juma Mwinyi'),
(2, 'E 567 BCD', 'Isuzu', 'NQR', 2021, '5000kg', 'active', 'Hassan Said'),
(3, 'R 901 CDE', 'Volvo', 'FH16', 2023, '12000kg', 'maintenance', 'Unassigned'),
(4, 'T 345 DEF', 'Mercedes', 'Actros', 2022, '10000kg', 'active', 'Salum Issa'),
(5, 'Z 789 EFG', 'Scania', 'R500', 2023, '8000kg', 'active', 'Ramadhani Ali'),
(6, 'T 234 FGH', 'MAN', 'TGS', 2021, '15000kg', 'inactive', 'Unassigned');

INSERT INTO trailers (id, type, capacity, plate, status) VALUES
(1, 'Flatbed', '5000kg', 'TRL-001', 'active'),
(2, 'Refrigerated', '3000kg', 'TRL-002', 'active'),
(3, 'Container', '12000kg', 'TRL-003', 'maintenance'),
(4, 'Tanker', '8000kg', 'TRL-004', 'active'),
(5, 'Flatbed', '5000kg', 'TRL-005', 'active');

INSERT INTO drivers (id, name, phone, license, status, trips) VALUES
(1, 'Juma Mwinyi', '+255 712 345 678', 'Class B', 'active', 45),
(2, 'Hassan Said', '+255 723 456 789', 'Class C', 'active', 38),
(3, 'Salum Issa', '+255 734 567 890', 'Class B', 'on_leave', 52),
(4, 'Ramadhani Ali', '+255 745 678 901', 'Class C', 'active', 29),
(5, 'Rashid Othman', '+255 756 789 012', 'Class A', 'active', 61),
(6, 'Mussa Mbelwa', '+255 767 890 123', 'Class B', 'inactive', 0);

INSERT INTO trips (id, ref, origin, destination, vehicle, driver, date, status, amount) VALUES
(1, 'TRP-2024-001', 'Dar es Salaam', 'Dodoma', 'T 123 ABC', 'Juma Mwinyi', '2024-07-15', 'in_transit', 'TZS 85,000'),
(2, 'TRP-2024-002', 'Mwanza', 'Arusha', 'E 567 BCD', 'Hassan Said', '2024-07-14', 'completed', 'TZS 45,000'),
(3, 'TRP-2024-003', 'Mbeya', 'Morogoro', 'R 901 CDE', 'Salum Issa', '2024-07-14', 'pending', 'TZS 62,000'),
(4, 'TRP-2024-004', 'Dar es Salaam', 'Moshi', 'Z 789 EFG', 'Ramadhani Ali', '2024-07-13', 'completed', 'TZS 120,000'),
(5, 'TRP-2024-005', 'Tanga', 'Dodoma', 'T 123 ABC', 'Juma Mwinyi', '2024-07-12', 'cancelled', 'TZS 0');

INSERT INTO customers (id, name, email, phone, status, total_trips) VALUES
(1, 'Tanzania Breweries Ltd', 'logistics@tzbreweries.co.tz', '+255 22 123 4567', 'active', 12),
(2, 'Air Tanzania', 'cargo@airtanzania.co.tz', '+255 22 234 5678', 'active', 8),
(3, 'CRDB Bank', 'supply@crdbbank.co.tz', '+255 22 345 6789', 'active', 15),
(4, 'Vodacom Tanzania', 'logistics@vodacom.co.tz', '+255 22 456 7890', 'inactive', 3);

INSERT INTO suppliers (id, name, category, email, status) VALUES
(1, 'Toyota Tanzania Ltd', 'Vehicle Parts', 'parts@toyota.co.tz', 'active'),
(2, 'TotalEnergies Tanzania', 'Fuel', 'b2b@totalenergies.co.tz', 'active'),
(3, 'Bridgestone TZ', 'Tires', 'info@bridgestone.co.tz', 'active'),
(4, 'ExxonMobil Tanzania', 'Lubricants', 'sales@exxonmobil.co.tz', 'pending');

INSERT INTO inventory (id, name, sku, category, qty, min, price, status) VALUES
(1, 'Engine Oil 5W-30', 'INV-001', 'Lubricants', 150, 50, 2500.00, 'in_stock'),
(2, 'Brake Pads', 'INV-002', 'Spare Parts', 45, 20, 4500.00, 'in_stock'),
(3, 'Truck Tires 295/80R22.5', 'INV-003', 'Tires', 8, 10, 35000.00, 'low_stock'),
(4, 'Diesel Filter', 'INV-004', 'Filters', 0, 15, 1200.00, 'out_of_stock'),
(5, 'Air Filter', 'INV-005', 'Filters', 28, 10, 800.00, 'in_stock');

INSERT INTO purchase_orders (id, ref, supplier, date, amount, status) VALUES
(1, 'PO-2024-001', 'Toyota Tanzania Ltd', '2024-07-15', 'TZS 125,000', 'pending'),
(2, 'PO-2024-002', 'TotalEnergies Tanzania', '2024-07-14', 'TZS 250,000', 'approved'),
(3, 'PO-2024-003', 'Bridgestone TZ', '2024-07-13', 'TZS 420,000', 'delivered');

INSERT INTO expenses (id, ref, category, amount, date, trip, status) VALUES
(1, 'EXP-001', 'Fuel', 85000.00, '2024-07-15', 'TRP-2024-001', 'approved'),
(2, 'EXP-002', 'Maintenance', 45000.00, '2024-07-14', 'N/A', 'pending'),
(3, 'EXP-003', 'Insurance', 120000.00, '2024-07-13', 'N/A', 'approved'),
(4, 'EXP-004', 'Salary', 350000.00, '2024-07-01', 'N/A', 'approved');

INSERT INTO invoices (id, ref, client, date, amount, due, status) VALUES
(1, 'INV-2024-001', 'Tanzania Breweries Ltd', '2024-07-15', 'TZS 185,000', '2024-08-15', 'pending'),
(2, 'INV-2024-002', 'Air Tanzania', '2024-07-14', 'TZS 120,000', '2024-08-14', 'paid'),
(3, 'INV-2024-003', 'CRDB Bank', '2024-07-13', 'TZS 95,000', '2024-08-13', 'overdue');

INSERT INTO payments (id, ref, method, amount, date, status) VALUES
(1, 'PAY-2024-001', 'Bank Transfer', 'TZS 120,000', '2024-07-14', 'completed'),
(2, 'PAY-2024-002', 'M-Pesa', 'TZS 45,000', '2024-07-13', 'completed'),
(3, 'PAY-2024-003', 'Cheque', 'TZS 250,000', '2024-07-12', 'pending');

INSERT INTO users (id, name, email, role, status, password, last_login) VALUES
(1, 'Admin User', 'admin@kasuku.co.tz', 'Administrator', 'active', '$2y$12$L4Wlfq.g2uJ4FsgGdmnJWe1KFCzom2Vz/nCnuMN8HwmwKR/6knzu6', '2024-07-15 09:30'),
(2, 'Ops Manager', 'ops@kasuku.co.tz', 'Manager', 'active', '$2y$12$L4Wlfq.g2uJ4FsgGdmnJWe1KFCzom2Vz/nCnuMN8HwmwKR/6knzu6', '2024-07-15 08:15'),
(3, 'Accountant', 'fin@kasuku.co.tz', 'Accountant', 'active', '$2y$12$L4Wlfq.g2uJ4FsgGdmnJWe1KFCzom2Vz/nCnuMN8HwmwKR/6knzu6', '2024-07-14 17:45'),
(4, 'Dispatcher', 'disp@kasuku.co.tz', 'Dispatcher', 'inactive', '$2y$12$L4Wlfq.g2uJ4FsgGdmnJWe1KFCzom2Vz/nCnuMN8HwmwKR/6knzu6', '2024-07-10 14:20');

INSERT INTO activities (id, user, action, time, icon) VALUES
(1, 'Juma Mwinyi', 'Completed trip TRP-2024-002', '2 hours ago', 'fa-truck-fast'),
(2, 'Admin User', 'Added new vehicle Z 789 EFG', '3 hours ago', 'fa-car'),
(3, 'Accountant', 'Processed invoice INV-2024-002', '5 hours ago', 'fa-file-invoice'),
(4, 'Ops Manager', 'Approved expense EXP-004', 'Yesterday', 'fa-check-circle'),
(5, 'Dispatcher', 'Assigned driver to trip TRP-2024-004', 'Yesterday', 'fa-user-plus');

DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence (name, seq) VALUES
('vehicles', 6), ('trailers', 5), ('drivers', 6), ('trips', 5),
('customers', 4), ('suppliers', 4), ('inventory', 5), ('purchase_orders', 3),
('expenses', 4), ('invoices', 3), ('payments', 3), ('users', 4), ('activities', 5);
