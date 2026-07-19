const Layout = {
  init() {
    this.bindSidebar();
    this.bindSearch();
    this.bindTheme();
    this.bindDropdowns();
    this.ensureBackdrop();
    this.setActiveMenu();
    this.syncSidebarOnLoad();
  },

  isMobile() { return window.innerWidth < 768; },
  isTablet() { return window.innerWidth >= 768 && window.innerWidth <= 1023; },

  syncSidebarOnLoad() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;
    if (this.isMobile()) {
      sidebar.classList.remove('open', 'collapsed');
      this.toggleBackdrop(false);
    } else {
      sidebar.classList.remove('open');
      this.toggleBackdrop(false);
    }
    this.syncToggleState();
  },

  syncToggleState() {
    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (!toggle || !sidebar) return;
    const expanded = sidebar.classList.contains('open') || (!this.isMobile() && !sidebar.classList.contains('collapsed'));
    toggle.setAttribute('aria-expanded', String(expanded));
  },

  bindSidebar() {
    const toggle = document.getElementById('sidebar-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      const sidebar = document.querySelector('.sidebar');
      if (!sidebar) return;
      if (this.isMobile()) {
        sidebar.classList.remove('collapsed');
        sidebar.classList.toggle('open');
        this.toggleBackdrop(sidebar.classList.contains('open'));
      } else if (this.isTablet()) {
        sidebar.classList.remove('open');
        this.toggleBackdrop(false);
        sidebar.classList.toggle('open');
      } else {
        sidebar.classList.remove('open');
        this.toggleBackdrop(false);
        sidebar.classList.toggle('collapsed');
      }
      this.syncToggleState();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && sidebar.classList.contains('open')) {
          sidebar.classList.remove('open');
          this.toggleBackdrop(false);
          this.syncToggleState();
        }
      }
    });

    document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
      item.addEventListener('click', () => {
        if (this.isMobile()) {
          const sidebar = document.querySelector('.sidebar');
          if (sidebar) sidebar.classList.remove('open');
          this.toggleBackdrop(false);
          this.syncToggleState();
        }
      });
    });

    window.addEventListener('resize', () => {
      const sidebar = document.querySelector('.sidebar');
      if (!sidebar) return;
      if (this.isMobile()) {
        sidebar.classList.remove('open', 'collapsed');
        this.toggleBackdrop(false);
      } else {
        sidebar.classList.remove('open');
        this.toggleBackdrop(false);
      }
    });
  },

  bindSearch() {
    const btn = document.getElementById('topbar-search-toggle');
    const search = document.querySelector('.topbar-search');
    if (!btn || !search) return;
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      search.classList.toggle('is-open');
    });
    document.addEventListener('click', (e) => {
      if (window.innerWidth >= 768) return;
      if (!search.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
        search.classList.remove('is-open');
      }
    });
  },

  ensureBackdrop() {
    if (document.getElementById('sidebarBackdrop')) return;
    const backdrop = document.createElement('div');
    backdrop.id = 'sidebarBackdrop';
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
    backdrop.addEventListener('click', () => {
      const sidebar = document.querySelector('.sidebar');
      if (sidebar) sidebar.classList.remove('open');
      this.toggleBackdrop(false);
    });
  },

  toggleBackdrop(show) {
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!backdrop) return;
    if (show) {
      backdrop.classList.add('active');
      document.body.style.overflow = 'hidden';
    } else {
      backdrop.classList.remove('active');
      document.body.style.overflow = '';
    }
  },

  bindTheme() {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', () => ThemeManager.toggle());
  },

  bindDropdowns() {
    document.querySelectorAll('.dropdown-toggle').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const dropdown = btn.closest('.dropdown');
        if (!dropdown) return;
        document.querySelectorAll('.dropdown.open').forEach(d => {
          if (d !== dropdown) d.classList.remove('open');
        });
        dropdown.classList.toggle('open');
      });
    });

    document.addEventListener('click', () => {
      document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open'));
    });
  },

  setActiveMenu() {
    const path = window.location.pathname;
    const filename = path.split('/').pop() || 'dashboard.html';
    const map = {
      'dashboard.html': 'dashboard',
      'vehicles.html': 'vehicles',
      'trailers.html': 'trailers',
      'drivers.html': 'drivers',
      'trips.html': 'trips',
      'customers.html': 'customers',
      'suppliers.html': 'suppliers',
      'inventory.html': 'inventory',
      'purchase-orders.html': 'purchase-orders',
      'expenses.html': 'expenses',
      'invoices.html': 'invoices',
      'payments.html': 'payments',
      'reports.html': 'reports',
      'users.html': 'users',
      'settings.html': 'settings',
      'profile.html': 'profile'
    };

    const key = map[filename];
    if (key) {
      document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.page === key) item.classList.add('active');
      });
    }
  }
};
