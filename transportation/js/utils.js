const Utils = {
  formatCurrency(amount) {
    return 'TZS ' + Number(amount).toLocaleString('en-TZ');
  },

  formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-TZ', { year: 'numeric', month: 'short', day: 'numeric' });
  },

  getStatusBadge(status) {
    const map = {
      active: 'badge-success',
      completed: 'badge-success',
      paid: 'badge-success',
      delivered: 'badge-success',
      in_transit: 'badge-info',
      approved: 'badge-info',
      pending: 'badge-warning',
      on_leave: 'badge-warning',
      maintenance: 'badge-warning',
      overdue: 'badge-danger',
      cancelled: 'badge-danger',
      inactive: 'badge-danger',
      out_of_stock: 'badge-danger',
      low_stock: 'badge-warning',
      in_stock: 'badge-success'
    };
    return map[status] || 'badge-secondary';
  },

  escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  },

  debounce(fn, delay) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  },

  confirm(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
      <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
          <h3 class="modal-title">Confirm Action</h3>
          <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
        </div>
        <div class="modal-body">
          <p>${Utils.escapeHtml(message)}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" id="confirm-cancel">Cancel</button>
          <button class="btn btn-danger" id="confirm-ok">Delete</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    requestAnimationFrame(() => modal.classList.add('active'));

    modal.querySelector('#confirm-cancel').onclick = () => modal.remove();
    modal.querySelector('#confirm-ok').onclick = () => {
      modal.remove();
      callback();
    };
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
  },

  toast(message, type = 'success') {
    const icons = {
      success: 'fa-check-circle',
      error: 'fa-times-circle',
      warning: 'fa-exclamation-triangle',
      info: 'fa-info-circle'
    };

    const container = document.querySelector('.toast-container') || (() => {
      const c = document.createElement('div');
      c.className = 'toast-container';
      document.body.appendChild(c);
      return c;
    })();

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type]}"></i><span>${Utils.escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('removing');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  },

  downloadPdf(url) {
    const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    if (!token) {
      window.location.href = '../login.html';
      return;
    }
    Utils.showLoader();
    fetch(url, {
      headers: {
        'Authorization': 'Bearer ' + token
      }
    }).then(res => {
      if (!res.ok) throw new Error('Failed to download PDF');
      return res.blob();
    }).then(blob => {
      const blobUrl = URL.createObjectURL(blob);
      window.open(blobUrl, '_blank');
      setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
    }).catch(() => {
      Utils.toast('Failed to download PDF. Please try again.', 'error');
    }).finally(() => {
      Utils.hideLoader();
    });
  },

  showLoader() {
    const loader = document.querySelector('.loader-overlay') || (() => {
      const l = document.createElement('div');
      l.className = 'loader-overlay';
      l.innerHTML = '<div class="spinner"></div><p style="color: var(--text-secondary)">Loading...</p>';
      document.body.appendChild(l);
      return l;
    })();
    loader.classList.remove('hidden');
  },

  hideLoader() {
    const loader = document.querySelector('.loader-overlay');
    if (loader) loader.classList.add('hidden');
  }
};
