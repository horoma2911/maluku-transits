const AppData = {
  vehicles: [
    { id: 1, plate: 'T 123 ABC', make: 'Toyota', model: 'Hilux', year: 2022, capacity: '2500kg', status: 'active', driver: 'Juma Mwinyi' },
    { id: 2, plate: 'E 567 BCD', make: 'Isuzu', model: 'NQR', year: 2021, capacity: '5000kg', status: 'active', driver: 'Hassan Said' },
    { id: 3, plate: 'R 901 CDE', make: 'Volvo', model: 'FH16', year: 2023, capacity: '12000kg', status: 'maintenance', driver: 'Unassigned' },
    { id: 4, plate: 'T 345 DEF', make: 'Mercedes', model: 'Actros', year: 2022, capacity: '10000kg', status: 'active', driver: 'Salum Issa' },
    { id: 5, plate: 'Z 789 EFG', make: 'Scania', model: 'R500', year: 2023, capacity: '8000kg', status: 'active', driver: 'Ramadhani Ali' },
    { id: 6, plate: 'T 234 FGH', make: 'MAN', model: 'TGS', year: 2021, capacity: '15000kg', status: 'inactive', driver: 'Unassigned' },
  ],

  trailers: [
    { id: 1, type: 'Flatbed', capacity: '5000kg', plate: 'TRL-001', status: 'active' },
    { id: 2, type: 'Refrigerated', capacity: '3000kg', plate: 'TRL-002', status: 'active' },
    { id: 3, type: 'Container', capacity: '12000kg', plate: 'TRL-003', status: 'maintenance' },
    { id: 4, type: 'Tanker', capacity: '8000kg', plate: 'TRL-004', status: 'active' },
    { id: 5, type: 'Flatbed', capacity: '5000kg', plate: 'TRL-005', status: 'active' },
  ],

  drivers: [
    { id: 1, name: 'Juma Mwinyi', phone: '+255 712 345 678', license: 'Class B', status: 'active', trips: 45 },
    { id: 2, name: 'Hassan Said', phone: '+255 723 456 789', license: 'Class C', status: 'active', trips: 38 },
    { id: 3, name: 'Salum Issa', phone: '+255 734 567 890', license: 'Class B', status: 'on_leave', trips: 52 },
    { id: 4, name: 'Ramadhani Ali', phone: '+255 745 678 901', license: 'Class C', status: 'active', trips: 29 },
    { id: 5, name: 'Rashid Othman', phone: '+255 756 789 012', license: 'Class A', status: 'active', trips: 61 },
    { id: 6, name: 'Mussa Mbelwa', phone: '+255 767 890 123', license: 'Class B', status: 'inactive', trips: 0 },
  ],

  trips: [
    { id: 1, ref: 'TRP-2026-001', origin: 'Dar es Salaam', destination: 'Dodoma', vehicle: 'T 123 ABC', driver: 'Juma Mwinyi', date: '2026-07-15', status: 'in_transit', amount: 'TZS 85,000' },
    { id: 2, ref: 'TRP-2026-002', origin: 'Mwanza', destination: 'Arusha', vehicle: 'E 567 BCD', driver: 'Hassan Said', date: '2026-07-14', status: 'completed', amount: 'TZS 45,000' },
    { id: 3, ref: 'TRP-2026-003', origin: 'Mbeya', destination: 'Morogoro', vehicle: 'R 901 CDE', driver: 'Salum Issa', date: '2026-07-14', status: 'pending', amount: 'TZS 62,000' },
    { id: 4, ref: 'TRP-2026-004', origin: 'Dar es Salaam', destination: 'Moshi', vehicle: 'Z 789 EFG', driver: 'Ramadhani Ali', date: '2026-07-13', status: 'completed', amount: 'TZS 120,000' },
    { id: 5, ref: 'TRP-2026-005', origin: 'Tanga', destination: 'Dodoma', vehicle: 'T 123 ABC', driver: 'Juma Mwinyi', date: '2026-07-12', status: 'cancelled', amount: 'TZS 0' },
  ],

  customers: [
    { id: 1, name: 'Tanzania Breweries Ltd', email: 'logistics@tzbreweries.co.tz', phone: '+255 22 123 4567', status: 'active', totalTrips: 12 },
    { id: 2, name: 'Air Tanzania', email: 'cargo@airtanzania.co.tz', phone: '+255 22 234 5678', status: 'active', totalTrips: 8 },
    { id: 3, name: 'CRDB Bank', email: 'supply@crdbbank.co.tz', phone: '+255 22 345 6789', status: 'active', totalTrips: 15 },
    { id: 4, name: 'Vodacom Tanzania', email: 'logistics@vodacom.co.tz', phone: '+255 22 456 7890', status: 'inactive', totalTrips: 3 },
  ],

  suppliers: [
    { id: 1, name: 'Toyota Tanzania Ltd', category: 'Vehicle Parts', email: 'parts@toyota.co.tz', status: 'active' },
    { id: 2, name: 'TotalEnergies Tanzania', category: 'Fuel', email: 'b2b@totalenergies.co.tz', status: 'active' },
    { id: 3, name: 'Bridgestone TZ', category: 'Tires', email: 'info@bridgestone.co.tz', status: 'active' },
    { id: 4, name: 'ExxonMobil Tanzania', category: 'Lubricants', email: 'sales@exxonmobil.co.tz', status: 'pending' },
  ],

  inventory: [
    { id: 1, name: 'Engine Oil 5W-30', sku: 'INV-001', category: 'Lubricants', qty: 150, min: 50, price: 2500, status: 'in_stock' },
    { id: 2, name: 'Brake Pads', sku: 'INV-002', category: 'Spare Parts', qty: 45, min: 20, price: 4500, status: 'in_stock' },
    { id: 3, name: 'Truck Tires 295/80R22.5', sku: 'INV-003', category: 'Tires', qty: 8, min: 10, price: 35000, status: 'low_stock' },
    { id: 4, name: 'Diesel Filter', sku: 'INV-004', category: 'Filters', qty: 0, min: 15, price: 1200, status: 'out_of_stock' },
    { id: 5, name: 'Air Filter', sku: 'INV-005', category: 'Filters', qty: 28, min: 10, price: 800, status: 'in_stock' },
  ],

  purchaseOrders: [
    { id: 1, ref: 'PO-2026-001', supplier: 'Toyota Tanzania Ltd', date: '2026-07-15', amount: 'TZS 125,000', status: 'pending' },
    { id: 2, ref: 'PO-2026-002', supplier: 'TotalEnergies Tanzania', date: '2026-07-14', amount: 'TZS 250,000', status: 'approved' },
    { id: 3, ref: 'PO-2026-003', supplier: 'Bridgestone TZ', date: '2026-07-13', amount: 'TZS 420,000', status: 'delivered' },
  ],

  expenses: [
    { id: 1, ref: 'EXP-001', category: 'Fuel', amount: 85000, date: '2026-07-15', trip: 'TRP-2026-001', status: 'approved' },
    { id: 2, ref: 'EXP-002', category: 'Maintenance', amount: 45000, date: '2026-07-14', trip: 'N/A', status: 'pending' },
    { id: 3, ref: 'EXP-003', category: 'Insurance', amount: 120000, date: '2026-07-13', trip: 'N/A', status: 'approved' },
    { id: 4, ref: 'EXP-004', category: 'Salary', amount: 350000, date: '2026-07-01', trip: 'N/A', status: 'approved' },
  ],

  invoices: [
    { id: 1, ref: 'INV-2026-001', client: 'Tanzania Breweries Ltd', date: '2026-07-15', amount: 'TZS 185,000', due: '2026-08-15', status: 'pending' },
    { id: 2, ref: 'INV-2026-002', client: 'Air Tanzania', date: '2026-07-14', amount: 'TZS 120,000', due: '2026-08-14', status: 'paid' },
    { id: 3, ref: 'INV-2026-003', client: 'CRDB Bank', date: '2026-07-13', amount: 'TZS 95,000', due: '2026-08-13', status: 'overdue' },
  ],

  payments: [
    { id: 1, ref: 'PAY-2026-001', method: 'Bank Transfer', amount: 'TZS 120,000', date: '2026-07-14', status: 'completed' },
    { id: 2, ref: 'PAY-2026-002', method: 'M-Pesa', amount: 'TZS 45,000', date: '2026-07-13', status: 'completed' },
    { id: 3, ref: 'PAY-2026-003', method: 'Cheque', amount: 'TZS 250,000', date: '2026-07-12', status: 'pending' },
  ],

  users: [
    { id: 1, name: 'Admin User', email: 'admin@malukulogistics.co.tz', role: 'Administrator', status: 'active', lastLogin: '2026-07-15 09:30' },
    { id: 2, name: 'Ops Manager', email: 'ops@malukulogistics.co.tz', role: 'Manager', status: 'active', lastLogin: '2026-07-15 08:15' },
    { id: 3, name: 'Accountant', email: 'fin@malukulogistics.co.tz', role: 'Accountant', status: 'active', lastLogin: '2026-07-14 17:45' },
    { id: 4, name: 'Dispatcher', email: 'disp@malukulogistics.co.tz', role: 'Dispatcher', status: 'inactive', lastLogin: '2026-07-10 14:20' },
  ],

  activities: [
    { id: 1, user: 'Juma Mwinyi', action: 'Completed trip TRP-2026-002', time: '2 hours ago', icon: 'fa-truck-fast' },
    { id: 2, user: 'Admin User', action: 'Added new vehicle Z 789 EFG', time: '3 hours ago', icon: 'fa-car' },
    { id: 3, user: 'Accountant', action: 'Processed invoice INV-2026-002', time: '5 hours ago', icon: 'fa-file-invoice' },
    { id: 4, user: 'Ops Manager', action: 'Approved expense EXP-004', time: 'Yesterday', icon: 'fa-check-circle' },
    { id: 5, user: 'Dispatcher', action: 'Assigned driver to trip TRP-2026-004', time: 'Yesterday', icon: 'fa-user-plus' },
  ]
};

if (typeof module !== 'undefined' && module.exports) {
  module.exports = AppData;
}
